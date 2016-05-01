<?php

/**
 * Required Exception
 */

namespace Straw\Exceptions;

class RequiredException extends StrawException
{
    /**
     * Create RequiredException
     * 
     * @param string|string[] $key
     * @return self
     */
    public static function create($key)
    {
        $keys = (is_array($key)) ? explode(', ', $key) : $key;
        $message = sprintf('No value of an essential "%s" is input.', $keys);
        return new self($message, 10);
    }
}
