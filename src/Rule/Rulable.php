<?php

/**
 * Is the base class of the rule classes
 */

namespace Straw\Rule;

abstract class Rulable
{
    protected $key;
    protected $default;

    protected $type;
    protected $delimiter = '';

    protected $to_key;
    protected $format;

    /**
     * Validate
     * 
     * @param string|string[] $value
     * @return mixed
     */
    abstract protected function validate($value);

    /**
     * Patch the filter & after
     * 
     * @param string $valid
     * @return mixed
     */
    protected function patch($valid)
    {
        $filter    = FilterSelector::get($this);
        $format    = $this->format;
        $filtered  = ($filter) ? $filter($valid)    : $valid;
        $formatted = ($format) ? $format($filtered) : $filtered;
        return $formatted;
    }

    /**
     * Apply
     * 
     * @param stiring $value
     * @return mixed
     */
    public function __invoke($value)
    {
        $valid = $this->validate($value);
        if ($valid === null) {
            return null;
        }
        $result = $this->patch($valid);
        if ($result === null) {
            return null;
        }

        $key = ($this->to_key) ?: (is_array($this->key) ? implode('', $this->key) : $this->key);
        return array(
            'key'   => $key,
            'value' => $result,
        );
    }

    /**
     * Get object property
     * 
     * @param string $key
     * @return string|array|null
     */
    public function info($key)
    {
        return (property_exists($this, $key)) ? $this->{$key} : null;
    }

    /**
     * Cehck Rule type
     * 
     * @param int $type
     * @return bool
     */
    public function types($type)
    {
        return $this->type === $type;
    }

    /**
     * Change output key
     * 
     * @param string $key
     * @return $this
     */
    public function to($key)
    {
        $this->to_key = $key;
        return $this;
    }

    /**
     * After filter
     * 
     * @param callable $callback
     * @return $this
     */
    public function format($callback)
    {
        $this->format = $callback;
        return $this;
    }
}
