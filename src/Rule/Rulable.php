<?php

/**
 * Is the base class of the rule classes
 */

namespace Straw\Rule;

use Straw\Exceptions\ValidateException;

abstract class Rulable
{
    /**
     * @var string|string[]
     */
    protected $key;
    protected $default;

    protected $type;
    protected $delimiter = '';

    protected $to_key;
    protected $format;
    protected $required;

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
        $result = $value;
        foreach (array(1, 0) as $apply) {
            $result = ($apply) ? $this->validate($result) : $this->patch($result);
            if ($result !== null) {
                continue;
            }
            if (! $this->required) {
                return null;
            }
            throw ValidateException::create($this->key, $value);
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

    /**
     * Throw an exception when failed to verification
     * 
     * @see Dummy::required
     * @return $this
     */
    public function required()
    {
        $this->required = true;
        return $this;
    }
}
