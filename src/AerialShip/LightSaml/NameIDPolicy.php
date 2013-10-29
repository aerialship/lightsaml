<?php

namespace AerialShip\LightSaml;


final class NameIDPolicy
{
    const PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    const TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';



    private static $_constants = null;

    private static function getConstants() {
        if (self::$_constants === null) {
            $ref = new \ReflectionClass('\AerialShip\LightSaml\NameIDPolicy');
            self::$_constants = $ref->getConstants();
        }
        return self::$_constants;
    }


    static function isValid($value) {
        $result = in_array($value, self::getConstants());
        return $result;
    }


    private function __construct() { }
}