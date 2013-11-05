<?php

namespace AerialShip\LightSaml\Model\Metadata\Service;


class SingleSignOnService extends AbstractService
{

    protected function getXmlNodeName() {
        return 'SingleSignOnService';
    }

    /**
     * @param \DOMNode $parent
     * @return \DOMNode
     */
    function getXml(\DOMNode $parent) {
        $result = parent::getXml($parent);
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        parent::loadFromXml($xml);
    }


}