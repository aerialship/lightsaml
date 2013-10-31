<?php

namespace AerialShip\LightSaml\Model;


use AerialShip\LightSaml\Error\InvalidSubjectException;
use AerialShip\LightSaml\Error\InvalidXmlException;
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
     * @param \AerialShip\LightSaml\Model\NameID $nameID
     */
    public function setNameID($nameID) {
        $this->nameID = $nameID;
    }

    /**
     * @return \AerialShip\LightSaml\Model\NameID
     */
    public function getNameID() {
        return $this->nameID;
    }

    /**
     * @param \AerialShip\LightSaml\Model\SubjectConfirmationData $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return \AerialShip\LightSaml\Model\SubjectConfirmationData
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
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $this->prepareForXml();

        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElement('SubjectConfirmation', $this->getValue());
        $parent->appendChild($result);

        $result->setAttribute('Method', $this->getMethod());


        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \LogicException
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
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
        $result = $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'NameID', 'ns'=>Protocol::NS_ASSERTION),
                    'class' => '\AerialShip\LightSaml\Model\NameID'
                ),
                array(
                    'node' => array('name'=>'SubjectConfirmationData', 'ns'=>Protocol::NS_ASSERTION),
                    'class' => '\AerialShip\LightSaml\Model\SubjectConfirmationData'
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

        return $result;
    }

}