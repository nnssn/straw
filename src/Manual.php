<?php

/**
 * Manual is the base class for
 */

namespace Straw;

class Manual
{
    protected $alpha;
    protected $alnum;
    protected $number;
    protected $sub_characters;

    protected $list;
    protected $pair;
    protected $range;
    protected $set;

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
        $this->configure();
    }

    /**
     * Get configure
     * 
     * @return array
     */
    final public function getConfigure()
    {
        return get_object_vars($this);
    }

    /**
     * Set configure
     */
    protected function configure()
    {
        $this->alpha  = 'a-zA-Z';
        $this->alnum  = 'a-zA-Z0-9';
        $this->number = '0-9';
        $this->sub_characters = '_';

        $this->list  = ',';
        $this->pair  = ':';
        $this->range = '-';
        $this->set   = ';';
    }

    /**
     * Overwrite input source
     * 
     * return array
     */
	public function source()
    {
        return $_GET;
    }

    /**
     * After format
     * 
     * return callable|null
     */
	public function complate()
    {
        return null;
    }

    /**
     * Your rules
     * 
     * @param Straw $s
     */
    public function rules(Straw $s)
    {
    }
}
