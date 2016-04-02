<?php

/**
 * Filter selector
 */

namespace Straw\Rule;

use Straw\Filter\Check;
use Straw\Filter\Cast;

class FilterSelector
{
    /**
     * Get filter
     * 
     * @param Rulable $rule
     * @return callable|null
     */
    public static function get(Rulable $rule)
    {
        if ($rule instanceof Regex) {
            return self::regex($rule);
        }
        return null;
    }

    /**
     * For Regex
     * 
     * @param Regex $rule
     * @return callable|null
     */
    public static function regex(Regex $rule)
    {
        $filters = array();
        $is_multiple = (! $rule->types(Regex::TYPE_NORMAL));

        ($is_multiple) and ($filters[] = Cast::explode($rule));
        if ($rule->types(Regex::TYPE_RANGE)) {
            $filters[] = Cast::fillSide($rule);
            $filters[] = Check::greaterThanOrEqual();
            ($rule->info('is_datetime')) and ($filters[] = Cast::datetime($rule));
        }
        if ($rule->info('is_number')) {
            $filters[] = ($is_multiple) ? Check::numberMultiple($rule) : Check::number($rule);
        }

        $count = count($filters);
        if (! $count) {
            return null;
        }
        if ($count === 1) {
            return $filters[0];
        }
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


}
