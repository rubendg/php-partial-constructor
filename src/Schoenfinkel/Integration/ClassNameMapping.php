<?php

namespace Schoenfinkel\Integration;

interface ClassNameMapping {
   public function from($className);
   public function to($className);
}
