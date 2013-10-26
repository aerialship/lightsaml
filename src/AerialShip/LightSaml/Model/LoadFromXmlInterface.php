<?php

namespace AerialShip\LightSaml\Model;


interface LoadFromXmlInterface
{
    /**
     * @param \DOMElement $xml
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml);
}