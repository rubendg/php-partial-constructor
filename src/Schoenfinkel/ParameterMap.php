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
use \ArrayIterator;
use \BadMethodCallException;
use \IteratorAggregate;

class ParameterMap implements ArrayAccess, IteratorAggregate {
   
   private $parameterNames;
   
   private $map = [];

   private $class;
   
   public function __construct($class, array $parameters) {
      $this->class = $class;
      $this->parameterNames = $parameters;
   }
   
   public function offsetExists($offset) {
      return isset($this->map[$offset]);
   }

   public function offsetGet($offset) {
      if(!$this->offsetExists($offset)) {
         return null;
      }
      
      return $this->map[$offset];
   }

   public function offsetSet($offset, $value) {
      if(is_null($offset)) {
         throw new BadMethodCallException;
      }
      
      if(!in_array($offset, $this->parameterNames)) {
         throw new BadMethodCallException("Constructor parameter \"$offset\" does not exist for class \"$this->class\"");
      }
      
      $this->map[$offset] = $value;
   }
   
   public function offsetUnset($offset) {
      unset($this->map[$offset]);
   }
   
   public function saturated() {
      $isSaturated = true;
      foreach($this->parameterNames as $paramName) {
         if(!isset($this->map[$paramName])) {
            $isSaturated = false;
            break;
         }
      }
      return $isSaturated;
   }
   
   public function toAssocArray() {
      return $this->map;
   }
   
   public function fill($arr) {
      foreach($arr as $k => $v) {
         $this[$k] = $v;
      }
      return $this;
   }
   
   private function partition() {
      $got = array_keys($this->map);
      $expected = array_diff($this->parameterNames, $got);
      return [$got, $expected];
   }
   
   public function merge(ParameterMap $map) {
      $new = new self($this->class, $this->parameterNames);
      return $new->fill($map);
   }
   
   public function getIterator() {
      return new ArrayIterator($this->map);
   }
   
   public function __toString() {
      list($got, $expected) = $this->partition();
      return "Got: " . implode(', ', $got) . "\n" .
             "Still expecting: " . implode(', ', $expected) . "\n";
   }
   
}