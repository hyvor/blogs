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

        $ret = "";
        if (empty($value)) {
            return $ret;
        }

        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $current = ord($value[$i]);
            if (($current == 0x9) ||
                ($current == 0xA) ||
                ($current == 0xD) ||
                (($current >= 0x20) && ($current <= 0xD7FF)) || // @phpstan-ignore-line
                (($current >= 0xE000) && ($current <= 0xFFFD)) || // @phpstan-ignore-line
                (($current >= 0x10000) && ($current <= 0x10FFFF))  // @phpstan-ignore-line
            ) {
                $ret .= chr($current);
            } else {
                $ret .= " ";
            }
        }
        return $ret;
    }
}
