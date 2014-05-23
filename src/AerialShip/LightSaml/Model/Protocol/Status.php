<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Protocol;


class Status implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;

    /** @var  StatusCode */
    protected $statusCode;

    /** @var string */
    protected $message;


    /**
     * @param StatusCode|null $statusCode
     * @param string $message
     */
    public function __construct(StatusCode $statusCode = null, $message = null)
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
    }

    /**
     * @param \AerialShip\LightSaml\Model\Protocol\StatusCode $statusCode
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Protocol\StatusCode
     */
    public function getStatusCode() {
        return $this->statusCode;
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
        $result = $this->getStatusCode() && $this->getStatusCode()->getValue() == Protocol::STATUS_SUCCESS;
        return $result;
    }


    public function setSuccess() {
        $this->setStatusCode(new StatusCode());
        $this->getStatusCode()->setValue(Protocol::STATUS_SUCCESS);
    }



    protected function prepareForXml() {
        if (!$this->getStatusCode()) {
            throw new InvalidXmlException('StatusCode not set');
        }
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $this->prepareForXml();

        $result = $context->getDocument()->createElementNS(Protocol::SAML2, 'samlp:Status');
        $parent->appendChild($result);

        $result->appendChild($this->getStatusCode()->getXml($result, $context));

        if ($this->getMessage()) {
            $statusMessageNode = $context->getDocument()->createElementNS(Protocol::SAML2, 'samlp:StatusMessage', $this->getMessage());
            $result->appendChild($statusMessageNode);
        }

        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'Status' || $xml->namespaceURI != Protocol::SAML2) {
            throw new InvalidXmlException('Expected Status element but got '.$xml->localName);
        }

        $this->iterateChildrenElements($xml, function(\DOMElement $node) {
            if ($node->localName == 'StatusCode' && $node->namespaceURI == Protocol::SAML2) {
                $statusCode = new StatusCode();
                $statusCode->loadFromXml($node);
                $this->setStatusCode($statusCode);
            } else if ($node->localName == 'StatusMessage' && $node->namespaceURI == Protocol::SAML2) {
                $this->setMessage($node->textContent);
            }
        });

        if (!$this->getStatusCode()) {
            throw new InvalidXmlException('Missing StatusCode node');
        }
    }

}
