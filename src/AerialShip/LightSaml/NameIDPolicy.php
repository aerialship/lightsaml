<?php

namespace AerialShip\LightSaml;


final class NameIDPolicy
{
    const PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    const TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';

    private function __construct() { }
}