<?php

/**
 * Regex type rule
 */

namespace Straw\Rule;

use Straw\Straw;

class Regex extends Rulable
{
    const TYPE_NORMAL = 1;
    const TYPE_LIST   = 2;
    const TYPE_PAIR   = 3;
    const TYPE_RANGE  = 4;

    protected static $formats = array(
        self::TYPE_NORMAL => '/\A:part\z/',
        self::TYPE_LIST   => '/\A(:part:delimiter(?!:delimiter))*:part\z/',
        self::TYPE_PAIR   => '/\A:part:delimiter:part\z/',
        self::TYPE_RANGE  => '/\A(:part){0,1}:delimiter(:part){0,1}\z/',
    );

    protected static $names = array(
        self::TYPE_LIST   => 'list',
        self::TYPE_PAIR   => 'pair',
        self::TYPE_RANGE  => 'range',
    );

    protected $piece;
    protected $pattern_format;
    protected $datetime_format;

    protected $length;
    protected $allow;

    protected $is_number   = false;
    protected $is_datetime = false;
    protected $is_original = false;

    /**
     * Make a repeat pattern array
     * 
     * @param mixed $length
     * @return array
     */
    protected static function makeRepeatPattern($length)
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
     * Make a part of the pattern
     * 
     * @param Regex $rule
     * @return string
     */
    protected static function makePart(self $rule)
    {
        if ($rule->is_original && $rule->types(self::TYPE_NORMAL)) {
            return $rule->piece;
        }
        $core = ($rule->is_number || $rule->is_original)
            ? $rule->piece
            : $rule->piece . Straw::getConfigure('sub_characters');
        $repeat = self::makeRepeatPattern($rule->length);
        return sprintf('[%s]{%s,%s}', $core, $repeat[0], $repeat[1]);
    }

    /**
     * Create
     * 
     * @param string|string[] $key
     * @param string|string[]|null $default
     * @param int $type
     * @return self
     */
    public static function create($key, $default, $type)
    {
        $rule = new self();
        $rule->key            = $key;
        $rule->default        = $default;
        $rule->type           = $type;
        $rule->pattern_format = self::$formats[$type];
    
        $rule->delimiter = ($rule->types(self::TYPE_NORMAL))
            ? null
            : Straw::getConfigure(self::$names[$type]);
        return $rule;
    }

    /**
     * Set alpha type
     * 
     * @param mixed $length
     * @return $this
     */
    public function alpha($length)
    {
        $this->piece  = Straw::getConfigure('alpha');
        $this->length = $length;
        return $this;
    }

    /**
     * Set alphanum type
     * 
     * @param mixed $length
     * @return $this
     */
    public function alnum($length)
    {
        $this->piece  = Straw::getConfigure('alnum');
        $this->length = $length;
        return $this;
    }

    /**
     * Set num type
     * 
     * @param array|null $allow
     * @return $this
     */
    public function number($allow)
    {
        $this->piece     = Straw::getConfigure('number');
        $this->allow     = $allow;
        $this->is_number = true;
        return $this;
    }

    /**
     * Set original type
     * 
     * @param string $piece
     * @return $this
     */
    public function original($piece)
    {
        $this->piece       = $piece;
        $this->is_original = true;
        return $this;
    }

    /**
     * Set datetime type
     * 
     * @param string $format
     * @return $this
     */
    public function datetime($format)
    {
        $this->piece           = Straw::getConfigure('alnum');
        $this->datetime_format = $format;
        $this->is_datetime     = true;
        return $this;
    }

    /**
     * Validate
     * 
     * @param string|string[] $value
     * @return mixed
     */
    protected function validate($value)
    {
        $pattern = $this->pattern();
        $input   = $this->implode($value);
        if (($input || $input === '0') && preg_match($pattern, $input)) {
            return $input;
        }

        $default = $this->implode($this->default);
        if (($default || $default === '0') && preg_match($pattern, $default)) {
            return $default;
        }
        return null;
    }

    /**
     * Make regex pattern
     * 
     * @return string
     */
    protected function pattern()
    {
        $search  = array(':part', ':delimiter');
        $replace = array(self::makePart($this), $this->delimiter);
        return str_replace($search, $replace, $this->pattern_format);
    }

    /**
     * Implode
     * 
     * @param string|string[] $value
     * @return string
     */
    protected function implode($value)
    {
        return (string)((is_array($value)) ? implode($this->delimiter, $value) : $value);
    }
}
