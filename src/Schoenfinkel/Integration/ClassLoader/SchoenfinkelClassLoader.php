<?php

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

}