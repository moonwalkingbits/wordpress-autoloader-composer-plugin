<?php

/**
 * Copyright (c) 2020 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MoonwalkingBits\Composer\Plugin\WordPress;

trait MergesAssociativeArrayTrait
{
    protected function mergeAssociativeArrays(array $a, array $b, string $strategy): array
    {
        foreach ($b as $key => $value) {
            if (!array_key_exists($key, $a)) {
                $a[$key] = $value;

                continue;
            }

            if ($this->isAssociativeArray($a[$key]) && $this->isAssociativeArray($value)) {
                $a[$key] = $this->mergeAssociativeArrays($a[$key], $value, $strategy);

                continue;
            }

            if ($this->shouldMergeIndexedArrays($strategy) && is_array($a[$key]) && is_array($value)) {
                $a[$key] = array_values(array_unique(array_merge($a[$key], $value)));

                continue;
            }

            $a[$key] = $value;
        }

        return $a;
    }

    private function isAssociativeArray($value): bool
    {
        return is_array($value) && count(array_filter(array_keys($value), 'is_string')) > 0;
    }

    private function shouldMergeIndexedArrays(string $strategy): bool
    {
        return $strategy === MergeStrategy::MERGE_INDEXED_ARRAYS;
    }
}
