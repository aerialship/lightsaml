<?php

namespace AerialShip\LightSaml;


final class Helper
{
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