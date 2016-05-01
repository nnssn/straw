<?php

/**
 * Validate Exception
 */

namespace Straw\Exceptions;

class ValidateException extends StrawException
{
    protected $value;

    /**
     * Create ValidateException
     * 
     * @param string|string[] $key
     * @param mixed $value
     * @return self
     */
    public static function create($key, $value)
    {
        $keys = (is_array($key)) ? explode(', ', $key) : $key;
        $message = sprintf('Failed to verification of "%s".', $keys);
        $e = new self($message, 20);
        $e->value = $value;
        return $e;
    }

    /**
     * Get input value
     * 
     * @return type
     */
    public function getValue()
    {
        return $this->value;
    }
}
