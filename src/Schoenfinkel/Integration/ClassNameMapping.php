<?php

/*
 * This file is part of the php-partial-constructor package.
 * (c) Ruben Alexander de Gooijer <rubendegooijer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoenfinkel\Integration;

interface ClassNameMapping {
   public function from($className);
   public function to($className);
}
