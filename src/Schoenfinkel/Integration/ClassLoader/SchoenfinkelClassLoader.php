<?php

/*
 * This file is part of the php-partial-constructor package.
 * (c) Ruben Alexander de Gooijer <rubendegooijer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoenfinkel\Integration\ClassLoader;

use \ReflectionClass;
use \Schoenfinkel\Integration\CodeGenerator;

class SchoenfinkelClassLoader {

   private $loader;
   private $generator;
   private $postfix;

   public function __construct($loader, CodeGenerator $generator) {
      $this->loader = $loader;
      $this->generator = $generator;
   }

   public function setPostfix($postfix) {
      $this->postfix = $postfix;
   }
   
   public function loadClass($class) {
      if(($targetClass = $this->generator->getClassNameMapper()->from($class))) {
         eval($this->generator->getPartialClass(new ReflectionClass($targetClass)));
         return true;
      }
      
      return $this->loader->loadClass($class);
   }
   
   /**
    * Forward all other calls to the wrapped class loader
    * 
    * @param type $name
    * @param type $arguments
    * @return type
    */
   public function __call($name, $arguments) {
      return call_user_func_array([$this->loader, $name], $arguments);
   }

}