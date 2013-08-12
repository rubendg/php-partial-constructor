# Schönfinkelize your constructor - partial constructor application for PHP

[![Build Status](https://api.travis-ci.org/rubendg/php-partial-constructor.png?branch=master)](http://travis-ci.org/rubendg/php-partial-constructor)

A library for partially applying object constructors. It puts you in control
over *when* you provide *which* constructor arguments, instead of all or none.

A quick example. Suppose you have a class *Foo*:

```php
class Foo {
   public function __construct(A $a, B $b) {}
}
```

and you want to provide *A*, but provide *B* at a later moment. Normally this would not 
be possible, and one has to reach to other means like using a setter:

```php
class Foo {
   public function __construct(A $a) {}

   public function setB(B $b) {}
}
```

This would however imply that every method relying on the presence of *B* will
need an additional consistency check. If *B* really is an integral part of what
it means to be a *Foo* it should belong in the constructor, not in a setter.

Using the library it is possible to maintain the previous design with a little
extra help:

```php
$foo = new Foo_;

$fooWithA = $foo->apply(['a' => new A]);
```

Note that we did not create an instance of *Foo* but of *Foo_*. 
*Foo_* is actually a wrapper around the construction process of *Foo*. It
provides you with an *apply* method that takes an unordered list of 
constructor parameters and dependent on whether all arguments are provided 
instantiates the target class *Foo*. 

All you need to do is create a companion class for *Foo* called *Foo_*: 

```php
class Foo_ implements \ArrayAccess {
   use Schoenfinkel\Schoenfinkelize;
   protected static $targetClassName = 'Foo';
}
```

Where "$targetClassName" points to the target class.

Obviously creating these companion classes quickly becomes tiresome. You can
you can get rid of the manual boilerplate by generating the requested
classes on the fly.

If you are in a [Composer](http://getcomposer.org/) project all you have to do 
is create your own "autoload.php" file adapting the one generated by Composer:

```php
use \Schoenfinkel\Integration\CodeGenerator;
use \Schoenfinkel\Integration\PostfixClassNameMapping;
use \Schoenfinkel\Integration\ClassLoader\SchoenfinkelClassLoader;

$loader = require __DIR__ . '/../vendor/autoload.php';
$custom = new SchoenfinkelClassLoader($loader, new CodeGenerator(new PostfixClassNameMapping()));
$loader->unregister();
spl_autoload_register(array($custom, 'loadClass'));
```

By default classes which have an underscore at the end will be treated
as classes that can have their constructor partially applied. The class
name without the underscore will become the target class name. Of course you
can change this behavior to your own liking by either setting a different
postfix on the *PostfixClassNameMapping* or implement your own *ClassNameMapping*.

Note that currently the implementation uses *eval* for bringing the generated code into scope. 
This may be a problem for you if you have disabled *eval* in the [disabled_functions](http://php.net/manual/en/ini.core.php#ini.disable-functions) directive.

To sum up some of the advantages of using the library:

- For simple cases replaces to need for applying the [builder pattern](http://en.wikipedia.org/wiki/Builder_pattern)
- No more setters for attributes that should be provided in the constructor
- Type hinting support for partially constructed classes (albeit of minimal expressiveness see disadvantages)
- Until all parameters are provided every parameter can accessed by name and or overwritten
- Partially applied objects can be passed around like ordinary classes
- Independent of dependency injection framework
- Type hints of target class are checked during partial application ([fail-fast](http://en.wikipedia.org/wiki/Fail-fast))
- The library also plays nice with [Symfony DIC](http://symfony.com/blog/symfony-components-the-dependency-injection-container).
  A subset of the actual constructor parameters can be passed directly into the partial constructor:

```xml
<service id="a" class="A"/>

<service id="foo" class="Foo_">
   <argument type="collection">
      <argument key="a" id="a" type="service"/>
   </argument>
</service>
```
         
Disadvantages:

- Induces a slight performance overhead. 

```bash
./vendor/bin/phpunit test/performance/PerformanceTest.php
```

  Shows that plain object construction is about 24 times faster.
- Does not work for constructors that take a variable amount of arguments (using func_get_args())
- Default and optional arguments are treated as regular (required) arguments. 
- Currently the class facilitating partial construction does not take on any of the types that its target
  class might have. Hence type hinting for partial classes is of limited expressiveness.

Future:

- Maybe provide class generation based on PHP annotations @Curried
- Maybe replace *eval* with something like this: http://www.whitewashing.de/2010/12/18/generate-proxy-code-using-a-stream-wrapper.html 
- Lift the restriction put up at the last point of the disadvantages listing.

Related:

- Constructor currying with Google Guice: http://slesinsky.org/brian/code/guice_with_curry.html
- PHP function currying: https://github.com/reactphp/curry
- PHP functional prelude: https://github.com/lstrojny/functional-php
- Currying vs partial application: http://allthingsphp.blogspot.nl/2012/02/currying-vs-partial-application.html
