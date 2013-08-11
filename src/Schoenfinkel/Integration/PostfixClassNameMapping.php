<?php

/*
 * This file is part of the php-partial-constructor package.
 * (c) Ruben Alexander de Gooijer <rubendegooijer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
