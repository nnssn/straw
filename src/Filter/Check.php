<?php

/**
 * Check filters
 */

namespace Straw\Filter;

use Straw\Rule\Rulable;

class Check
{
    /**
     * Check num
     * 
     * @param Rulable $rule
     * @return callable
     */
    public static function number(Rulable $rule)
    {
        $allow = $rule->info('allow');
        return function ($input) use ($allow) {
            $value = (int)$input;
            if (! $allow) {
                return $value;
            }
            return ($value < $allow[0] || $allow[1] < $value) ? null : $value;
        };
    }

    /**
     * Check num multiple
     * 
     * @param Rulable $rule
     * @return callable
     */
    public static function numberMultiple(Rulable $rule)
    {
        $allow = $rule->info('allow');
        return function (array $input) use ($allow) {
            $values = array_map(function ($v) {return (int)$v;}, $input);
            if (! $allow) {
                return $values;
            }
            foreach ($values as $v) {
                if ($v < $allow[0] || $allow[1] < $v) {
                    return null;
                }
            }
            return $values;
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
    public static function set($candidates)
    {
        return function ($input)use ($candidates) {
            $flip = array_flip($candidates);
            foreach ((array)$input as $value) {
                if (! isset($flip[(string)$value])) {
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
    public static function enum($candidates)
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
