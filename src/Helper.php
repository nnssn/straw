<?php

/**
 * Helper class
 */

namespace Straw;

class Helper
{
    /**
     * Parse url
     * 
     * @param string $url
     * @param string $guide
     * @param bool $with_get
     * @return array
     */
    public static function parseUrl($url, $guide, $with_get = true)
    {
		$query  = preg_match('#\?(.*)\z#', $url, $query) ? $query[1] : null;
        $values = preg_split('#(?<![/|:])/#', preg_replace('#\?.*$#', '', $url));
        $names  = explode('/', $guide);
        $result = array();

        foreach ($names as $key => $name) {
            if ($name && $name[0] !== '_') {
                $result[$name] = $values[$key];
            }
        }
		return ($with_get && $query) ? $result + parse_str($query) : $result;
    }
}
