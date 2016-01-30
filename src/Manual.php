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
     * @var mixed
     */
    protected $more;

    /**
     * Construct
     * 
     * @param mixed $more additional data
     */
    public function __construct($more = null)
    {
        $this->more = $more;
    }

    /**
     * Overwrite input source
     * 
     * return array
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
