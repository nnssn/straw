<?php

namespace Nnssn\Straw\Core;

use Nnssn\Straw\Straw;
use Nnssn\Straw\Manual;
use Nnssn\Straw\Manuals\Standard;

/**
 * Creation of the array
 *
 * @author nnssn
 */
class Maker
{
    /**
     * @var Manual
     */
    private $manual;

    /**
     * @var array
     */
    private $source;

    /**
     * @var callable
     */
    private $complate_callback;

    /**
     * Construct
     * 
     * @param Manual $manual
     */
    public function __construct(Manual $manual=null)
    {
        $this->manual = ($manual) ?: new Standard;
        $this->manual->rules();
    }

    /**
     * Change the source
     * 
     * @param array $source
     * @return $this
     */
    public function source(array $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * After format
     * 
     * @param callable $callback
     * @return $this
     */
    public function complate(callable $callback)
    {
        $this->complate_callback = $callback;
        return $this;
    }

    /**
     * Return the results
     * 
     * @return array
     */
    public function make()
    {
        $sets  = array();
        $rules = Straw::getRules();
        foreach ($rules as $key => $rule) {
            $value = $this->getInputValue($key);
            $res   = $rule($value);
            ($res) and ($sets[] = $res);
        }
        $data     = static::build($sets);
        $complate = $this->decideComplate();
        return ($complate) ? $complate($data) : $data;
    }

    /**
     * Get the input value
     * 
     * @staticvar array $source
     * @param string $key
     * @return mixed
     */
    private function getInputValue($key)
    {
        $source = ($this->source) ?: $this->manual->source();
        return (isset($source[$key])) ? $source[$key] : null;
    }

    /**
     * Decide complate callback
     * 
     * @return callable|null
     */
    private function decideComplate()
    {
        if ($this->complate_callback) {
            return $this->complate_callback;
        }
        return $this->manual->complate();
    }

    /**
     * Build the valid datum
     * 
     * @param array $sets
     * @return array
     */
    private static function build(array $sets)
    {
        $result = array();
        foreach ($sets as $set) {
            extract($set);
            $keys = explode('.', $key);

            if (count($keys) == 1) {
                $result[$key] = $value;
                continue;
            }

            $target =& $result;
            while (count($keys) > 1) {
                $key = array_shift($keys);
                if (! isset($target[$key]) || ! is_array($target[$key])) {
                    $target[$key] = array();
                }
                $target =& $target[$key];
            }

            if (! $keys[0]) {
                $target[] = $value;
            }
            else {
                $target[array_shift($keys)] = $value;
            }
        }
        return $result;
    }
}
