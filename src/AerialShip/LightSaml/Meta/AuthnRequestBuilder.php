<?php

namespace AerialShip\LightSaml\Meta;

use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Metadata\Service\SingleSignOnService;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;

class AuthnRequestBuilder extends AbstractRequestBuilder
{
    private function getDestination() {
        $idp = $this->getIdpSsoDescriptor();
        $result = null;
        if ($this->spMeta->getAuthnRequestBinding()) {
            $arr = $idp->findSingleSignOnServices($this->spMeta->getAuthnRequestBinding());
            if ($arr) {
                $result = $arr[0]->getLocation();
            }
        }
        if (!$result) {
            $arr = $idp->findSingleSignOnServices();
            /** @var SingleSignOnService $sso */
            $sso = array_shift($arr);
            $result = $sso->getLocation();
        }
        if (!$result) {
            throw new \LogicException('Unable to find IDP destination');
        }
        return $result;
    }


    private function getProtocolBinding() {
        $result = $this->spMeta->getAuthnRequestBinding();
        if (!$result) {
            $asc = $this->getAssertionConsumerService($this->getSpSsoDescriptor());
            if ($asc) {
                $result = $asc->getBinding();
            }
        }
        if (!$result) {
            throw new \LogicException('Unable to determine protocol binding');
        }
        return $result;
    }

    /**
     * @return AuthnRequest
     */
    function build() {
        $result = new AuthnRequest();
        $edSP = $this->getEdSP();
        $sp = $this->getSpSsoDescriptor();

        $result->setID(Helper::generateID());
        $result->setDestination($this->getDestination());
        $result->setIssueInstant(time());

        $result->setProtocolBinding($this->getProtocolBinding());
        $asc = $this->getAssertionConsumerService($sp);
        $result->setAssertionConsumerServiceURL($asc->getLocation());

        $result->setIssuer($edSP->getEntityID());

        $result->setNameIdPolicyAllowCreate(true);
        $result->setNameIdPolicyFormat($this->spMeta->getNameIdFormat());

        return $result;
    }

}