<?php
/**
 * @desc
 * @author Ostojić Aleksandar <ao@boutsourcing.com> 11/7/13
 */

namespace AerialShip\LightSaml\Meta;


use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService;
use AerialShip\LightSaml\Model\Protocol\LogoutRequest;

class LogoutRequestBuilder extends AbstractRequestBuilder{

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
    function build() {
        $result = new LogoutRequest();
        $edSP = $this->getEdSP();
        $sp = $this->getSpSsoDescriptor();

        $result->setID(Helper::generateID());
        $result->setDestination($this->getDestination());
        $result->setIssueInstant(time());
        $result->setNotOnOrAfter(new \DateTime('now', new \DateTimeZone('UTC')));
        $result->setReason('LogoutRequestBuilder');

        $asc = $this->getAssertionConsumerService($sp);
        $result->setIssuer($edSP->getEntityID());
        return $result;
    }
} 