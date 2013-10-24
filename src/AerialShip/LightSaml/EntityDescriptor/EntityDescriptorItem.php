<?php

namespace AerialShip\LightSaml\EntityDescriptor;


abstract class EntityDescriptorItem
{
    /**
     * @return string
     */
    abstract function toXmlString();
}