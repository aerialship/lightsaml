<?php

namespace AerialShip\LightSaml\Model\Metadata;

use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Assertion\Attribute;
use AerialShip\LightSaml\Model\Metadata\Service\AbstractService;
use AerialShip\LightSaml\Protocol;


class IdpSsoDescriptor extends SSODescriptor
{
    /** @var  Attribute[] */
    protected $attributes = array();



    /**
     * @param \AerialShip\LightSaml\Model\Assertion\Attribute[] $attributes
     * @return $this|IdpSsoDescriptor
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Assertion\Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param Attribute $attribute
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[] = $attribute;
    }


    /**
     * @param AbstractService $service
     * @return SpSsoDescriptor
     * @throws \InvalidArgumentException
     */
    public function addService(AbstractService $service)
    {
        $class = Helper::getClassNameOnly($service);
        if ($class != 'SingleLogoutService' &&
            $class != 'SingleSignOnService'
        ) {
            throw new \InvalidArgumentException("Invalid service type $class for IDPSSODescriptor");
        }
        return parent::addService($service);
    }


    /**
     * @return string
     */
    public function getXmlNodeName()
    {
        return 'IDPSSODescriptor';
    }


    /**
     * @param \DOMNode $parent
     * @param SerializationContext $context
     * @return \DOMElement
     */
    public function getXml(\DOMNode $parent, SerializationContext $context)
    {
        $result = parent::getXml($parent, $context);

        if ($this->getAttributes()) {
            foreach ($this->getAttributes() as $attribute) {
                $attribute->getXml($result, $context);
            }
        }

        return $result;
    }


}