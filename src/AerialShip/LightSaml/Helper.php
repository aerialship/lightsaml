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
}