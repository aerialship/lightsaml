<?php

namespace AerialShip\LightSaml\Meta;


interface LoadFromXmlInterface
{
    /**
     * @param \DOMElement $xml
     * @return void
     */
    function loadFromXml(\DOMElement $xml);
}