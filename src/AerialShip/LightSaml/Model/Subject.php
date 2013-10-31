<?php

namespace AerialShip\LightSaml\Model;


use AerialShip\LightSaml\Error\InvalidSubjectException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Protocol;

class Subject implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;


    /** @var NameID */
    protected $nameID;

    /** @var SubjectConfirmation[] */
    protected $subjectConfirmations = array();




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
     * @param \AerialShip\LightSaml\Model\SubjectConfirmation $subjectConfirmation
     */
    public function addSubjectConfirmation($subjectConfirmation) {
        $this->subjectConfirmations[] = $subjectConfirmation;
    }

    /**
     * @return \AerialShip\LightSaml\Model\SubjectConfirmation[]
     */
    public function getSubjectConfirmations() {
        return $this->subjectConfirmations;
    }



    protected function prepareForXml() {
        if (!$this->getNameID()) {
            throw new InvalidSubjectException('No NameID set');
        }
    }



    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $this->prepareForXml();

        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElement('Subject');
        $parent->appendChild($result);

        $this->getNameID()->getXml($result);

        foreach ($this->getSubjectConfirmations() as $sc) {
            $sc->getXml($result);
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \LogicException
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'Subject' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected Subject element but got '.$xml->localName);
        }

        $this->nameID = null;
        $this->subjectConfirmations = array();

        $result = $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'NameID', 'ns'=>Protocol::NS_ASSERTION),
                    'class' => '\AerialShip\LightSaml\Model\NameID'
                ),
                array(
                    'node' => array('name'=>'SubjectConfirmation', 'ns'=>Protocol::NS_ASSERTION),
                    'class' => '\AerialShip\LightSaml\Model\SubjectConfirmation'
                )
            ),
            function ($object) {
                if ($object instanceof NameID) {
                    if ($this->getNameID()) {
                        throw new InvalidXmlException('More than one NameID in Subject');
                    }
                    $this->setNameID($object);
                } else if ($object instanceof SubjectConfirmation) {
                    $this->addSubjectConfirmation($object);
                } else {
                    throw new \LogicException('Unexpected type '.get_class($object));
                }
            }
        );
        if (!$this->getNameID()) {
            throw new InvalidXmlException('Missing NameID element in Subject');
        }
        if (!$this->getSubjectConfirmations()) {
            throw new InvalidXmlException('Missing SubjectConfirmation element in Subject');
        }

        return $result;
    }


}