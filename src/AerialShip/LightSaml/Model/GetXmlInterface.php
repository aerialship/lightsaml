<?php

namespace AerialShip\LightSaml\Model;


interface GetXmlInterface
{
    /**
     * @param \DOMNode $parent
     * @return \DOMNode
     */
    function getXml(\DOMNode $parent);
}