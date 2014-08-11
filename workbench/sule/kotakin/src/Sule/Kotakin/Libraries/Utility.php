<?php
namespace Sule\Kotakin\Libraries;

/*
 * This file is part of the Kotakin
 *
 * Author: Sulaeman <me@sulaeman.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Utility
{

    /* never allowed, string replacement */
    protected static $never_allowed_str = array(
        'document.cookie' => '[removed]',
        'document.write'  => '[removed]',
        '.parentNode'     => '[removed]',
        '.innerHTML'      => '[removed]',
        'window.location' => '[removed]',
        '-moz-binding'    => '[removed]',
        '<!--'            => '&lt;!--',
        '-->'             => '--&gt;',
        '<![CDATA['       => '&lt;![CDATA['
    );

    /* never allowed, regex replacement */
    protected static $never_allowed_regex = array(
        "javascript\s*:"           => '[removed]',
        "expression\s*(\(|&\#40;)" => '[removed]', // CSS and IE
        "vbscript\s*:"             => '[removed]', // IE, surprise!
        "Redirect\s+302"           => '[removed]'
    );

    protected $domain = 'kotakin';
    protected $locale;
    protected $localePath;

    public function setLocale($locale, $path)
    {
        if (function_exists('putenv')) {
            putenv('LC_ALL='.$locale);
        }
        
        setlocale(LC_ALL, $locale);

        $this->locale     = $locale;
        $this->localePath = $path;

        // path to the .MO file that we should monitor
        $file = $path.'/'.$locale.'/LC_MESSAGES/'.$this->domain.'.mo';

        if (file_exists($file)) {
            // check its modification time
            $mtime = filemtime($file);

            // our new unique .MO file
            $newFile = $path.'/'.$locale.'/LC_MESSAGES/'.$this->domain.'_'.$mtime.'.mo';

            // check if we have created it before
            if ( ! file_exists($newFile)) {
                // if not, create it now, by copying the original
                if (@copy($file, $newFile)) {
                    $this->domain = $this->domain.'_'.$mtime;
                }
            }
        }

        return $locale;
    }

    public function t($text)
    {
        if (is_null($this->localePath))
            return $text;

        bindtextdomain($this->domain, $this->localePath);
        textdomain($this->domain);

        return _($text);
    }

    /**
     * Character Limiter
     *
     * Limits the string based on the character count.  Preserves complete words
     * so the character count may not be exactly as specified.
     *
     * @access  public
     * @param   string
     * @param   integer
     * @param   string  the end character. Usually an ellipsis
     * @return  string
     */
    public function characterLimiter($str, $n = 500, $endChar = '&#8230;')
    {
        if (strlen($str) < $n)
            return $str;

        $str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

        if (strlen($str) <= $n)
            return $str;

        $out = "";
        foreach (explode(' ', trim($str)) as $val) {
            $out .= $val.' ';

            if (strlen($out) >= $n) {
                $out = trim($out);
                return (strlen($out) == strlen($str)) ? $out : $out.$endChar;
            }
        }
    }

    public static function getBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    public static function humanReadableFileSize($size)
    {
        $filesizename = array(' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
        return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }

    /**
     * Get the file dimension
     *
     * @access    public
     * @param file: string. file path
     * @return array
     */
    public static function getDimension($file)
    {
        if (empty($file)) {
            return array(
                'width' => 0,
                'height' => 0
            );
        }

        if (!function_exists('getimagesize')) {
            return array(
                'width' => 0,
                'height' => 0
            );
        }
        
        $dimension = @getimagesize($file);
        
        if($dimension === false)
            $dimension = array(0, 0);

        return array(
            'width' => $dimension[0],
            'height' => $dimension[1]
        );
    }

    /**
     * Sanitizes text, replacing whitespace with dashes.
     *
     * Limits the output to alphanumeric characters, underscore (_) and dash (-).
     * Whitespace becomes a dash.
     *
     * @param
     * string $text The title to be sanitized.
     * string $useUnderscore boolean if we don't want to use dashed
     * @return string The sanitized title.
     */
    public static function sanitizeText($text, $useUnderscore = false)
    {
        $rep_txt = '-';
        if ($useUnderscore)
            $rep_txt = '_';

        $text = strip_tags($text);
        // Preserve escaped octets.
        $text = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $text);
        // Remove percent signs that are not part of an octet.
        $text = str_replace('%', '', $text);
        // Restore octets.
        $text = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $text);

        $text = self::removeAccents($text);
        if (self::seemsUtf8($text)) {
            if (function_exists('mb_strtolower')) {
                $text = mb_strtolower($text, 'UTF-8');
            }
            $text = self::utf8UriEncode($text, 200);
        }

        $text = strtolower($text);
        $text = preg_replace('/&.+?;/', '', $text); // kill entities
        $text = str_replace('.', $rep_txt, $text);
        $text = preg_replace('/[^%a-z0-9 _-]/', '', $text);
        $text = preg_replace('/\s+/', $rep_txt, $text);
        $text = preg_replace('|-+|', $rep_txt, $text);
        $text = trim($text, $rep_txt);

        return $text;
    }

    /**
     * Encode the Unicode values to be used in the URI.
     *
     * @param string $utf8String
     * @param int $length Max length of the string
     * @return string String with Unicode encoded for URI.
     */
    public static function utf8UriEncode( $utf8String, $length = 0 )
    {
        $unicode = '';
        $values = array();
        $num_octets = 1;
        $unicodeLength = 0;

        $string_length = strlen( $utf8String );
        for ($i = 0; $i < $string_length; $i++ ) {
            $value = ord( $utf8String[ $i ] );

            if ( $value < 128 ) {
                if ( $length && ( $unicodeLength >= $length ) )
                    break;

                $unicode .= chr($value);
                $unicodeLength++;
            } else {
                if ( count( $values ) == 0 ) $num_octets = ( $value < 224 ) ? 2 : 3;

                $values[] = $value;

                if ( $length && ( $unicodeLength + ($num_octets * 3) ) > $length )
                    break;

                if ( count( $values ) == $num_octets ) {
                    if ($num_octets == 3) {
                        $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
                        $unicodeLength += 9;
                    } else {
                        $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
                        $unicodeLength += 6;
                    }

                    $values = array();
                    $num_octets = 1;
                }
            }
        }

        return $unicode;
    }

    /**
     * Checks to see if a string is utf8 encoded.
     *
     * NOTE: This function checks for 5-Byte sequences, UTF8
     *             has Bytes Sequences with a maximum length of 4.
     *
     * @author bmorel at ssi dot fr (modified)
     *
     * @param string $str The string to be checked
     * @return bool True if $str fits a UTF-8 model, false otherwise.
     */
    public static function seemsUtf8($str)
    {
        $length = strlen($str);
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; # 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
            else return false; # Does not match any model
            for ($j=0; $j<$n; $j++)
            { # n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    public static function removeAccents($string)
    {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        if (self::seemsUtf8($string)) {
            $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
            // Euro Sign
            chr(226).chr(130).chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194).chr(163) => '');

            $string = strtr($string, $chars);
        } else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
                .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
                .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
                .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
                .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
                .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
                .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
                .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
                .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
                .chr(252).chr(253).chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $doubleChars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $doubleChars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($doubleChars['in'], $doubleChars['out'], $string);
        }

        return $string;
    }

    /**
     * Generate a random string.
     *
     * @param
     * len: int number char to return
     * num: boolean, to include numeric
     * uc: boolean, to include uppercase char
     * lc: boolean, to include lowercase char
     * oc: boolean, to include others char => !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
     * @return string
     */
    public static function randString($len = 50, $num = true, $uc = true, $lc = true, $oc = false)
    {
        if (!$len || $len < 1 || $len > 100)
            $len = 100;

        $s = '';
        $i = 0;
        do {
            switch(mt_rand(1,4)) {
                // get number - ASCII characters (0:48 through 9:57)
                case 1:
                    if ($num) {
                        $s .= chr(mt_rand(48,57));
                        $i++;
                    }
                break;
                // get uppercase letter - ASCII characters (a:65 through z:90)
                case 2:
                    if ($uc) {
                        $s .= chr(mt_rand(65,90));
                        $i++;
                    }
                break;
                // get lowercase letter - ASCII characters (A:97 through Z:122)
                case 3:
                    if ($lc) {
                        $s .= chr(mt_rand(97,122));
                        $i++;
                    }
                    break;
                // get other characters - ASCII characters
                // !"#$%&'()*+,-./ :;<=>?@ [\]^_` {|}~
                // (33-47, 58-64, 91-96, 123-126)
                case 4:
                    if ($oc) {
                        switch(mt_rand(1,4)) {
                            case 1:
                                $s .= "&#" . mt_rand(33,47) . ";";
                                $i++;
                                break;
                            case 2:
                                $s .= "&#" . mt_rand(58,64) . ";";
                                $i++;
                                break;
                            case 3:
                                $s .= "&#" . mt_rand(91,96) . ";";
                                $i++;
                                break;
                            case 4:
                                $s .= "&#" . mt_rand(123,126) . ";";
                                $i++;
                                break;
                        }
                    }
                break;
            }
        } while ($i < $len);

        return $s;
    }

    /**
     * Appends a trailing slash.
     *
     * Will remove trailing slash if it exists already before adding a trailing
     * slash. This prevents double slashing a string or path.
     *
     * The primary use of this is for paths and thus should be used for paths. It is
     * not restricted to paths and offers no specific path support.
     *
     * @uses untrailingslashit() Unslashes string if it was slashed already.
     *
     * @param string $string What to add the trailing slash to.
     * @return string String with trailing slash added.
     */
    public static function trailingslashit($string)
    {
        return self::untrailingslashit($string) . '/';
    }

    /**
     * Removes trailing slash if it exists.
     *
     * The primary use of this is for paths and thus should be used for paths. It is
     * not restricted to paths and offers no specific path support.
     *
     * @param string $string What to remove the trailing slash from.
     * @return string String without the trailing slash.
     */
    public static function untrailingslashit($string)
    {
        return rtrim($string, '/');
    }

    public static function xssCleanArray($arr, $is_image = FALSE)
    {
        if (empty($arr))
            return $arr;

        foreach($arr as &$item)
            $item = self::xssClean($item, $is_image);

        return $arr;
    }

    public static function xssClean($str, $is_image = FALSE)
    {
        /*
        * Is the string an array?
        *
        */
        if (is_array($str)) {
            while (list($key) = each($str)) {
                $str[$key] = self::xssClean($str[$key]);
            }
        
            return $str;
        }
        
        /*
        * Remove Invisible Characters
        */
        $str = self::removeInvisibleCharacters($str);
        
        /*
        * Protect GET variables in URLs
        */
        
        // 901119URL5918AMP18930PROTECT8198
        
        $str = preg_replace('|\&([a-z\_0-9]+)\=([a-z\_0-9]+)|i', self::xssHash()."\\1=\\2", $str);
        
        /*
        * Validate standard character entities
        *
        * Add a semicolon if missing.    We do this to enable
        * the conversion of entities to ASCII later.
        *
        */
        $str = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);
        
        /*
        * Validate UTF16 two byte encoding (x00) 
        *
        * Just as above, adds a semicolon if missing.
        *
        */
        $str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);
        
        /*
        * Un-Protect GET variables in URLs
        */
        $str = str_replace(self::xssHash(), '&', $str);
        
        /*
        * URL Decode
        *
        * Just in case stuff like this is submitted:
        *
        * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
        *
        * Note: Use rawurldecode() so it does not remove plus signs
        *
        */
        $str = rawurldecode($str);
        
        /*
        * Convert character entities to ASCII 
        *
        * This permits our tests below to work reliably.
        * We only convert entities that are within tags since
        * these are the ones that will pose security problems.
        *
        */
        
        $str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", 'self::convertAttribute', $str);
        
        $str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", 'self::htmlEntityDecodeCallback', $str);
        
        /*
        * Remove Invisible Characters Again!
        */
        $str = self::removeInvisibleCharacters($str);
        
        /*
        * Convert all tabs to spaces
        *
        * This prevents strings like this: ja    vascript
        * NOTE: we deal with spaces between characters later.
        * NOTE: preg_replace was found to be amazingly slow here on large blocks of data,
        * so we use str_replace.
        *
        */
        
        if (strpos($str, "\t") !== FALSE) {
            $str = str_replace("\t", ' ', $str);
        }
        
        /*
        * Capture converted string for later comparison
        */
        $converted_string = $str;
        
        /*
        * Not Allowed Under Any Conditions
        */
        
        foreach (self::$never_allowed_str as $key => $val) {
            $str = str_replace($key, $val, $str);     
        }
        
        foreach (self::$never_allowed_regex as $key => $val) {
            $str = preg_replace("#".$key."#i", $val, $str);     
        }
        
        /*
        * Makes PHP tags safe
        *
        *    Note: XML tags are inadvertently replaced too:
        *
        *    <?xml
        *
        * But it doesn't seem to pose a problem.
        *
        */
        if ($is_image === true) {
            // Images have a tendency to have the PHP short opening and closing tags every so often
            // so we skip those and only do the long opening tags.
            $str = preg_replace('/<\?(php)/i', "&lt;?\\1", $str);
        } else {
            $str = str_replace(array('<?', '?'.'>'),    array('&lt;?', '?&gt;'), $str);
        }
        
        /*
        * Compact any exploded words
        *
        * This corrects words like:    j a v a s c r i p t
        * These words are compacted back to their correct state.
        *
        */
        $words = array('javascript', 'expression', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
        foreach ($words as $word) {
            $temp = '';
        
            for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++) {
                $temp .= substr($word, $i, 1)."\s*";
            }
        
            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $str = preg_replace_callback('#('.substr($temp, 0, -3).')(\W)#is', 'self::compactExplodedWords', $str);
        }
        
        /*
        * Remove disallowed Javascript in links or img tags
        * We used to do some version comparisons and use of stripos for PHP5, but it is dog slow compared
        * to these simplified non-capturing preg_match(), especially if the pattern exists in the string
        */
        do
        {
            $original = $str;
        
            if (preg_match("/<a/i", $str)) {
                $str = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", 'self::jsLinkRemoval', $str);
            }
        
            if (preg_match("/<img/i", $str)) {
                $str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", 'self::jsImgRemoval', $str);
            }
        
            if (preg_match("/script/i", $str) OR preg_match("/xss/i", $str)) {
                $str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
            }
        }
        while($original != $str);
        
        unset($original);
        
        /*
        * Remove JavaScript Event Handlers
        *
        * Note: This code is a little blunt.    It removes
        * the event handler and anything up to the closing >,
        * but it's unlikely to be a problem.
        *
        */
        $event_handlers = array('[^a-z_\-]on\w*','xmlns');
        
        if ($is_image === true) {
            /*
            * Adobe Photoshop puts XML metadata into JFIF images, including namespacing, 
            * so we have to allow this for images. -Paul
            */
            unset($event_handlers[array_search('xmlns', $event_handlers)]);
        }
        
        $str = preg_replace("#<([^><]+?)(".implode('|', $event_handlers).")(\s*=\s*[^><]*)([><]*)#i", "<\\1\\4", $str);
        
        /*
        * Sanitize naughty HTML elements
        *
        * If a tag containing any of the words in the list
        * below is found, the tag gets converted to entities.
        *
        * So this: <blink>
        * Becomes: &lt;blink&gt;
        *
        */
        $naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
        $str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', 'self::sanitizeNaughtyHtml', $str);
        
        /*
        * Sanitize naughty scripting elements
        *
        * Similar to above, only instead of looking for
        * tags it looks for PHP and JavaScript commands
        * that are disallowed.    Rather than removing the
        * code, it simply converts the parenthesis to entities
        * rendering the code un-executable.
        *
        * For example:    eval('some code')
        * Becomes:        eval&#40;'some code'&#41;
        *
        */
        $str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
        
        /*
        * Final clean up
        *
        * This adds a bit of extra precaution in case
        * something got through the above filters
        *
        */
        foreach (self::$never_allowed_str as $key => $val) {
            $str = str_replace($key, $val, $str);     
        }
        
        foreach (self::$never_allowed_regex as $key => $val) {
            $str = preg_replace("#".$key."#i", $val, $str);
        }
        
        /*
        *    Images are Handled in a Special Way
        *    - Essentially, we want to know that after all of the character conversion is done whether
        *    any unwanted, likely XSS, code was found.    If not, we return TRUE, as the image is clean.
        *    However, if the string post-conversion does not matched the string post-removal of XSS,
        *    then it fails, as there was unwanted XSS code found and removed/changed during processing.
        */
        
        if ($is_image === true) {
            if ($str == $converted_string) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        
        return $str;
    }

    /**
    * Random Hash for protecting URLs
    *
    * @access    public
    * @return    string
    */
    public static function xssHash()
    {
        if (phpversion() >= 4.2)
            mt_srand();
        else
            mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);

        return (md5(time() + mt_rand(0, 1999999999)));
    }

    /**
    * Remove Invisible Characters
    *
    * This prevents sandwiching null characters
    * between ascii characters, like Java\0script.
    *
    * @access    public
    * @param    string
    * @return    string
    */
    public static function removeInvisibleCharacters($str)
    {
        // every control character except newline (dec 10), carriage return (dec 13), and horizontal tab (dec 09),
        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',                // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/', '/\x0c/',            // 11, 12
            '/[\x0e-\x1f]/'                // 14-31
        );

        do {
            $cleaned = $str;
            $str = preg_replace($non_displayables, '', $str);
        } while ($cleaned != $str);

        return $str;
    }

    /**
    * Attribute Conversion
    *
    * Used as a callback for XSS Clean
    *
    * @access    public
    * @param    array
    * @return    string
    */
    public static function convertAttribute($match)
    {
        return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
    }

    /**
    * HTML Entity Decode Callback
    *
    * Used as a callback for XSS Clean
    *
    * @access    public
    * @param    array
    * @return    string
    */
    public static function htmlEntityDecodeCallback($match)
    {
        return self::htmlEntityDecode($match[0], 'UTF-8');
    }

    /**
    * Compact Exploded Words
    *
    * Callback function for xss_clean() to remove whitespace from
    * things like j a v a s c r i p t
    *
    * @access    public
    * @param    type
    * @return    type
    */
    public static function compactExplodedWords($matches)
    {
        return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
    }

    /**
    * JS Link Removal
    *
    * Callback function for xss_clean() to sanitize links
    * This limits the PCRE backtracks, making it more performance friendly
    * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
    * PHP 5.2+ on link-heavy strings
    *
    * @access    private
    * @param    array
    * @return    string
    */
    public static function jsLinkRemoval($match)
    {
        $attributes = self::filterAttributes(str_replace(array('<', '>'), '', $match[1]));
        return str_replace($match[1], preg_replace("#href=.*?(alert\(|alert&\#40;|javascript\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
    }

    /**
    * JS Image Removal
    *
    * Callback function for xss_clean() to sanitize image tags
    * This limits the PCRE backtracks, making it more performance friendly
    * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
    * PHP 5.2+ on image tag heavy strings
    *
    * @access    private
    * @param    array
    * @return    string
    */
    public static function jsImgRemoval($match)
    {
        $attributes = self::filterAttributes(str_replace(array('<', '>'), '', $match[1]));
        return str_replace($match[1], preg_replace("#src=.*?(alert\(|alert&\#40;|javascript\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
    }

    /**
    * Sanitize Naughty HTML
    *
    * Callback function for xss_clean() to remove naughty HTML elements
    *
    * @access    private
    * @param    array
    * @return    string
    */
    public static function sanitizeNaughtyHtml($matches)
    {
        // encode opening brace
        $str = '&lt;'.$matches[1].$matches[2].$matches[3];

        // encode captured opening or closing brace to prevent recursive vectors
        $str .= str_replace(array('>', '<'), array('&gt;', '&lt;'), $matches[4]);

        return $str;
    }

    /**
    * HTML Entities Decode
    *
    * This function is a replacement for htmlEntityDecode()
    *
    * In some versions of PHP the native function does not work
    * when UTF-8 is the specified character set, so this gives us
    * a work-around.    More info here:
    * http://bugs.php.net/bug.php?id=25670
    *
    * @access    private
    * @param    string
    * @param    string
    * @return    string
    */
    /* -------------------------------------------------
    /*    Replacement for htmlEntityDecode()
    /* -------------------------------------------------*/

    /*
    NOTE: htmlEntityDecode() has a bug in some PHP versions when UTF-8 is the
    character set, and the PHP developers said they were not back porting the
    fix to versions other than PHP 5.x.
    */
    public static function htmlEntityDecode($str, $charset='UTF-8')
    {
        if (stristr($str, '&') === FALSE) return $str;

        // The reason we are not using htmlEntityDecode() by itself is because
        // while it is not technically correct to leave out the semicolon
        // at the end of an entity most browsers will still interpret the entity
        // correctly.    htmlEntityDecode() does not convert entities without
        // semicolons, so we are left with our own little solution here. Bummer.

        if (function_exists('htmlEntityDecode') && (strtolower($charset) != 'utf-8' OR version_compare(phpversion(), '5.0.0', '>='))) {
            $str = html_entity_decode($str, ENT_COMPAT, $charset);
            $str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
            return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
        }

        // Numeric Entities
        $str = preg_replace('~&#x(0*[0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $str);
        $str = preg_replace('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $str);

        // Literal Entities - Slightly slow so we do another check
        if (stristr($str, '&') === FALSE) {
            $str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
        }

        return $str;
    }

    /**
    * Filter Attributes
    *
    * Filters tag attributes for consistency and safety
    *
    * @access    public
    * @param    string
    * @return    string
    */
    public static function filterAttributes($str)
    {
        $out = '';

        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= preg_replace("#/\*.*?\*/#s", '', $match);
            }
        }

        return $out;
    }
    
    /**
     * Retrieve the current time based on specified type.
     *
     * The 'mysql' type will return the time in the format for MySQL DATETIME field.
     * The 'timestamp' type will return the current timestamp.
     *
     * If $gmt is set to either '1' or 'true', then both types will use GMT time.
     * if $gmt is false, the output is adjusted with the GMT offset in the option.
     *
     * @param string $type Either 'mysql' or 'timestamp'.
     * @param int|bool $gmt Optional. Whether to use GMT timezone. Default is false.
     * @return int|string String if $type is 'gmt', int if $type is 'timestamp'.
     */
    public static function currentTime( $type, $gmt = false )
    {
        switch ( $type ) {
            case 'mysql':
                return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ((int) date('Z')) ) );
                break;
            case 'timestamp':
                return ( $gmt ) ? time() : time() + ((int) date('Z'));
                break;
        }
    }

}