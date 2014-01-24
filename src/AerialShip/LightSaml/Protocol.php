<?php

namespace AerialShip\LightSaml;


final class Protocol
{
    const VERSION_2_0 = '2.0';
    const SAML2 = 'urn:oasis:names:tc:SAML:2.0:protocol';
    const SAML1 = 'urn:oasis:names:tc:SAML:1.0:protocol';
    const SAML11 = 'urn:oasis:names:tc:SAML:1.1:protocol';
    const SHIB1 = 'urn:mace:shibboleth:1.0';

    const NS_METADATA = 'urn:oasis:names:tc:SAML:2.0:metadata';
    const NS_XMLDSIG = 'http://www.w3.org/2000/09/xmldsig#';
    const NS_ASSERTION = 'urn:oasis:names:tc:SAML:2.0:assertion';


    const AC_PASSWORD = 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password';
    const AC_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:ac:classes:unspecified';
    const AC_WINDOWS = 'urn:federation:authentication:windows';

    const XMLSEC_TRANSFORM_ALGORITHM_ENVELOPED_SIGNATURE = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';


    const STATUS_SUCCESS = 'urn:oasis:names:tc:SAML:2.0:status:Success';
    const STATUS_REQUESTER = 'urn:oasis:names:tc:SAML:2.0:status:Requester';
    const STATUS_RESPONDER = 'urn:oasis:names:tc:SAML:2.0:status:Responder';
    const STATUS_VERSION_MISMATCH = 'urn:oasis:names:tc:SAML:2.0:status:VersionMismatch';
    const STATUS_NO_PASSIVE = 'urn:oasis:names:tc:SAML:2.0:status:NoPassive';
    const STATUS_PARTIAL_LOGOUT = 'urn:oasis:names:tc:SAML:2.0:status:PartialLogout';
    const STATUS_PROXY_COUNT_EXCEEDED = 'urn:oasis:names:tc:SAML:2.0:status:ProxyCountExceeded';
    const STATUS_INVALID_NAME_ID_POLICY = 'urn:oasis:names:tc:SAML:2.0:status:InvalidNameIDPolicy';


    const CM_BEARER = 'urn:oasis:names:tc:SAML:2.0:cm:bearer';
    const CM_HOK = 'urn:oasis:names:tc:SAML:2.0:cm:holder-of-key';


    const ENCODING_DEFLATE = 'urn:oasis:names:tc:SAML:2.0:bindings:URL-Encoding:DEFLATE';


    protected function __construct() { }
}