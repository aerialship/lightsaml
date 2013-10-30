<?php

namespace AerialShip\LightSaml;

use AerialShip\LightSaml\Error\InvalidBindingException;

final class Binding
{
    const SAML2_HTTP_REDIRECT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    const SAML2_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
    const SAML2_HTTP_ARTIFACT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact';

    const SAML1_BROWSER_POST = 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post';
    const SAML1_ARTIFACT1 = 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01';

    private static $_binding2protocol = array(
        self::SAML2_HTTP_REDIRECT => Protocol::SAML2,
        self::SAML2_HTTP_POST => Protocol::SAML2,
        self::SAML2_HTTP_ARTIFACT => Protocol::SAML2,
        self::SAML1_BROWSER_POST => Protocol::SAML1,
        self::SAML1_ARTIFACT1 => Protocol::SAML1
    );


    private static $_constants = null;

    private static function getConstants() {
        if (self::$_constants === null) {
            $ref = new \ReflectionClass('\AerialShip\LightSaml\Binding');
            self::$_constants = $ref->getConstants();
        }
        return self::$_constants;
    }

    /**
     * @param string $binding
     * @return bool
     */
    static function isValid($binding) {
        $result = in_array($binding, self::getConstants());
        return $result;
    }


    static function validate($binding) {
        if (!self::isValid($binding)) {
            throw new InvalidBindingException($binding);
        }
    }


    /**
     * @param string $binding   one of \AerialShip\LightSaml\Binding
     * @return string  one of \AerialShip\LightSaml\Protocol::* constants
     */
    static function getBindingProtocol($binding) {
        $result = @self::$_binding2protocol[$binding];
        return $result;
    }


    private function __construct() { }
}