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
      foreach($this->map as $k => $v) {
         if(is_null($v)) {
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
      $got = [];
      $expected = [];
      foreach($this as $k => $v) {
         if(is_null($v)) {
            $expected[] = $k;
         } else {
            $got[] = $k;
         }
      }
      
      return [$got, $expected];
   }
   
   public function merge(ParameterMap $map) {
      $new = new self($this->class, $this->parameterNames);
      foreach($this->parameterNames as $k) {
         if(is_null($map[$k])) {
            $new[$k] = $this[$k];
         } else {
            $new[$k] = $map[$k];
         }
      }
      return $new;
   }
   
   public function getIterator() {
      return new ArrayIterator($this->map);
   }
   
   public function __toString() {
      list(, $expected) = $this->partition();
      $expects = $this->keys();
      return "Expects: " . implode(', ', $expects) . "\n" .
             "Missing: " . implode(', ', $expected) . "\n";
   }
   
}