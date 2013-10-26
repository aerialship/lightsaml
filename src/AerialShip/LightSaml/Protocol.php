<?php

namespace AerialShip\LightSaml;


final class Protocol
{
    const SAML2 = 'urn:oasis:names:tc:SAML:2.0:protocol';

    const NS_METADATA = 'urn:oasis:names:tc:SAML:2.0:metadata';
    const NS_KEY_INFO = 'http://www.w3.org/2000/09/xmldsig#';

    protected function __construct() { }
}