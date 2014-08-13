<?php

namespace AerialShip\LightSaml\Model\Assertion;

use AerialShip\LightSaml\Error\InvalidSubjectException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Protocol;


class SubjectConfirmation implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;


    /** @var string */
    protected $method;

    /** @var NameID */
    protected $nameID;

    /** @var SubjectConfirmationData */
    protected $data;



    /**
     * @param string $method
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param NameID $nameID
     */
    public function setNameID($nameID) {
        $this->nameID = $nameID;
    }

    /**
     * @return NameID
     */
    public function getNameID() {
        return $this->nameID;
    }

    /**
     * @param SubjectConfirmationData $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return SubjectConfirmationData
     */
    public function getData() {
        return $this->data;
    }






    protected function prepareForXml() {
        if (!$this->getMethod()) {
            throw new InvalidSubjectException('No SubjectConfirmation Method set');
        }
        if (!$this->getData()) {
            throw new InvalidSubjectException('No SubjectConfirmationData set');
        }
    }


    /**
     * @param \DOMNode $parent
     * @param \AerialShip\LightSaml\Meta\SerializationContext $context
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context) {
        $this->prepareForXml();

        $result = $context->getDocument()->createElementNS(Protocol::NS_ASSERTION, 'saml:SubjectConfirmation');
        $parent->appendChild($result);

        $result->setAttribute('Method', $this->getMethod());

        if ($this->getNameID()) {
            $this->getNameID()->getXml($result, $context);
        }

        $this->getData()->getXml($result, $context);

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \LogicException
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'SubjectConfirmation' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected Subject element but got '.$xml->localName);
        }

        if (!$xml->hasAttribute('Method')) {
            throw new InvalidXmlException('Missing Method attribute in SubjectConfirmation');
        }
        $this->setMethod($xml->getAttribute('Method'));


        $this->nameID = null;
        $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'NameID', 'ns'=>Protocol::NS_ASSERTION),
                    'class' => '\AerialShip\LightSaml\Model\Assertion\NameID'
                ),
                array(
                    'node' => array('name'=>'SubjectConfirmationData', 'ns'=>Protocol::NS_ASSERTION),
                    'class' => '\AerialShip\LightSaml\Model\Assertion\SubjectConfirmationData'
                )
            ),
            function ($obj) {
                if ($obj instanceof NameID) {
                    if ($this->getNameID()) {
                        throw new InvalidXmlException('More than one NameID in SubjectConfirmation');
                    }
                    $this->setNameID($obj);
                } else if ($obj instanceof SubjectConfirmationData) {
                    $this->setData($obj);
                } else {
                    throw new \LogicException('Unexpected type '.get_class($obj));
                }
            }
        );
    }

}
