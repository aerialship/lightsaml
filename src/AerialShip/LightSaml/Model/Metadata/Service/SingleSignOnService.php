<?php

namespace AerialShip\LightSaml\Model\Metadata\Service;

use AerialShip\LightSaml\Meta\SerializationContext;


class SingleSignOnService extends AbstractService
{

    protected function getXmlNodeName() {
        return 'SingleSignOnService';
    }

    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMNode
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $result = parent::getXml($parent, $context);
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