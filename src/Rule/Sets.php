<?php

/**
 * Sets type rule
 */

namespace Straw\Rule;

use Straw\Straw;
use Straw\Filter\Check;
use Straw\Filter\Cast;

class Sets extends Rulable
{
    const TYPE_SET  = 1;
    const TYPE_ENUM = 2;

    protected $candidates;

    /**
     * Create
     * 
     * @param string|string[] $key
     * @param string|string[] $default
     * @param string $type
     * @param array $candidates
     * @return self
     */
    public static function create($key, $default, $type, array $candidates)
    {
        $rule = new self();
        $rule->key        = $key;
        $rule->default    = $default;
        $rule->type       = $type;
        $rule->candidates = $candidates;
        $rule->delimiter  = ($rule->types(self::TYPE_SET)) ? Straw::getConfigure('set') : null;
        return $rule;
    }

    /**
     * Validate
     * 
     * @param string|string[] $value
     * @return mixed
     */
    protected function validate($value)
    {
        return ($this->type === self::TYPE_SET)
            ? $this->validateSet($value)
            : $this->validateEnum($value);
    }

    /**
     * Validate set
     * 
     * @param string[] $values
     * @return array|null
     */
    protected function validateSet($values)
    {
        $explode = Cast::explode($this);
        $set     = Check::set($this->candidates);
        return $set($explode($values));
    }

    /**
     * Validate enum
     * 
     * @param string|string[] $value
     * @return string|null
     */
    protected function validateEnum($value)
    {
        $enum = Check::enum($this->candidates);
        return $enum($value);
    }
}
