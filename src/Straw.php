<?php

namespace Nnssn\Straw;

/**
 * The main class of this library
 *
 * @author nnssn
 */
class Straw
{
    private static $pattenrs = array(
        'alpha'    => 'a-zA-Z',
        'num'      => '0-9',
        'alphanum' => 'a-zA-Z0-9',
    );

    protected static $delimiters = array(
        'list'  => ',',
        'set'   => ';',
        'range' => '-',
    );

    protected static $allow_alpha_subs = '_';

    /**
     * @var Core\Rule[]
     */
    protected static $rules = array();

    /**
     * Set options
     * 
     * @param array $options
     */
    public static function options(array $options)
    {
        static::$pattenrs   = $options + static::$pattenrs;
        static::$delimiters = $options + static::$delimiters;
        if (isset($options['allow_alpha_subs'])) {
            static::$allow_alpha_subs = $options['allow_alpha_subs'];
        }
    }

    /**
     * Create Core\Maker instance
     * 
     * @return Core\Maker
     */
    public static function open(Manual $manual=null)
    {
        return new Core\Maker($manual);
    }

    /**
     * Return the registered rules
     * 
     * @return Core\Rule[]
     */
    public static function getRules()
    {
        return static::$rules;
    }

    /**
     * Make normal pattern
     * 
     * @param string $chars
     * @return string
     */
    private static function pattern($chars)
    {
        $main = ($chars === static::$pattenrs['num']) ? $chars : $chars . static::$allow_alpha_subs;
        return sprintf('/\A[%s]+\Z/', $main);
    }

    /**
     * Make multi pattern
     * 
     * @param string $chars
     * @param string $delimiter
     * @param string $format
     * @return string
     */
    private static function patternMulti($chars, $delimiter, $format)
    {
        $main    = ($chars === static::$pattenrs['num']) ? $chars : $chars . static::$allow_alpha_subs;
        $search  = array(':main', ':delimiter');
        $replace = array($main, $delimiter);
        return str_replace($search, $replace, $format);
    }

    /**
     * Make list pattern
     * 
     * @param string $chars
     * @param string $delimiter
     * @return string
     */
    private static function patternList($chars, $delimiter)
    {
        $format = '/\A([:main]|:delimiter(?!:delimiter))*[:main]\Z/';
        return static::patternMulti($chars, $delimiter, $format);
    }

    /**
     * Make range pattern
     * 
     * @param string $chars
     * @return string
     */
    private static function patternRange($chars)
    {
        $format = '/\A([:main]+){0,1}:delimiter([:main]+){0,1}\Z/';
        return static::patternMulti($chars, static::$delimiters['range'], $format);
    }

    /**
     * Register rule
     * 
     * @param string $key
     * @param string|null $default
     * @param string $pattern
     * @param callable $filter
     * @return Core\Rule
     */
    private static function register($key, $default, $pattern, callable $filter=null)
    {
        static::$rules[$key] = new Core\Rule($key, $default, $pattern, $filter);
        return static::$rules[$key];
    }

    /**
     * Register list type rule
     * 
     * @param string $key
     * @param string|null $default
     * @param string $pattern
     * @param string $delimiter
     * @param array $allow
     * @return Core\Rule
     */
    private static function registerList($key, $default, $pattern, $delimiter=null, $allow=null)
    {
        $filter = function ($input) use ($delimiter, $allow) {
            $values = explode($delimiter, $input);
            if (is_array($allow)) {
                $values = array_map(function ($v) {return (int)$v;}, $values);
            }
            if ($allow) {
                foreach ($values as $v) {
                    if ($v < $allow[0] || $allow[1] < $v) {
                        return null;
                    }
                }
            }
            if ($delimiter === static::$delimiters['set'] && $values !== array_unique($values)) {
                return null;
            }
            return $values;
        };
        return static::register($key, $default, $pattern, $filter);
    }

    /**
     * Register range type rule
     * 
     * @param string $key
     * @param string|null $default
     * @param string $pattern
     * @param callable $filter
     * @return Core\Rule
     */
    private static function registerRange($key, $default, $pattern, callable $filter=null)
    {
        $range_filter = function ($input) use ($default) {
            if ($input === static::$delimiters['range']) {
                return null;
            }
            $values = explode(static::$delimiters['range'], $input);
            $fills  = array_filter(explode(static::$delimiters['range'], $default), 'strlen');
            if ((! $values[0] || ! $values[1]) && count($fills) === 2) {
                (! $values[0]) and ($values[0] = $fills[0]);
                (! $values[1]) and ($values[1] = $fills[1]);
            }
            if ($values[0] > $values[1]) {
                return null;
            }
            return (count(array_filter($values, 'strlen')) === 2) ? $values : null;
        };
        $filters = (! $filter)
                ? $range_filter
                : function ($input) use ($filter, $range_filter) {
                      $values = $range_filter($input);
                      return ($values) ? $filter($values) : null;
                  };
        return static::register($key, $default, $pattern, $filters);
    }

    /**
     * 
     * Add original rule
     * 
     * @param string $key
     * @param string $pattern
     * @param string $default
     * @return Core\Rule
     */	
    public static function newRule($key, $pattern, $default=null)
    {
        return static::register($key, $default, $pattern);
    }

    /**
     * Add boolean rule
     * 
     * @param string $key
     * @param string|null $default
     * @return Core\Rule
     */
    public static function bool($key, $default=null)
    {
        $filter = function ($value) {
            return (int)$value;
        };
        return static::register($key, $default, '/\A(0|1)\Z/', $filter);
    }

    /**
     * Add alpha rule
     * 
     * @param string $key
     * @param string $default
     * @return Core\Rule
     */
    public static function alpha($key, $default=null)
    {
        $pattern = static::pattern(static::$pattenrs['alpha']);
        return static::register($key, $default, $pattern);
    }

    /**
     * Add numeric rule
     * 
     * @param string $key
     * @param string $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function num($key, $default=null, array $allow=array())
    {
        $pattern = static::pattern(static::$pattenrs['num']);
        $filter = function ($value) use ($allow) {
            $v = (int)$value;
            if (! $allow) {
                return $v;
            }
            return ($allow[0] <= $v && $v <= $allow[1]) ? $v : null;
        };
        return static::register($key, $default, $pattern, $filter);
    }

    /**
     * Add alphanumeric rule
     * 
     * @param string $key
     * @param string $default
     * @return Core\Rule
     */
    public static function alphanum($key, $default=null)
    {
        $pattern = static::pattern(static::$pattenrs['alphanum']);
        return static::register($key, $default, $pattern);
    }

    /**
     * Add alpha list rule
     * 
     * @param string $key
     * @param string $default
     * @return Core\Rule
     */
    public static function alphaList($key, $default=null)
    {
        $pattern = static::patternList(static::$pattenrs['alpha'], static::$delimiters['list']);
        return static::registerList($key, $default, $pattern, static::$delimiters['list']);
    }

    /**
     * Add numeric list rule
     * 
     * @param string $key
     * @param string $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function numList($key, $default=null, array $allow=array())
    {
        $pattern = static::patternList(static::$pattenrs['num'], static::$delimiters['list']);
        return static::registerList($key, $default, $pattern, static::$delimiters['list'], $allow);
    }

    /**
     * Add alphanumeric list rule
     * 
     * @param string $key
     * @param string $default
     * @return Core\Rule
     */
    public static function alphanumList($key, $default=null)
    {
        $pattern = static::patternList(static::$pattenrs['alphanum'], static::$delimiters['list']);
        return static::registerList($key, $default, $pattern, static::$delimiters['list']);
    }

    /**
     * Add alpha set rule
     * 
     * @param string $key
     * @param string $default
     * @return Core\Rule
     */
    public static function alphaSet($key, $default=null)
    {
        $pattern = static::patternList(static::$pattenrs['alpha'], static::$delimiters['set']);
        return static::registerList($key, $default, $pattern, static::$delimiters['set']);
    }

    /**
     * Add numeric set rule
     * 
     * @param string $key
     * @param string $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function numSet($key, $default=null, array $allow=array())
    {
        $pattern = static::patternList(static::$pattenrs['num'], static::$delimiters['set']);
        return static::registerList($key, $default, $pattern, static::$delimiters['set'], $allow);
    }

    /**
     * Add alphanumeric set rule
     * 
     * @param string $key
     * @param string $default
     * @return Core\Rule
     */
    public static function alphanumSet($key, $default=null)
    {
        $pattern = static::patternList(static::$pattenrs['alphanum'], static::$delimiters['set']);
        return static::registerList($key, $default, $pattern, static::$delimiters['set']);
    }

    /**
     * Add num range rule
     * 
     * @param string $key
     * @param string|null $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function numRange($key, $default=null, array $allow=null)
    {
        $pattern = static::patternRange(static::$pattenrs['num']);
        $filter = function ($values) use ($allow) {
            $values = array_map(function ($v) {return (int)$v;}, $values);
            if (! $allow) {
                return $values;
            }
            foreach ($values as $v) {
                if ($v < $allow[0] || $allow[1] < $v) {
                    return null;
                }
            }
            return $values;
        };
        return static::registerRange($key, $default, $pattern, $filter);
    }

    /**
     * Add datetime range rule
     * 
     * @param string $key
     * @param string|null $default
     * @param string $format
     * @return Core\Rule
     */
    public static function datetimeRange($key, $default=null, $format='Ymd')
    {
        if (strpos($format, static::$delimiters['range']) !== false) {
            throw new \RuntimeException('A delimiter is included in a character string.');
        }
        //The character besides the alphanumeric is added.
        $chars   = static::$pattenrs['alphanum'] . $format;
        $pattern = static::patternRange($chars);
        $filter  = function ($values) use ($format) {
            $start = \DateTime::createFromFormat($format, $values[0]);
            $end   = \DateTime::createFromFormat($format, $values[1]);
            if (! $start || ! $end) {
                return null;
            }
            return array(
                $start,
                $end
            );
        };
        return static::registerRange($key, $default, $pattern, $filter);
    }
}
