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
    private $delimiter;

    private $to_key;
    private $format;

    /**
     * Construct
     * 
     * @param string|string[] $key
     * @param string|null $default
     * @param string $pattern
     * @param callable|null $filter
     * @param string $delimiter
     */
    public function __construct($key, $default, $pattern, $filter, $delimiter = '')
    {
        $this->key       = $key;
        $this->default   = ($default) ? (string)$default : null;
        $this->pattern   = $pattern;
        $this->filter    = $filter;
        $this->delimiter = $delimiter;
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
        if (is_array($value)) {
            $value = implode($this->delimiter, $value);
        }
        $value       = (string)$value;
        $res_input   = null;
        $res_default = null;
        if ($value || $value === '0') {
            (preg_match($this->pattern, $value)) and ($res_input = $value);
        }
        if (! $res_input && ($this->default || $this->default === '0')) {
            (preg_match($this->pattern, $this->default)) and ($res_default = $this->default);
        }
        if ($res_input === null && $res_default === null) {
            return null;
        }

        $use_value    = $res_input ?: $res_default;
        $return_value = $this->patch($use_value);
        if ($return_value === null) {
            return null;
        }

        $key = (is_array($this->key)) ? implode('', $this->key) : $this->key;
        return array(
            'key'   => ($this->to_key) ?: $key,
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

        $filterd  = ($filter) ? $filter($value)   : $value;
        $formated = ($format) ? $format($filterd) : $filterd;
        return $formated;
    }
}
