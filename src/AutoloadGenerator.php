<?php

/**
 * Copyright (c) 2020 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MoonwalkingBits\Composer\Plugin\WordPress;

use Composer\Autoload\AutoloadGenerator as ComposerAutoloadGenerator;
use Composer\Package\PackageInterface;

class AutoloadGenerator extends ComposerAutoloadGenerator
{
    public function parseAutoloads(
        array $packageMap,
        PackageInterface $rootPackage,
        $filterOutRequireDevPackages = false
    ): array {
        if ($filterOutRequireDevPackages) {
            $packageMap = $this->filterPackageMap($packageMap, $rootPackage);
        }

        $wordpress = $this->parseAutoloadsType($packageMap, 'wordpress', $rootPackage);

        return compact('wordpress');
    }
}
