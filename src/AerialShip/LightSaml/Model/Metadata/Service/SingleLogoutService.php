<?php

namespace AerialShip\LightSaml\Model\Metadata\Service;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Error\InvalidXmlException;


class SingleLogoutService extends AbstractService
{
    protected function getXmlNodeName() {
        return 'SingleLogoutService';
    }

    function getXml(\DOMNode $parent) {
        $result = parent::getXml($parent);
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        return parent::loadFromXml($xml);
    }


}