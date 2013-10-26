<?php

namespace AerialShip\LightSaml\Model\Service;

use AerialShip\LightSaml\Binding;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Protocol;


class AssertionConsumerService extends AbstractService
{
    /** @var int */
    protected $index;


    function __construct($binding = null, $location = null, $index = null) {
        parent::__construct($binding, $location);
        if ($index !== null) {
            $this->setIndex($index);
        }
    }


    /**
     * @param int $index
     * @throws \InvalidArgumentException
     */
    public function setIndex($index) {
        $v = intval($index);
        if ($v != $index) {
            throw new \InvalidArgumentException("Expected int got $index");
        }
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function getIndex() {
        return $this->index;
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $result = $parent->ownerDocument->createElementNS(Protocol::NS_METADATA, 'md:AssertionConsumerService');
        $parent->appendChild($result);
        $result->setAttribute('Binding', $this->getBinding());
        $result->setAttribute('Location', $this->getLocation());
        $result->setAttribute('index', $this->getIndex());
        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'AssertionConsumerService' || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException('Expected AssertionConsumerService element and '.Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }
        parent::loadFromXml($xml);
        if (!$xml->hasAttribute('index')) {
            throw new InvalidXmlException("Missing index attribute");
        }
        $this->setIndex($xml->getAttribute('index'));
        return array();
    }





}