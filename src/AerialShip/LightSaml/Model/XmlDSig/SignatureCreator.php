<?php

namespace AerialShip\LightSaml\Model\XmlDSig;

use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Protocol;
use AerialShip\LightSaml\Security\X509Certificate;


class SignatureCreator extends Signature implements GetXmlInterface
{
    /** @var string */
    protected $canonicalMethod = \XMLSecurityDSig::EXC_C14N;

    /** @var \XMLSecurityKey */
    protected $xmlSecurityKey;

    /** @var X509Certificate */
    protected $certificate;


    /**
     * @return string
     */
    public function getCanonicalMethod() {
        return $this->canonicalMethod;
    }

    /**
     * @param string $canonicalMethod
     */
    public function setCanonicalMethod($canonicalMethod) {
        $this->canonicalMethod = $canonicalMethod;
    }

    /**
     * @param \XMLSecurityKey $key
     */
    public function setXmlSecurityKey(\XMLSecurityKey $key) {
        $this->xmlSecurityKey = $key;
    }

    /**
     * @return \XMLSecurityKey
     */
    public function getXmlSecurityKey() {
        return $this->xmlSecurityKey;
    }

    /**
     * @param \AerialShip\LightSaml\Security\X509Certificate $certificate
     */
    public function setCertificate(X509Certificate $certificate) {
        $this->certificate = $certificate;
    }

    /**
     * @return \AerialShip\LightSaml\Security\X509Certificate
     */
    public function getCertificate() {
        return $this->certificate;
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMNode
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $objXMLSecDSig = new \XMLSecurityDSig();
        $objXMLSecDSig->setCanonicalMethod($this->getCanonicalMethod());
        $key = $this->getXmlSecurityKey();
        switch ($key->type) {
            case \XMLSecurityKey::RSA_SHA256:
                $type = \XMLSecurityDSig::SHA256;
                break;
            case \XMLSecurityKey::RSA_SHA384:
                $type = \XMLSecurityDSig::SHA384;
                break;
            case \XMLSecurityKey::RSA_SHA512:
                $type = \XMLSecurityDSig::SHA512;
                break;
            default:
                $type = \XMLSecurityDSig::SHA1;
        }

        $objXMLSecDSig->addReferenceList(
            array($parent),
            $type,
            array(Protocol::XMLSEC_TRANSFORM_ALGORITHM_ENVELOPED_SIGNATURE, \XMLSecurityDSig::EXC_C14N),
            array('id_name' => $this->getIDName(), 'overwrite' => FALSE)
        );

        $objXMLSecDSig->sign($key);
        $objXMLSecDSig->add509Cert($this->getCertificate()->getData(), false, false);
        $firstChild = $parent->hasChildNodes() ? $parent->firstChild : null;
        if ($firstChild && $firstChild->localName == 'Issuer') {
            // The signature node should come after the issuer node
            $firstChild = $firstChild->nextSibling;
        }
        $objXMLSecDSig->insertSignature($parent, $firstChild);
    }



}