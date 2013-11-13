<?php

namespace AerialShip\LightSaml\Meta;

use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Assertion\NameID;
use AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService;
use AerialShip\LightSaml\Model\Protocol\LogoutRequest;


class LogoutRequestBuilder extends AbstractRequestBuilder
{

    private function getDestination() {
        $idp = $this->getIdpSsoDescriptor();
        $result = null;
        if ($this->spMeta->getAuthnRequestBinding()) {
            $result = $idp->findSingleLogoutServices($this->spMeta->getAuthnRequestBinding());
        }
        if (!$result) {
            $arr = $idp->findSingleLogoutServices();
            /** @var SingleLogoutService $sso */
            $sso = array_shift($arr);
            $result = $sso->getLocation();
        }
        if (!$result) {
            throw new \LogicException('Unable to find IDP destination');
        }
        return $result;
    }


    /**
     * @return LogoutRequest
     */
    function build($NameIDFormat, $NameIDValue, $sessionIndex = null, $reason = null) {
        $result = new LogoutRequest();
        $edSP = $this->getEdSP();
        $sp = $this->getSpSsoDescriptor();

        $result->setID(Helper::generateID());
        $result->setDestination($this->getDestination());
        $result->setIssueInstant(time());
        $result->setNotOnOrAfter(new \DateTime('now', new \DateTimeZone('UTC')));
        if($reason) $result->setReason($reason);
        if($sessionIndex) $result->setSessionIndex($sessionIndex);

        $nameID = new NameID();
        $nameID->setFormat($NameIDFormat);
        $nameID->setValue($NameIDValue);
        $result->setNameID($nameID);

        $result->setIssuer($edSP->getEntityID());
        return $result;
    }
} 