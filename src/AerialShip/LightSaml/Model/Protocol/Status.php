<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Protocol;


class Status implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;


    /** @var string */
    protected $code;

    /** @var string */
    protected $message;

    /**
     * @param string $code
     */
    public function setCode($code) {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * @param string $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }



    public function isSuccess() {
        return $this->getCode() == Protocol::STATUS_SUCCESS;
    }


    public function setSuccess() {
        $this->setCode(Protocol::STATUS_SUCCESS);
    }



    protected function prepareForXml() {
        if (!$this->getCode()) {
            throw new InvalidXmlException('StatusCode not set');
        }
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $this->prepareForXml();

        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElementNS(Protocol::SAML2, 'samlp:Status');
        $parent->appendChild($result);

        $statusCodeNode = $doc->createElementNS(Protocol::SAML2, 'samlp:StatusCode');
        $result->appendChild($statusCodeNode);
        $statusCodeNode->setAttribute('Value', $this->getCode());

        if ($this->getMessage()) {
            $statusMessageNode = $doc->createElementNS(Protocol::SAML2, 'samlp:StatusMessage', $this->getMessage());
            $result->appendChild($statusMessageNode);
        }

        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'Status' || $xml->namespaceURI != Protocol::SAML2) {
            throw new InvalidXmlException('Expected Status element but got '.$xml->localName);
        }

        $result = array();
        $this->iterateChildrenElements($xml, function(\DOMElement $node) use (&$result) {
            if ($node->localName == 'StatusCode' && $node->namespaceURI == Protocol::SAML2) {
                if (!$node->hasAttribute('Value')) {
                    throw new InvalidXmlException('StatusCode element missing Value attribute');
                }
                $this->setCode($node->getAttribute('Value'));
            } else if ($node->localName == 'StatusMessage' && $node->namespaceURI == Protocol::SAML2) {
                $this->setMessage($node->textContent);
            } else {
                $result[] = $node;
            }
        });

        if (!$this->getCode()) {
            throw new InvalidXmlException('Missing Status node');
        }

        return $result;
    }

}