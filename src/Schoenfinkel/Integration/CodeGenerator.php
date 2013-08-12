<?php

/*
 * This file is part of the php-partial-constructor package.
 * (c) Ruben Alexander de Gooijer <rubendegooijer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoenfinkel\Integration;

use \ReflectionClass;

class CodeGenerator {
   
   private $classNameMapper;
   
   public function __construct(ClassNameMapping $mapping) {
      $this->classNameMapper = $mapping;
   }
   
   public function getClassNameMapper() {
      return $this->classNameMapper;
   }
   
   public function getPartialClass(ReflectionClass $class) {
      $transFqn = $this->classNameMapper->to($class->getShortName());
      $namespace = $class->getNamespaceName();
      $namespace = $namespace ? "namespace $namespace;" : '';
      return <<<EOT
$namespace
class $transFqn extends \Schoenfinkel\Schoenfinkelize {
   protected static \$typeMap;
   protected static \$targetClassName = '{$class->name}';
}
EOT;
   }
  
}
