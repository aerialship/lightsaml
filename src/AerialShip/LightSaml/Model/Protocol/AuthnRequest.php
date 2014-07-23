<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidRequestException;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\XmlDSig\SignatureXmlValidator;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Protocol;


class AuthnRequest extends AbstractRequest
{

    /** @var string */
    protected $assertionConsumerServiceURL;

    /** @var string */
    protected $protocolBinding;


    /** @var string */
    protected $nameIdPolicyFormat;

    /** @var bool */
    protected $nameIdPolicyAllowCreate = true;



    function getXmlNodeLocalName() {
        return 'AuthnRequest';
    }

    function getXmlNodeNamespace() {
        return Protocol::SAML2;
    }


    /**
     * @param string $assertionConsumerServiceURL
     */
    public function setAssertionConsumerServiceURL($assertionConsumerServiceURL) {
        $this->assertionConsumerServiceURL = $assertionConsumerServiceURL;
    }

    /**
     * @return string
     */
    public function getAssertionConsumerServiceURL() {
        return $this->assertionConsumerServiceURL;
    }




    /**
     * @param bool $nameIdPolicyAllowCreate
     */
    public function setNameIdPolicyAllowCreate($nameIdPolicyAllowCreate) {
        $this->nameIdPolicyAllowCreate = (bool)$nameIdPolicyAllowCreate;
    }

    /**
     * @return bool
     */
    public function getNameIdPolicyAllowCreate() {
        return $this->nameIdPolicyAllowCreate;
    }

    /**
     * @param string $nameIdPolicyFormat
     * @throws \InvalidArgumentException
     */
    public function setNameIdPolicyFormat($nameIdPolicyFormat) {
        if (!NameIDPolicy::isValid($nameIdPolicyFormat)) {
            throw new \InvalidArgumentException('Invalid NameIDPolicy');
        }
        $this->nameIdPolicyFormat = $nameIdPolicyFormat;
    }

    /**
     * @return string
     */
    public function getNameIdPolicyFormat() {
        return $this->nameIdPolicyFormat;
    }

    /**
     * @param string $protocolBinding
     */
    public function setProtocolBinding($protocolBinding) {
        $this->protocolBinding = $protocolBinding;
    }

    /**
     * @return string
     */
    public function getProtocolBinding() {
        return $this->protocolBinding;
    }





    protected function prepareForXml() {
        parent::prepareForXml();
        if (!$this->getAssertionConsumerServiceURL()) {
            throw new InvalidRequestException('AuthRequest AssertionConsumerServiceURL not set');
        }
        if (!$this->getProtocolBinding()) {
            throw new InvalidRequestException('AuthnRequest ProtocolBinding not set');
        }
        if (!NameIDPolicy::isValid($this->getNameIdPolicyFormat())) {
            throw new InvalidRequestException('AuthnRequest NameIDPolicy Format not set');
        }
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $result = parent::getXml($parent, $context);

        $result->setAttribute('AssertionConsumerServiceURL', $this->getAssertionConsumerServiceURL());
        $result->setAttribute('ProtocolBinding', $this->getProtocolBinding());

        $nameIDPolicyNode = $context->getDocument()->createElementNS(Protocol::SAML2, 'samlp:NameIDPolicy');
        $result->appendChild($nameIDPolicyNode);
        $nameIDPolicyNode->setAttribute('Format', $this->getNameIdPolicyFormat());
        $nameIDPolicyNode->setAttribute('AllowCreate', $this->getNameIdPolicyAllowCreate() ? 'true' : 'false');

        return $result;
    }


    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        parent::loadFromXml($xml);

        $this->setAssertionConsumerServiceURL($xml->getAttribute('AssertionConsumerServiceURL'));
        $this->setProtocolBinding($xml->getAttribute('ProtocolBinding'));

        $signatureNode = null;

        $this->iterateChildrenElements($xml, function(\DOMElement $node) use (&$signatureNode) {
            if ($node->localName == 'NameIDPolicy' && $node->namespaceURI == Protocol::SAML2) {
                $this->checkRequiredAttributes($node, array('Format', 'AllowCreate'));
                $this->setNameIdPolicyFormat($node->getAttribute('Format'));
                $this->setNameIdPolicyAllowCreate($node->getAttribute('AllowCreate') == 'true');
            } else if ($node->localName == 'Signature' && $node->namespaceURI == Protocol::NS_XMLDSIG) {
                $signatureNode = $node;
            }
        });

        if ($signatureNode) {
            $signature = new SignatureXmlValidator();
            $signature->loadFromXml($signatureNode);
            $this->setSignature($signature);
        }
    }

}