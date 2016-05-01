<?php

/**
 * Cast filters
 */

namespace Straw\Filter;

use Straw\Rule\Rulable;

class Cast
{
    /**
     * Mix the filter callbacks
     * 
     * @param callable[] $filters
     * @return callable
     */
    public static function mix(array $filters)
    {
        return function ($input) use ($filters) {
            foreach ($filters as $filter) {
                $input = $filter($input);
                if ($input === null) {
                    return null;
                }
            }
            return $input;
        };
    }

    /**
     * Explode
     * 
     * @param Rulable $rule
     * @return callable
     */
    public static function explode(Rulable $rule)
    {
        $delimiter = $rule->info('delimiter');
        return function ($input) use ($delimiter) {
            return (is_array($input)) ? $input : explode($delimiter, $input);
        };
    }

    /**
     * Integer
     * 
     * @return callable
     */
    public static function integer()
    {
        return function ($input) {
            return (is_array($input)) ? array_map('intval', $input) : (int)$input;
        };
    }

    /**
     * Decimal
     * 
     * @return callable
     */
    public static function decimal()
    {
        return function ($input) {
            return (is_array($input)) ? array_map('floatval', $input) : (float)$input;
        };
    }

    /**
     * Create DateTimeImmutable instance
     * 
     * @param Rulable $rule
     * @return callable
     * @throws \RuntimeException
     */
    public static function datetime(Rulable $rule)
    {
        $delimiter = $rule->info('delimiter');
        $format    = $rule->info('datetime_format');
        if (strpos($format, $delimiter) !== false) {
            throw new \RuntimeException('A delimiter is included in a character string.');
        }
        return function ($input) use ($format) {
            if (is_array($input)) {
                $start = \DateTimeImmutable::createFromFormat($format, $input[0]);
                $end   = \DateTimeImmutable::createFromFormat($format, $input[1]);
                return ($start && $end) ? array($start, $end) : null;
            }
            return (\DateTimeImmutable::createFromFormat($input)) ?: null;
        };
    }

    /**
     * Fill the side missing
     * 
     * @param Rulable $rule
     * @return callable
     */
    public static function fillSide(Rulable $rule)
    {
        $default   = $rule->info('default');
        $delimiter = $rule->info('delimiter');
        return function ($values) use ($default, $delimiter) {
            if (implode($delimiter, $values) === $delimiter) {
                return null;
            }
            $fill = array_filter(explode($delimiter, $default), 'strlen');
            if (count($fill) === 2) {
                (! $values[0] && $values[0] !== '0') and ($values[0] = $fill[0]);
                (! $values[1] && $values[1] !== '0') and ($values[1] = $fill[1]);
            }
            return $values;
        };
    }
}
