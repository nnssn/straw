<?php

/**
 * Dummy rule
 */

namespace Straw\Rule;

use Straw\Exceptions\RequiredException;

class Dummy extends Rulable
{
    /**
     * Create
     * 
     * @param string|string[] $key
     * @return self
     */
    public static function create($key)
    {
        $dummy = new self();
        return $dummy->setKey($key);
    }

    /**
     * Validate
     * 
     * @param string|string[] $value
     * @return false
     */
    protected function validate($value)
    {
        return ($value) ? null : null;
    }

    /**
     * Dummy
     * 
     * @param string $name
     * @param array $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        return $this;
    }

    /**
     * Throw an exception when there is no data
     * 
     * @see Rulable::required
     * @throws \Straw\Exceptions\RequiredException
     */
    public function required()
    {
        throw RequiredException::create($this->key);
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
}
