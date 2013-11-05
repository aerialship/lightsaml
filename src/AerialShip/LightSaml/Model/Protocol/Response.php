<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidResponseException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\Model\Assertion\Assertion;
use AerialShip\LightSaml\Protocol;


class Response extends StatusResponse
{
    /** @var Assertion[] */
    protected $assertions = array();



    /**
     * @return string
     */
    function getXmlNodeLocalName() {
        return 'Response';
    }

    /**
     * @return string|null
     */
    function getXmlNodeNamespace() {
        return Protocol::SAML2;
    }



    /**
     * @return \AerialShip\LightSaml\Model\Assertion\Assertion[]
     */
    public function getAllAssertions() {
        return $this->assertions;
    }

    public function addAssertion(Assertion $assertion) {
        $this->assertions[] = $assertion;
    }




    protected function prepareForXml() {
        parent::prepareForXml();
        if (!$this->getAllAssertions()) {
            throw new InvalidResponseException('Missing Assertions');
        }
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $result = parent::getXml($parent);
        foreach ($this->getAllAssertions() as $assertion) {
            $assertion->getXml($result);
        }
        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        parent::loadFromXml($xml);
        $this->iterateChildrenElements($xml, function(\DOMElement $node) {
            if ($node->localName == 'Assertion' && $node->namespaceURI == Protocol::NS_ASSERTION) {
                $assertion = new Assertion();
                $assertion->loadFromXml($node);
                $this->addAssertion($assertion);
            }
        });
        if (!$this->getAllAssertions()) {
            throw new InvalidXmlException('Missing Assertion element');
        }
    }


}