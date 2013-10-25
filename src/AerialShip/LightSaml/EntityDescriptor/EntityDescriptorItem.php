<?php

namespace AerialShip\LightSaml\EntityDescriptor;


abstract class EntityDescriptorItem
{
    /**
     * @return string
     */
    abstract public function toXmlString();

    /**
     * @param \DOMElement $root
     * @return \DOMElement[] unknown elements
     */
    abstract public function loadXml(\DOMElement $root);
}