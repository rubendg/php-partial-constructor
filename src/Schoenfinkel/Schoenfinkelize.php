<?php

/*
 * This file is part of the php-partial-constructor package.
 * (c) Ruben Alexander de Gooijer <rubendegooijer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoenfinkel;

use \ArrayAccess;
use \BadMethodCallException;
use \DomainException;
use \InvalidArgumentException;
use \ReflectionClass;

/**
 * 
 */
trait Schoenfinkelize {

   /**
    * An instance for the target class used to obtain the constructor
    * parameter list, and when the constructor is fully applied to create
    * an instance of.
    * 
    * @var ReflectionClass
    */
   protected $targetClass;
   
   /**
    * Contains the parameters the constructor is currently
    * closed over. 
    * 
    * @var ParameterMap
    */
   private $parameterMap;
   
   /**
    * Contains the fully qualified target class name.
    * 
    * @var string
    */
//   protected static $targetClassName;

   /**
    * Contains a mapping from argument name to its type.
    * 
    * Child classes should override this property such that static
    * pertains to the child instead of the parent.
    * 
    * string -> array|class|callable|null
    */
   protected static $typeMap;
   
   /**
    * Constant for arguments of type array
    */
   private static $arrayType = 0;
   
   /**
    * Constant for argument of type callable
    */
   private static $callableType = 1;
   

   /**
    * The constructor is overloaded for three different parameter lists.
    * 
    * 1. When called with no arguments it initializes the object by creating an
    * ReflectionClass instance for the $targetClassName, it then reflects over the constructors
    * properties obtaining a list of parameters together with their types.
    * 
    * 2. When called with 1 argument, the argument must be of type array, and contain a mapping
    * from argument names to values. This has the same effect as passing in no arguments and immediately
    * calling apply after that.
    * 
    * 3. When called with 2 arguments the first should be an instance of ReflectionClass for 
    * the target class, and the second one an instance of ParameterMap containing a modified 
    * environment. This constructor overload is only ever called internally as a result of calling
    * apply.
    * 
    * 
    * @throws DomainException
    * @throws BadMethodCallException
    */
   public function __construct() {
      $arguments = func_get_args();
      $argumentCount = count($arguments);
         
      if($argumentCount == 0 || $argumentCount == 1) {
         
         if(!static::$targetClassName) {
            throw new DomainException('No target class name set');
         }
         
         $this->targetClass = new ReflectionClass(static::$targetClassName);
         
         $params = $this->targetClass->getConstructor()->getParameters(); 
         
         //we initialize $typeMap only once for all instances of the target class
         $initializeTypeMap = static::$typeMap == null;
         $paramNames = [];
         foreach($params as $param) {
            $paramNames[] = $param->name;
            
            if($initializeTypeMap) {
               if($class = $param->getClass()) {
                  static::$typeMap[$param->name] = $class->name;
               } else if($param->isArray()) {
                  static::$typeMap[$param->name] = self::$arrayType;
               } else if($param->isCallable()) {
                  static::$typeMap[$param->name] = self::$callableType;
               }
            }
         }
         
         $numArgs = count($params);
         if($numArgs < 1) {
            throw new DomainException("There is no point in enabling partial constructor application for \"{$this->targetClass->getName()}\" which has zero arguments");
         }
         
         $this->parameterMap = new ParameterMap($this->targetClass->getName(), $paramNames);
         
         if($argumentCount == 1) {
            self::checkTypes($arguments[0]);
            $this->parameterMap->fill($arguments[0]);
            
            if($this->parameterMap->saturated()) {
               throw new BadMethodCallException("\"{$this->targetClass->getName()}\" should be partially applied, otherwise use regular object instantiation");
            }
         }
         
      } else if($argumentCount == 2) {
         
         list($targetClass, $params) = $arguments;
         $this->targetClass = $targetClass;
         $this->parameterMap = $params;
         
      } else {
         throw new BadMethodCallException('Wrong argument number.');
      }
   }
   
   private function newInstance(ParameterMap $map) {
      $paramList = [];
      foreach($this->targetClass->getConstructor()->getParameters() as $param) {
         $paramList[$param->name] = $map[$param->name];
      }
      return $this->targetClass->newInstanceArgs($paramList);
   }
   
   public function apply(array $paramMap) {
      self::checkTypes($paramMap);
      
      $map = clone $this->parameterMap;
      $map->fill($paramMap);

      $parameterMap = $this->parameterMap->merge($map);

      //undersaturated
      if(! $parameterMap->saturated() ) {
         return $this->newSchoenfinkel($parameterMap);
      }

      return $this->newInstance($parameterMap);
   }

   private function newSchoenfinkel(ParameterMap $parameterMap) {
      return new static($this->targetClass, $parameterMap);
   }
   
   private static function checkTypes(array $nameValue) {
      foreach($nameValue as $name => $value) {
         self::typeCheck($name, $value);
      }
   }
   
   private static function typeCheck($name, $value) {
      $expectedType = isset(static::$typeMap[$name]) ? static::$typeMap[$name] : null;
      
      $match = $expectedType === null
         || is_string($expectedType) && $value instanceof $expectedType
         || is_array($value)         && $expectedType === self::$arrayType
         || is_callable($value)      && $expectedType === self::$callableType;
      
      if(!$match)  {
         throw new InvalidArgumentException("Expected \"$name\" to be of type \"$expectedType\", but got a value of type \"".  gettype($value)."\"");
      }
      
      return true;
   }
   
   public function __toString() {
      return "Schoenfinkel for \"{$this->targetClass->getName()}\"\n{$this->parameterMap}";
   }
   
   public function __call($name, $arguments) {
      throw new BadMethodCallException(sprintf(
         "Trying to call method \"%s\" on a partially constructed class \"%s\" \n%s",
         $name,
         $this->targetClass->getName(),
         $this->parameterMap
      ));
   }

   public function offsetExists($offset) {
      return $this->parameterMap->offsetExists($offset);
   }

   public function offsetGet($offset) {
      return $this->parameterMap->offsetGet($offset);
   }

   public function offsetSet($offset, $value) {
      return $this->parameterMap->offsetSet($offset, $value);
   }

   public function offsetUnset($offset) {
      return $this->parameterMap->offsetUnset($offset);
   }
}