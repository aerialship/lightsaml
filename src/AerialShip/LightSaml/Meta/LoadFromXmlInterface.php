<?php

namespace AerialShip\LightSaml\Meta;


interface LoadFromXmlInterface
{
    /**
     * @param \DOMElement $xml
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml);
}