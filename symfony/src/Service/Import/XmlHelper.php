<?php

namespace App\Service\Import;

class XmlHelper
{
    /**
     * https://stackoverflow.com/questions/12229572/php-generated-xml-shows-invalid-char-value-27-message
     * https://stackoverflow.com/questions/3466035/how-to-skip-invalid-characters-in-xml-file-using-php
     */
    public static function validUtf8(string $value): string
    {
        $ret = '';
        if ($value === '') {
            return $ret;
        }

        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $current = ord($value[$i]);
            // ord() only ever returns 0-255, so this is a byte-level filter for
            // XML-invalid control characters (tab/LF/CR are the only ones kept below 0x20)
            if ($current === 0x9 || $current === 0xA || $current === 0xD || $current >= 0x20) {
                $ret .= chr($current);
            } else {
                $ret .= ' ';
            }
        }
        return $ret;
    }
}
