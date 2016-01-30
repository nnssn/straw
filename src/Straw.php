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
        'pair'  => ':',
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
        self::$pattenrs   = $options + self::$pattenrs;
        self::$delimiters = $options + self::$delimiters;
        if (isset($options['allow_alpha_subs'])) {
            self::$allow_alpha_subs = $options['allow_alpha_subs'];
        }
    }

    /**
     * Create Core\Maker instance
     * 
     * @return Core\Maker
     */
    public static function open(Manual $manual = null)
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
        return self::$rules;
    }

    /**
     * Normalize length array
     * 
     * @param mixed $length
     * @return array
     */
    private static function repeat($length)
    {
        if (is_numeric($length)) {
            return array($length, $length);
        }
        if (! is_array($length)) {
            return array(1, '');
        }
        return array(
            (! empty($length[0])) ? $length[0] : 1,
            (! empty($length[1])) ? $length[1] : '',
        );
    }

    /**
     * Make normal pattern
     * 
     * @param string $chars
     * @param mixed $length
     * @return string
     */
    private static function pattern($chars, $length = null)
    {
        $main   = ($chars === self::$pattenrs['num']) ? $chars : $chars . self::$allow_alpha_subs;
        $repeat = self::repeat($length);
        return sprintf('/\A[%s]{%s,%s}\Z/', $main, $repeat[0], $repeat[1]);
    }

    /**
     * Make multi pattern
     * 
     * @param string $chars
     * @param string $delimiter
     * @param string $format
     * @param mixed $length
     * @return string
     */
    private static function patternMulti($chars, $delimiter, $format, $length = null)
    {
        $main    = ($chars === self::$pattenrs['num']) ? $chars : $chars . self::$allow_alpha_subs;
        $repeat  = self::repeat($length);
        $search  = array(':main', ':delimiter', ':min', ':max');
        $replace = array($main, $delimiter, $repeat[0], $repeat[1]);
        return str_replace($search, $replace, $format);
    }

    /**
     * Make list pattern
     * 
     * @param string $chars
     * @param string $delimiter
     * @param mixed $length
     * @return string
     */
    private static function patternList($chars, $delimiter, $length = null)
    {
        $format = '/\A([:main]{:min,:max}:delimiter(?!:delimiter))*[:main]{:min,:max}\z/';
        return self::patternMulti($chars, $delimiter, $format, $length);
    }

    /**
     * Make Pair pattern
     * 
     * @param string $chars
     * @param string $delimiter
     * @param mixed $length
     * @return string
     */
    private static function patternPair($chars, $delimiter, $length = null)
    {
        $format = '/\A[:main]{:min,:max}:delimiter[:main]{:min,:max}\z/';
        return self::patternMulti($chars, $delimiter, $format, $length);
    }

    /**
     * Make range pattern
     * 
     * @param string $chars
     * @return string
     */
    private static function patternRange($chars)
    {
        $format = '/\A([:main]+){0,1}:delimiter([:main]+){0,1}\z/';
        return self::patternMulti($chars, self::$delimiters['range'], $format);
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
    private static function register($key, $default, $pattern, callable $filter = null)
    {
        self::$rules[$key] = new Core\Rule($key, $default, $pattern, $filter);
        return self::$rules[$key];
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
    private static function registerList($key, $default, $pattern, $delimiter = null, $allow = null)
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
            if ($delimiter === self::$delimiters['set'] && $values !== array_unique($values)) {
                return null;
            }
            return $values;
        };
        return self::register($key, $default, $pattern, $filter);
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
    private static function registerRange($key, $default, $pattern, callable $filter = null)
    {
        $range_filter = function ($input) use ($default) {
            if ($input === self::$delimiters['range']) {
                return null;
            }
            $values = explode(self::$delimiters['range'], $input);
            $fills  = array_filter(explode(self::$delimiters['range'], $default), 'strlen');
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
        return self::register($key, $default, $pattern, $filters);
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
    public static function newRule($key, $pattern, $default = null)
    {
        return self::register($key, $default, $pattern);
    }

    /**
     * Add boolean rule
     * 
     * @param string $key
     * @param string|null $default
     * @return Core\Rule
     */
    public static function bool($key, $default = null)
    {
        $filter = function ($input) {
            return (int)$input;
        };
        return self::register($key, $default, '/\A(0|1)\z/', $filter);
    }

    /**
     * Add alpha rule
     * 
     * @param string $key
     * @param string $default
     * @param mixed $length
     * @return Core\Rule
     */
    public static function alpha($key, $default = null, $length = null)
    {
        $pattern = self::pattern(self::$pattenrs['alpha'], $length);
        return self::register($key, $default, $pattern);
    }

    /**
     * Add numeric rule
     * 
     * @param string $key
     * @param string $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function num($key, $default = null, array $allow = array())
    {
        $pattern = self::pattern(self::$pattenrs['num']);
        $filter  = function ($input) use ($allow) {
            $value = (int)$input;
            if (! $allow) {
                return $value;
            }
            return ($value < $allow[0] || $allow[1] < $value) ? null : $value;
        };
        return self::register($key, $default, $pattern, $filter);
    }

    /**
     * Add alphanumeric rule
     * 
     * @param string $key
     * @param string $default
     * @param mixed $length
     * @return Core\Rule
     */
    public static function alphanum($key, $default = null, $length = null)
    {
        $pattern = self::pattern(self::$pattenrs['alphanum'], $length);
        return self::register($key, $default, $pattern);
    }

    /**
     * Add alpha list rule
     * 
     * @param string $key
     * @param string $default
     * @param mixed $length
     * @return Core\Rule
     */
    public static function alphaList($key, $default = null, $length = null)
    {
        $pattern = self::patternList(self::$pattenrs['alpha'], self::$delimiters['list'], $length);
        return self::registerList($key, $default, $pattern, self::$delimiters['list']);
    }

    /**
     * Add numeric list rule
     * 
     * @param string $key
     * @param string $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function numList($key, $default = null, array $allow = array())
    {
        $pattern = self::patternList(self::$pattenrs['num'], self::$delimiters['list']);
        return self::registerList($key, $default, $pattern, self::$delimiters['list'], $allow);
    }

    /**
     * Add alphanumeric list rule
     * 
     * @param string $key
     * @param string $default
     * @param mixed $length
     * @return Core\Rule
     */
    public static function alphanumList($key, $default = null, $length = null)
    {
        $pattern = self::patternList(self::$pattenrs['alphanum'], self::$delimiters['list'], $length);
        return self::registerList($key, $default, $pattern, self::$delimiters['list']);
    }

    /**
     * Add alpha set rule
     * 
     * @param string $key
     * @param string $default
     * @param mixed $length
     * @return Core\Rule
     */
    public static function alphaSet($key, $default = null, $length = null)
    {
        $pattern = self::patternList(self::$pattenrs['alpha'], self::$delimiters['set'], $length);
        return self::registerList($key, $default, $pattern, self::$delimiters['set']);
    }

    /**
     * Add numeric set rule
     * 
     * @param string $key
     * @param string $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function numSet($key, $default = null, array $allow = array())
    {
        $pattern = self::patternList(self::$pattenrs['num'], self::$delimiters['set']);
        return self::registerList($key, $default, $pattern, self::$delimiters['set'], $allow);
    }

    /**
     * Add alphanumeric set rule
     * 
     * @param string $key
     * @param string $default
     * @param mixed $length
     * @return Core\Rule
     */
    public static function alphanumSet($key, $default = null, $length = null)
    {
        $pattern = self::patternList(self::$pattenrs['alphanum'], self::$delimiters['set'], $length);
        return self::registerList($key, $default, $pattern, self::$delimiters['set']);
    }

    /**
     * Add alpha pair rule
     * 
     * @param string $key
     * @param string $default
     * @param mixed $length
     * @return Core\Rule
     */
    public static function alphaPair($key, $default = null, $length = null)
    {
        $pattern = self::patternPair(self::$pattenrs['alpha'], self::$delimiters['pair'], $length);
        return self::registerList($key, $default, $pattern, self::$delimiters['pair']);
    }

    /**
     * Add numeric pair rule
     * 
     * @param string $key
     * @param string $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function numPair($key, $default = null, array $allow = array())
    {
        $pattern = self::patternPair(self::$pattenrs['num'], self::$delimiters['pair']);
        return self::registerList($key, $default, $pattern, self::$delimiters['pair'], $allow);
    }

    /**
     * Add alphanumeric pair rule
     * 
     * @param string $key
     * @param string $default
     * @param mixed $length
     * @return Core\Rule
     */
    public static function alphanumPair($key, $default = null, $length = null)
    {
        $pattern = self::patternPair(self::$pattenrs['alphanum'], self::$delimiters['pair'], $length);
        return self::registerList($key, $default, $pattern, self::$delimiters['pair']);
    }

    /**
     * Add num range rule
     * 
     * @param string $key
     * @param string|null $default
     * @param array $allow
     * @return Core\Rule
     */
    public static function numRange($key, $default = null, array $allow = array())
    {
        $pattern = self::patternRange(self::$pattenrs['num']);
        $filter = function ($input) use ($allow) {
            $range = array_map(function ($v) {return (int)$v;}, $input);
            if (! $allow) {
                return $range;
            }
            return ($range[0] < $allow[0] || $allow[1] < $range[0]
                    || $range[1] < $allow[0] || $allow[1] < $range[1]) ? null : $range;
        };
        return self::registerRange($key, $default, $pattern, $filter);
    }

    /**
     * Add datetime range rule
     * 
     * @param string $key
     * @param string|null $default
     * @param string $format
     * @return Core\Rule
     */
    public static function datetimeRange($key, $default = null, $format = 'Ymd')
    {
        if (strpos($format, self::$delimiters['range']) !== false) {
            throw new \RuntimeException('A delimiter is included in a character string.');
        }
        //The character besides the alphanumeric is added.
        $chars   = self::$pattenrs['alphanum'] . $format;
        $pattern = self::patternRange($chars);
        $filter  = function ($input) use ($format) {
            $start = \DateTime::createFromFormat($format, $input[0]);
            $end   = \DateTime::createFromFormat($format, $input[1]);
            return ($start && $end) ? array($start, $end) : null;
        };
        return self::registerRange($key, $default, $pattern, $filter);
    }
}
