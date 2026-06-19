<?php

namespace App\Shared\Helpers;

class StringHelper
{
    /**
     * Convert string to slug
     *
     * @param  string  $string
     * @param  string  $separator
     * @return string
     */
    public static function toSlug($string, $separator = '-')
    {
        $string = preg_replace('~[^\pL\d]+~u', $separator, $string);
        $string = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0100-\u7fff] remove', $string);
        $string = preg_replace('~[^\w-]+~', '', $string);
        $string = preg_replace('~-+~', $separator, $string);
        return trim($string, $separator);
    }

    /**
     * Truncate string
     *
     * @param  string  $string
     * @param  int  $length
     * @param  string  $ending
     * @return string
     */
    public static function truncate($string, $length = 100, $ending = '...')
    {
        if (strlen($string) <= $length) {
            return $string;
        }

        return substr($string, 0, $length - strlen($ending)) . $ending;
    }

    /**
     * Generate random string
     *
     * @param  int  $length
     * @return string
     */
    public static function random($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
