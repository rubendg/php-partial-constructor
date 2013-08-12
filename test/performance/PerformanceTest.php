<?php

/*
 * This file is part of the php-partial-constructor package.
 * (c) Ruben Alexander de Gooijer <rubendegooijer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

class Fooo_ {
   use Schoenfinkelize;
   protected static $targetClassName = 'Fooo';
}