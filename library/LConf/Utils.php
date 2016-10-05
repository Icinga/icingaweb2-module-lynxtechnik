<?php

/**
 * LConf_Utils class
 *
 * @package LConf
 */
/**
 * This class provides useful LConf/LDAP-related functions
 *
 * @author  Thomas Gelf <thomas@gelf.net>
 * @package LConf
 */
class LConf_Utils
{
    /**
     * Extends PHPs ldap_explode_dn() function
     *
     * UTF-8 chars like German umlauts would otherwise be escaped and shown
     * as backslash-prefixed hexcode-sequenzes.
     *
     * @param  string  DN
     * @param  boolean Returns 'type=value' when true and 'value' when false
     * @return string
     */
    public static function explodeDN($dn, $with_type = true)
    {
        $res = ldap_explode_dn($dn, $with_type ? 0 : 1);
        foreach ($res as $k => $v) {
            $res[$k] = preg_replace(
                '/\\\([0-9a-f]{2})/ei',
                "chr(hexdec('\\1'))",
                $v
            );
/*
                $result[$key] = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", $value); 
*/
        }
        unset($res['count']);
        return $res;
    }

    public static function implodeDN($parts)
    {
        $str = '';
        foreach ($parts as $part) {
            if ($str !== '') { $str .= ','; }
            list($key, $val) = preg_split('~=~', $part, 2);
            $str .= $key . '=' . self::quoteForDN($val);
        }
        return $str;
    }

    /**
     * Quote a string that should be used in a DN
     *
     * Special characters will be escaped
     *
     * @param  string DN-component
     * @return string
     */
    public static function quoteForDN($str)
    {
        return self::quoteChars($str, array(
            ',', '=', '+', '<', '>', ';', '\\', '"', '#'
        ));
    }

    /**
     * Quote a string that should be used in an LDAP search
     *
     * Special characters will be escaped
     *
     * @param  string String to be escaped
     * @return string
     */
    public static function quoteForSearch($str)
    {
        return self::quoteChars($str, array('*', '(', ')', '\\', chr(0)));
    }

    /**
     * Escape given characters in the given string
     *
     * Special characters will be escaped
     *
     * @param  string String to be escaped
     * @return string
     */
    protected static function quoteChars($str, $chars)
    {
        $quotedChars = array();
        foreach ($chars as $k => $v) {
            $quotedChars[$k] = ',' . str_pad(dechex(ord($v)), 2, '0');
        }
        $str = str_replace($chars, $quotedChars, $str);
        // Workaround, str_replace behaves pretty strange with leading backslash
        $str = preg_replace('~,~', '\\', $str);
        return $str;
    }
}
