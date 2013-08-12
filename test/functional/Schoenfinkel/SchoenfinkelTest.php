<?php

/*
 * This file is part of the php-partial-constructor package.
 * (c) Ruben Alexander de Gooijer <rubendegooijer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoenfinkel;

use \PHPUnit_Framework_TestCase;

class SchoenfinkelTest extends PHPUnit_Framework_TestCase {
      
   public function testPartialClass() {
      new \ReflectionClass_;
   }
   
   /**
    * @expectedException BadMethodCallException
    */
   public function testOnConstructionOverSaturation() {
      new InfectedColorApple_([
         'color' => new Color,
         'worm' => new Worm
      ]);
   }
   
   /**
    * @expectedException BadMethodCallException
    */
   public function testOverSaturation() {
      $a = new InfectedColorApple_;
      
      $a->apply([
         'color' => new Color,
         'worm' => new Worm,
         'undefined' => '',
      ]);
   }
   
   public function testUnorderedParams() {
      $a = new InfectedColorApple_;
      
      $this->assertInstanceOf('Schoenfinkel\InfectedColorApple', $a->apply([
         'color' => new Color,
         'worm' => new Worm
      ]));
      
      $this->assertInstanceOf('Schoenfinkel\InfectedColorApple', $a->apply([
         'worm' => new Worm,
         'color' => new Color
      ]));
   }
   
   public function testDuplicateParam() {
      $a = new InfectedColorApple_;
      
      $c = new Color;
      $apple = $a->apply([
         'color' => new Color,
         'color' => $c,
         'worm' => new Worm
      ]);
      
      $this->assertEquals($c, $apple->color);
   }
   
   public function testTypePreservation() {
      $a = new InfectedColorApple_;
      
      $this->assertInstanceOf('Schoenfinkel\InfectedColorApple_', $a->apply([
         'color' => new Color
      ]));
   }
   
   public function testEnvironmentAccess() {
      $a = new InfectedColorApple_;
      
      $worm = new Worm;
      $aWithWorm = $a->apply([
         'worm' => $worm
      ]);

      $this->assertInstanceOf('Schoenfinkel\InfectedColorApple_', $aWithWorm);
      
      $this->assertEquals($worm, $aWithWorm['worm']);
   }
   
   public function testImmutability() {
      $a = new InfectedColorApple_;

      $this->assertInstanceOf('Schoenfinkel\InfectedColorApple_', $a->apply([
         'worm' => new Worm
      ]));
      
      $this->assertInstanceOf('Schoenfinkel\InfectedColorApple_', $a->apply([
         'color' => new Color
      ]));
   }

   /**
    * @expectedException InvalidArgumentException
    */
   public function testTypeCheckingObject() {
      $a = new InfectedColorApple_;

      $a->apply([
         'worm' => new Color
      ]);
   }
   
    /**
    * @expectedException InvalidArgumentException
    */
   public function testTypeCheckingArray() {
      $a = new Foo_;

      $a->apply([
         'a' => 'test'
      ]);
   }
   
    /**
    * @expectedException InvalidArgumentException
    */
   public function testTypeCheckingCallable() {
      $a = new Bar_;

      $a->apply([
         'f' => 'function(){}'
      ]);
   }
   
   public function testTypeCheckingAnything() {
      $a = new FooBar_;

      $a->apply([
         'a' => 'b',
         'b' => 'c'
      ]);
   }
   
}

class Color {}
class Worm {}

class InfectedColorApple {
   public $color;
   public function __construct(Color $color, Worm $worm) {
      $this->color = $color;
   }
}

class InfectedColorApple_ extends Schoenfinkelize {
   protected static $typeMap;
   protected static $targetClassName = 'Schoenfinkel\InfectedColorApple';
}

class Foo {
   public function __construct(array $a, array $b) {
      
   }
}

class Foo_ extends Schoenfinkelize {
   protected static $typeMap;
   protected static $targetClassName = 'Schoenfinkel\Foo';
}

class Bar {
   public function __construct(callable $f, callable $g) {
      
   }
}

class Bar_ extends Schoenfinkelize {
   protected static $typeMap;
   protected static $targetClassName = 'Schoenfinkel\Bar';
}

class FooBar {
   public function __construct($a, $b) {
      
   }
}

class FooBar_ extends Schoenfinkelize {
   protected static $typeMap;
   protected static $targetClassName = 'Schoenfinkel\FooBar';
}