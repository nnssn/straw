<?php

/**
 * The main class of this library
 */

namespace Straw;

use Straw\Rule\Rulable;
use Straw\Rule\Dummy;
use Straw\Rule\Regex;
use Straw\Rule\Sets;

class Straw
{
    protected static $configure;

    protected static $dummy;

    /**
     * @var Rulable[]
     */
    protected $rules = array();

    /**
     * @var Manual
     */
    protected $manual;

    /**
     * @var array
     */
    protected $source;

    protected $complete;

    /**
     * Get Dummy instance
     * 
     * @param string|string[] $key
     * @return Dummy
     */
    protected static function getDummy($key)
    {
        return (static::$dummy) 
            ? static::$dummy->setKey($key)
            : static::$dummy = Dummy::create($key);
    }

    /**
     * Get configure
     * 
     * @param string $key
     * @return array
     */
    public static function getConfigure($key)
    {
        return (isset(static::$configure[$key])) ? static::$configure[$key] : null;
    }

    /**
     * Create instance
     * 
     * @param array|Manual $source
     * @return static
     */
    public static function open($source = null)
    {
        return new static($source);
    }

    /**
     * Exists source
     * 
     * @param string|string[] $key
     * @return bool
     */
    private function existsSource($key)
    {
        foreach ((array)$key as $k) {
            if (! isset($this->source[$k])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Register rule
     * 
     * @param Rulable $rule
     * @return Rulable
     */
    private function register(Rulable $rule)
    {
        $key = $rule->info('key');
        $name = (is_array($key)) ? implode('\\', $key) : $key;
        $this->rules[$name] = $rule;
        return $this->rules[$name];
    }

    /**
     * Add regex rule
     * 
     * @param string|string[] $key
     * @param string|string[] $default
     * @param int $type
     * @return Rulable
     */
    private function addRegex($key, $default, $type)
    {
        return ($default !== null || $this->existsSource($key))
            ? $this->register(Regex::create($key, $default, $type))
            : self::getDummy($key);
    }

    /**
     * Add regex rule
     * 
     * @param string|string[] $key
     * @param string|string[] $default
     * @param int $type
     * @param array $candidates
     * @return Rulable
     */
    private function addSets($key, $default, $type, array $candidates)
    {
        return ($default !== null || $this->existsSource($key))
            ? $this->register(Sets::create($key, $default, $type, $candidates))
            : self::getDummy($key);
    }

    /**
     * Apply rules
     * 
     * @return array
     */
    private function collection()
    {
        $result = array();
        foreach ($this->rules as $rule) {
            $key = $rule->info('key');
            $source = array();

            if (! is_array($key)) {
                $source = $this->source[$key];
            }
            else {
                foreach ($key as $k) {
                    $source[] = $this->source[$k];
                }
            }
            $key_value = $rule($source);
            ($key_value) and ($result[] = $key_value);
        }
        return $result;
    }

    /**
     * Build the valid data
     * 
     * @param array $collection
     * @return array
     */
    private function build(array $collection)
    {
        $result = array();
        foreach ($collection as $set) {
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

            ($keys[0])
                ? $target[$keys[0]] = $value
                : $target[] = $value;
        }
        return $result;
    }

    /**
     * Construct
     * 
     * @param array|Manual $source
     */
    public function __construct($source = null)
    {
        $this->manual = ($source instanceof Manual) ? $source : new Manual;
        $this->source = (is_array($source))         ? $source : $this->manual->source();
        static::$configure = $this->manual->getConfigure();
        $this->manual->rules($this);
    }

    /**
     * Set complete callback
     * 
     * @param callable $callback
     * @return $this
     */
    public function complete($callback)
    {
        $this->complete = $callback;
        return $this;
    }

    /**
     * Make
     * 
     * @return array
     */
    public function make()
    {
        $collection = $this->collection();
        $values     = $this->build($collection);
        $complete   = $this->complete ?: $this->manual->complate();
        return ($complete) ? $complete($values) : $values;
    }

    /**
     * Add alpha rule
     * 
     * @param string $key
     * @param string|null $default
     * @param mixed $length
     * @return Rulable
     */
    public function alpha($key, $default = null, $length = null)
    {
        return $this->addRegex($key, $default, Regex::TYPE_NORMAL)->alpha($length);
    }

    /**
     * Add alphanumeric rule
     * 
     * @param string $key
     * @param string|null $default
     * @param mixed $length
     * @return Rulable
     */
    public function alnum($key, $default = null, $length = null)
    {
        return $this->addRegex($key, $default, Regex::TYPE_NORMAL)->alnum($length);
    }

    /**
     * Add integer rule
     * 
     * @param string $key
     * @param string|null $default
     * @param array $allow
     * @return Rulable
     */
    public function integer($key, $default = null, array $allow = array())
    {
        return $this->addRegex($key, $default, Regex::TYPE_NORMAL)->integer($allow);
    }

    /**
     * Add decimal rule
     * 
     * @param string $key
     * @param string|null $default
     * @param array $allow
     * @return Rulable
     */
    public function decimal($key, $default = null, array $allow = array())
    {
        return $this->addRegex($key, $default, Regex::TYPE_NORMAL)->decimal($allow);
    }

    /**
     * Add original rule
     * 
     * @param string $key
     * @param string|null $default
     * @param string $piece
     * @return Rulable
     */
    public function original($key, $default, $piece)
    {
        return $this->addRegex($key, $default, Regex::TYPE_NORMAL)->original($piece);
    }

    /**
     * Add alpha list rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param mixed $length
     * @return Rulable
     */
    public function alphaList($key, $default = null, $length = null)
    {
        return $this->addRegex($key, $default, Regex::TYPE_LIST)->alpha($length);
    }

    /**
     * Add alphanumeric list rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param mixed $length
     * @return Rulable
     */
    public function alnumList($key, $default = null, $length = null)
    {
        return $this->addRegex($key, $default, Regex::TYPE_LIST)->alnum($length);
    }

    /**
     * Add integer list rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param array $allow
     * @return Rulable
     */
    public function integerList($key, $default = null, array $allow = array())
    {
        return $this->addRegex($key, $default, Regex::TYPE_LIST)->integer($allow);
    }

    /**
     * Add decimal list rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param array $allow
     * @return Rulable
     */
    public function decimalList($key, $default = null, array $allow = array())
    {
        return $this->addRegex($key, $default, Regex::TYPE_LIST)->decimal($allow);
    }

    /**
     * Add original list rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param string $piece
     * @return Rulable
     */
    public function originalList($key, $default, $piece)
    {
        return $this->addRegex($key, $default, Regex::TYPE_LIST)->original($piece);
    }

    /**
     * Add alpha pair rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param mixed $length
     * @return Rulable
     */
    public function alphaPair($key, $default = null, $length = null)
    {
        return $this->addRegex($key, $default, Regex::TYPE_PAIR)->alpha($length);
    }

    /**
     * Add alphanumeric pair rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param mixed $length
     * @return Rulable
     */
    public function alnumPair($key, $default = null, $length = null)
    {
        return $this->addRegex($key, $default, Regex::TYPE_PAIR)->alnum($length);
    }

    /**
     * Add numeric pair rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param array $allow
     * @return Rulable
     */
    public function integerPair($key, $default = null, array $allow = array())
    {
        return $this->addRegex($key, $default, Regex::TYPE_PAIR)->integer($allow);
    }

    /**
     * Add decimal pair rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param array $allow
     * @return Rulable
     */
    public function decimalPair($key, $default = null, array $allow = array())
    {
        return $this->addRegex($key, $default, Regex::TYPE_PAIR)->decimal($allow);
    }

    /**
     * Add original pair rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param string $piece
     * @return Rulable
     */
    public function originalPair($key, $default, $piece)
    {
        return $this->addRegex($key, $default, Regex::TYPE_PAIR)->original($piece);
    }

    /**
     * Add num range rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param array $allow
     * @return Rulable
     */
    public function integerRange($key, $default = null, array $allow = array())
    {
        return $this->addRegex($key, $default, Regex::TYPE_RANGE)->integer($allow);
    }

    /**
     * Add num decimal rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param array $allow
     * @return Rulable
     */
    public function decimalRange($key, $default = null, array $allow = array())
    {
        return $this->addRegex($key, $default, Regex::TYPE_RANGE)->decimal($allow);
    }

    /**
     * Add datetime range rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param string $format
     * @return Rulable
     */
    public function datetimeRange($key, $default = null, $format = 'Ymd')
    {
        return $this->addRegex($key, $default, Regex::TYPE_RANGE)->datetime($format);
    }

    /**
     * Add bool rule
     * 
     * @param string $key
     * @param string|null $default
     * @param array $candidates
     * @return Rulable
     * @throws \InvalidArgumentException
     */
    public function bool($key, $default = null, array $candidates = array(0, 1))
    {
        if (count($candidates) !== 2) {
            throw new \InvalidArgumentException();
        }
        return $this->addSets($key, $default, Sets::TYPE_ENUM, $candidates);
    }

    /**
     * Add set rule
     * 
     * @param string|string[] $key
     * @param mixed $default
     * @param array $candidates
     * @return Rulable
     */
    public function set($key, $default, array $candidates)
    {
        return $this->addSets($key, $default, Sets::TYPE_SET, $candidates);
    }

    /**
     * Add enum rule
     * 
     * @param string $key
     * @param string|null $default
     * @param array $candidates
     * @return Rulable
     */
    public function enum($key, $default, array $candidates)
    {
        return $this->addSets($key, $default, Sets::TYPE_ENUM, $candidates);
    }
}
