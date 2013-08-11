<?php

use \PHPUnit_Framework_TestCase;
use \Schoenfinkel\Schoenfinkelize;

class PerformanceTest extends PHPUnit_Framework_TestCase {

   public function testPerformance() {
      
      $loops = 1000;

      list($firstTotal, $firstAvg) = $this->doTest($loops, function(){
         $f = new Fooo('a', 'b');
      });

      list($secondTotal, $secondAvg) = $this->doTest($loops, function() {
         $f = new Fooo_;
         $f->apply([
            'a' => 'a',
            'b' => 'b'
         ]);
      });
      
      echo "Total first: $firstTotal, avg: $firstAvg\n";
      echo "Total second: $secondTotal, avg: $secondAvg\n";
      
      $totalDiff = $secondTotal - $firstTotal;
      $avgDiff = $secondAvg - $firstAvg;
      $timesFaster = $avgDiff / $firstAvg;

      echo "Difference: $totalDiff, avg: $avgDiff\n";
      echo "Plain object construction is $timesFaster times faster\n";
   }
   
   
   private function doTest($loops, callable $body) {
      $start = microtime(true);

      for ($i = 0; $i < $loops; $i++) {
         $body();
      }

      $total = microtime(true) - $start;
      $avg = $total / $loops;

      return [$total, $avg];
   }
   
}


class Fooo {
   public function __construct($a, $b) {
      
   }
}

class Fooo_ extends Schoenfinkelize {
   protected static $typeMap;
   protected static $targetClassName = 'Fooo';
}