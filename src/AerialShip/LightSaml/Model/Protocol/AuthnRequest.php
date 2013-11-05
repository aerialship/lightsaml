<?php

namespace AerialShip\LightSaml\Model\Protocol;

use AerialShip\LightSaml\Error\InvalidRequestException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\XmlRequiredAttributesTrait;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\LightSaml\Protocol;


class AuthnRequest extends AbstractRequest
{
    use XmlRequiredAttributesTrait;

    /** @var string */
    protected $assertionConsumerServiceURL;

    /** @var string */
    protected $protocolBinding;


    /** @var string */
    protected $nameIdPolicyFormat = NameIDPolicy::PERSISTENT;

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
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $result = parent::getXml($parent);

        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;

        $result->setAttribute('AssertionConsumerServiceURL', $this->getAssertionConsumerServiceURL());
        $result->setAttribute('ProtocolBinding', $this->getProtocolBinding());

        $nameIDPolicyNode = $doc->createElementNS(Protocol::SAML2, 'samlp:NameIDPolicy');
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
        $result = parent::loadFromXml($xml);

        $this->checkRequiredAttributes($xml, array('AssertionConsumerServiceURL', 'ProtocolBinding'));
        $this->setAssertionConsumerServiceURL($xml->getAttribute('AssertionConsumerServiceURL'));
        $this->setProtocolBinding($xml->getAttribute('ProtocolBinding'));

        $this->iterateChildrenElements($xml, function(\DOMElement $node) {
            if ($node->localName == 'NameIDPolicy' && $node->namespaceURI == Protocol::SAML2) {
                $this->checkRequiredAttributes($node, array('Format', 'AllowCreate'));
                $this->setNameIdPolicyFormat($node->getAttribute('Format'));
                $this->setNameIdPolicyAllowCreate($node->getAttribute('AllowCreate') == 'true');
            }
        });
    }

}