<?php

namespace AerialShip\LightSaml\Model\XmlDSig;


interface SignatureValidatorInterface
{
    function validate(\XMLSecurityKey $key);
}
