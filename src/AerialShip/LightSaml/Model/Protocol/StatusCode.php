<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Protocol;

class StatusCode implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;

    /** @var  string */
    protected $value;

    /** @var  StatusCode|null */
    protected $child;


    /**
     * @param string $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param \AerialShip\LightSaml\Model\Protocol\StatusCode|null $child
     */
    public function setChild($child) {
        $this->child = $child;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Protocol\StatusCode|null
     */
    public function getChild() {
        return $this->child;
    }



    protected function prepareForXml() {
        if (!$this->getValue()) {
            throw new InvalidXmlException('StatusCode value not set');
        }
    }


    /**
     * @param \DOMNode $parent
     * @param SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $this->prepareForXml();

        $result = $context->getDocument()->createElementNS(Protocol::SAML2, 'samlp:StatusCode');
        $result->setAttribute('Value', $this->getValue());

        if ($this->getChild()) {
            $this->getChild()->getXml($result, $context);
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return void
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'StatusCode' || $xml->namespaceURI != Protocol::SAML2) {
            throw new InvalidXmlException('Expected StatusCode element but got '.$xml->localName);
        }

        if (!$xml->hasAttribute('Value')) {
            throw new InvalidXmlException('Required attribute StatusCode Value missing');
        }
        $this->setValue($xml->getAttribute('Value'));

        $this->iterateChildrenElements($xml, function(\DOMElement $node) {
            if ($node->localName == 'StatusCode' && $node->namespaceURI == Protocol::SAML2) {
                $this->setChild(new StatusCode());
                $this->getChild()->loadFromXml($node);
            } else {
                throw new InvalidXmlException('Unknown element '.$node->localName);
            }
        });
    }


}