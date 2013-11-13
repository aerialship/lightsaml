<?php

namespace AerialShip\LightSaml;


final class Helper
{

    /**
     * @param string $time
     * @return int
     * @throws \InvalidArgumentException
     */
    static function parseSAMLTime($time) {
        $matches = array();
        if(preg_match('/^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)T(\\d\\d):(\\d\\d):(\\d\\d)(?:\\.\\d+)?Z$/D',
            $time, $matches) == 0) {
            throw new \InvalidArgumentException('Invalid SAML2 timestamp: ' . $time);
        }

        $year = intval($matches[1]);
        $month = intval($matches[2]);
        $day = intval($matches[3]);
        $hour = intval($matches[4]);
        $minute = intval($matches[5]);
        $second = intval($matches[6]);

        // Use gmmktime because the timestamp will always be given in UTC.
        $ts = gmmktime($hour, $minute, $second, $month, $day, $year);
        return $ts;
    }


    static function getClassNameOnly($value) {
        if (is_object($value)) {
            $value = get_class($value);
        } else if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string or object');
        }
        if (($pos = strrpos($value, '\\')) !== false) {
            $value = substr($value, $pos+1);
        }
        return $value;
    }

    static function doClassNameMatch($object, $class) {
        if (!is_string($class)) {
            throw new \InvalidArgumentException('class argument must be string');
        }
        $result = false;
        $class = ltrim($class, '\\');
        $itemClass = get_class($object);
        if ($itemClass == $class) {
            $result = true;
        } else {
            $itemClass = self::getClassNameOnly($itemClass);
            if ($itemClass == $class) {
                $result = true;
            }
        }
        return $result;
    }


    /**
     * @param int $length
     * @return string
     * @throws \InvalidArgumentException
     */
    static function generateRandomBytes($length) {
        $length = intval($length);
        if (!$length) {
            throw new \InvalidArgumentException();
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        }

        $data = '';
        for($i = 0; $i < $length; $i++) {
            $data .= chr(mt_rand(0, 255));
        }
        return $data;
    }

    /**
     * @param string $bytes
     * @return string
     */
    static function stringToHex($bytes) {
        $result = '';
        $len = strlen($bytes);
        for($i = 0; $i < $len; $i++) {
            $result .= sprintf('%02x', ord($bytes[$i]));
        }
        return $result;
    }


    /**
     * @return string
     */
    static function generateID() {
        return '_'.self::stringToHex(self::generateRandomBytes(21));
    }

}