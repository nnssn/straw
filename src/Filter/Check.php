<?php

/**
 * Check filters
 */

namespace Straw\Filter;

use Straw\Rule\Rulable;

class Check
{
    /**
     * Check in a range
     * 
     * @param Rulable $rule
     * @return callable
     */
    public static function inRange(Rulable $rule)
    {
        $allow = $rule->info('allow');
        if (! $allow) {
            return null;
        }
        return function ($input) use ($allow) {
            if (! is_array($input)) {
                return ($input < $allow[0] || $allow[1] < $input) ? null : $input;
            }
            foreach ($input as $v) {
                if ($v < $allow[0] || $allow[1] < $v) {
                    return null;
                }
            }
            return $input;
        };
    }

    /**
     * Check greater than or equal
     * 
     * @return callable
     */
    public static function greaterThanOrEqual()
    {
        return function ($values) {
            if (! is_array($values) || ! isset($values[0]) || ! isset($values[1])) {
                return null;
            }
            return ($values[0] <= $values[1]) ? $values : null;
        };
    }

    /**
     * Set
     * 
     * @param string[] $candidates
     * @return array|null
     */
    public static function set(array $candidates)
    {
        return function ($input) use ($candidates) {
            $flip = array_flip($candidates);
            foreach ((array)$input as $v) {
                if (! isset($flip[(string)$v])) {
                    return null;
                }
            }
            return $input;
        };
    }

    /**
     * Enum
     * 
     * @param string|string[] $candidates
     * @return string|null
     */
    public static function enum(array $candidates)
    {
        return function ($input) use ($candidates) {
            if (! is_string($input) && ! is_numeric($input)) {
                return null;
            }
            $flip = array_flip($candidates);
            return (isset($flip[(string)$input])) ? $input : null;
        };
    }
}
