<?php

namespace Nnssn\Straw\Core;

/**
 * It represents one of the rules
 *
 * @author nnssn
 */
class Rule
{
    private $key;
    private $default;
    private $pattern;
    private $filter;

    private $to_key;
    private $format;

    /**
     * Construct
     * 
     * @param string $key
     * @param string|null $default
     * @param string $pattern
     * @param callable|null $filter
     */
    public function __construct($key, $default, $pattern, $filter)
    {
        $this->key     = $key;
        $this->default = $default;
        $this->pattern = $pattern;
        $this->filter  = $filter;
    }

    /**
     * Change the output key
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
     * Format he value
     * 
     * @param callable $callback
     * @return $this
     */
    public function format(callable $callback)
    {
        $this->format = $callback;
        return $this;
    }

    /**
     * Apply
     * 
     * @param stiring $value
     * @return mixed
     */
    public function __invoke($value)
    {
        $res_input   = null;
        $res_default = null;
        if ($value || $value === '0') {
            (preg_match($this->pattern, $value)) and ($res_input = $value);
        }
        if (! $res_input && ($this->default || $this->default === '0')) {
            (preg_match($this->pattern, $this->default)) and ($res_default = $this->default);
        }
        if (! $res_input && ! $res_default) {
            return null;
        }

        $use_value = ($res_input) ?: $res_default;
        $return_value = $this->patch($use_value);
        if (! $return_value) {
            return null;
        }
        return array(
            'key'   => ($this->to_key) ?: $this->key,
            'value' => $return_value,
        );
    }

    /**
     * Patch the filter & format
     * 
     * @param string $value
     * @return mixed
     */
    private function patch($value)
    {
        $filter   = $this->filter;
        $format   = $this->format;

        $filterd  = ($filter) ? $filter($value) : $value;
        $formated = ($format) ? $format($filterd) : $filterd;
        return $formated;
    }
}
