<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Protocol;


class LogoutResponse extends StatusResponse
{

    /**
     * @return string
     */
    function getXmlNodeLocalName() {
        return 'LogoutResponse';
    }

    /**
     * @return string|null
     */
    function getXmlNodeNamespace() {
        return Protocol::SAML2;
    }
} 