<?php

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
      $fqn = $class->getShortName();
      $transFqn = $this->classNameMapper->to($fqn);
      $namespace = $class->getNamespaceName();
      $namespace = $namespace ? "namespace $namespace;" : '';
      return <<<EOT
$namespace
class $transFqn extends Schoenfinkel\Schoenfinkelize {
   protected static \$typeMap;
   protected static \$targetClassName = '$fqn';
}
EOT;
   }
  
}
