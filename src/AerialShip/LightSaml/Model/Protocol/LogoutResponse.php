<?php
/**
 * @desc
 * @author Ostojić Aleksandar <ao@boutsourcing.com> 11/7/13
 */

namespace AerialShip\LightSaml\Model\Protocol;


use AerialShip\LightSaml\Protocol;

class LogoutResponse extends Response{

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