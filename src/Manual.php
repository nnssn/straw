<?php

namespace Nnssn\Straw;

/**
 * Manual is the base class for
 *
 * @author nnssn
 */
abstract class Manual
{
    /**
     * Overwrite input source
     */
	public function source()
    {
        return null;
    }

    /**
     * After format
     * 
     * return callable
     */
	public function complate()
    {
        return null;
    }

	/**
     * Please write your rules.
     */
	abstract public function rules();
}
