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
     * @return string
     */
    public function toXmlString() {
        $binding = htmlspecialchars($this->getBinding());
        $location = htmlspecialchars($this->getLocation());
        $index = $this->getIndex();
        return "<md:AssertionConsumerService Binding=\"{$binding}\" Location=\"{$location}\" index=\"{$index}\" />";
    }

    /**
     * @param \DOMElement $root
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[] unknown elements
     */
    public function loadXml(\DOMElement $root) {
        if (!$root->hasAttribute('Binding')) {
            throw new InvalidXmlException("Missing Binding attribute");
        }
        if (!$root->hasAttribute('Location')) {
            throw new InvalidXmlException("Missing Location attribute");
        }
        if (!$root->hasAttribute('index')) {
            throw new InvalidXmlException("Missing index attribute");
        }
        $this->setBinding($root->getAttribute('Binding'));
        $this->setLocation($root->getAttribute('Location'));
        $this->setIndex($root->getAttribute('index'));
        return array();
    }


}