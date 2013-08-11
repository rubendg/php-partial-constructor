<?php

namespace Schoenfinkel\Integration;

class PostfixClassNameMapping implements ClassNameMapping {

   private $postfix = '_';
   
   public function setPostfix($postfix) {
      $this->postfix = $postfix;
   }

   public function from($className) {
      $start = strlen($className) - strlen($this->postfix);
      if(substr($className, $start) === $this->postfix) {
         return substr($className, 0, $start);
      }
      return false;
   }

   public function to($className) {
      return $className . $this->postfix;
   }   
}
