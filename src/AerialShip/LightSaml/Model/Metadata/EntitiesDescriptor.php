<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\GetXmlInterface;
use AerialShip\LightSaml\Meta\LoadFromXmlInterface;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Meta\XmlChildrenLoaderTrait;
use AerialShip\LightSaml\Model\XmlDSig\Signature;
use AerialShip\LightSaml\Model\XmlDSig\SignatureCreator;
use AerialShip\LightSaml\Protocol;

class EntitiesDescriptor implements GetXmlInterface, LoadFromXmlInterface
{
    use XmlChildrenLoaderTrait;


    /** @var  int */
    protected $validUntil;

    /** @var  string */
    protected $cacheDuration;

    /** @var  string */
    protected $id;

    /** @var  string */
    protected $name;

    /** @var  Signature */
    protected $signature;

    /** @var EntitiesDescriptor[]|EntityDescriptor[] */
    protected $items = array();


    /**
     * @param string $cacheDuration
     * @throws \InvalidArgumentException
     */
    public function setCacheDuration($cacheDuration) {
        try {
            new \DateInterval($cacheDuration);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException('Invalid duration format', 0, $ex);
        }
        $this->cacheDuration = $cacheDuration;
    }

    /**
     * @return string
     */
    public function getCacheDuration() {
        return $this->cacheDuration;
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param \AerialShip\LightSaml\Model\XmlDSig\Signature $signature
     */
    public function setSignature(Signature $signature) {
        $this->signature = $signature;
    }

    /**
     * @return \AerialShip\LightSaml\Model\XmlDSig\Signature
     */
    public function getSignature() {
        return $this->signature;
    }

    /**
     * @param int|string $validUntil
     * @throws \InvalidArgumentException
     */
    public function setValidUntil($validUntil) {
        if (is_string($validUntil)) {
            $validUntil = Helper::parseSAMLTime($validUntil);
        } else if (!is_int($validUntil) || $validUntil < 1) {
            throw new \InvalidArgumentException('Invalid validUntil');
        }
        $this->validUntil = $validUntil;
    }

    /**
     * @return int
     */
    public function getValidUntil() {
        return $this->validUntil;
    }





    /**
     * @param EntitiesDescriptor|EntityDescriptor $item
     * @return $this|EntitiesDescriptor
     * @throws \InvalidArgumentException
     */
    public function addItem($item)
    {
        if (!($item instanceof EntitiesDescriptor) && !($item instanceof EntityDescriptor)) {
            throw new \InvalidArgumentException('Expected EntitiesDescriptor or EntityDescriptor');
        }
        if ($item === $this) {
            throw new \InvalidArgumentException('Circular reference detected');
        }
        if ($item instanceof EntitiesDescriptor) {
            if ($item->containsItem($this)) {
                throw new \InvalidArgumentException('Circular reference detected');
            }
        }
        $this->items[] = $item;
        return $this;
    }


    /**
     * @param EntitiesDescriptor|EntityDescriptor $item
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function containsItem($item) {
        $result = false;
        if (!($item instanceof EntitiesDescriptor) && !($item instanceof EntityDescriptor)) {
            throw new \InvalidArgumentException('Expected EntitiesDescriptor or EntityDescriptor');
        }
        foreach ($this->items as $i) {
            if ($i === $item) {
                $result = true;
                break;
            }
            if ($i instanceof EntitiesDescriptor) {
                if ($i->containsItem($item)) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @return EntitiesDescriptor[]|EntityDescriptor[]
     */
    public function getAllItems()
    {
        return $this->items;
    }


    /**
     * @return EntityDescriptor[]
     */
    public function getAllEntityDescriptors()
    {
        $result = array();
        foreach ($this->items as $item) {
            if ($item instanceof EntitiesDescriptor) {
                $result = array_merge($result, $item->getAllEntityDescriptors());
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }


    /**
     * @param \DOMNode $parent
     * @param SerializationContext $context
     * @throws \RuntimeException
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent, SerializationContext $context)
    {
        $result = $context->getDocument()->createElementNS(Protocol::NS_METADATA, 'md:EntitiesDescriptor');
        $parent->appendChild($result);

        if ($this->getValidUntil()) {
            $result->setAttribute('validUntil', Helper::time2string($this->getValidUntil()));
        }
        if ($this->getCacheDuration()) {
            $result->setAttribute('cacheDuration', $this->getCacheDuration());
        }
        if ($this->getId()) {
            $result->setAttribute('ID', $this->getId());
        }
        if ($this->getName()) {
            $result->setAttribute('Name', $this->getName());
        }

        if ($signature = $this->getSignature()) {
            if ($signature instanceof SignatureCreator) {
                $signature->getXml($result, $context);
            } else {
                throw new \RuntimeException('Signature must be SignatureCreator');
            }
        }

        foreach ($this->items as $item) {
            $item->getXml($result, $context);
        }

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'EntitiesDescriptor' || $xml->namespaceURI != Protocol::NS_METADATA) {
            throw new InvalidXmlException('Expected EntitiesDescriptor element and '.Protocol::NS_METADATA.' namespace but got '.$xml->localName);
        }

        if ($xml->hasAttribute('validUntil')) {
            $this->setValidUntil(Helper::parseSAMLTime($xml->getAttribute('validUntil')));
        }
        if ($xml->hasAttribute('cacheDuration')) {
            $this->setCacheDuration($xml->getAttribute('cacheDuration'));
        }
        if ($xml->hasAttribute('ID')) {
            $this->setId($xml->getAttribute('ID'));
        }
        if ($xml->hasAttribute('Name')) {
            $this->setName($xml->getAttribute('Name'));
        }

        $this->items = array();
        $this->loadXmlChildren(
            $xml,
            array(
                array(
                    'node' => array('name'=>'EntitiesDescriptor', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\EntitiesDescriptor'
                ),
                array(
                    'node' => array('name'=>'EntityDescriptor', 'ns'=>Protocol::NS_METADATA),
                    'class' => '\AerialShip\LightSaml\Model\Metadata\EntityDescriptor'
                ),
            ),
            function(LoadFromXmlInterface $obj) {
                $this->addItem($obj);
            }
        );

        if (empty($this->items)) {
            throw new InvalidXmlException('Expected at least one of EntityDescriptor or EntitiesDescriptor');
        }
    }

} 