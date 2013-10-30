<?php

namespace AerialShip\LightSaml;


final class Protocol
{
    const VERSION_2_0 = '2.0';
    const SAML2 = 'urn:oasis:names:tc:SAML:2.0:protocol';
    const SAML1 = 'urn:oasis:names:tc:SAML:1.0:protocol'; // TODO check this

    const NS_METADATA = 'urn:oasis:names:tc:SAML:2.0:metadata';
    const NS_XMLDSIG = 'http://www.w3.org/2000/09/xmldsig#';
    const NS_ASSERTION = 'urn:oasis:names:tc:SAML:2.0:assertion';

    const XMLSEC_TRANSFORM_ALGORITHM_ENVELOPED_SIGNATURE = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';

    protected function __construct() { }
}