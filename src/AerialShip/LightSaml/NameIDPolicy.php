<?php

namespace AerialShip\LightSaml;


final class NameIDPolicy
{
    const NONE = null;
    const PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    const TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    const EMAIL = 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress';
    const SHIB_NAME_ID = 'urn:mace:shibboleth:1.0:nameIdentifier';


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