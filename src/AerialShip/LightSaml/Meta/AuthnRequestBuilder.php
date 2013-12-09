<?php

namespace AerialShip\LightSaml\Meta;

use AerialShip\LightSaml\Binding\BindingDetector;
use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Metadata\Service\SingleSignOnService;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;
use AerialShip\LightSaml\Model\Protocol\Message;

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
            $asc = $this->getAssertionConsumerService();
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
    public function build()
    {
        $result = new AuthnRequest();
        $edSP = $this->getEdSP();

        $result->setID(Helper::generateID());
        $result->setDestination($this->getDestination());
        $result->setIssueInstant(time());

        $result->setProtocolBinding($this->getProtocolBinding());
        $asc = $this->getAssertionConsumerService();
        $result->setAssertionConsumerServiceURL($asc->getLocation());

        $result->setIssuer($edSP->getEntityID());

        $result->setNameIdPolicyAllowCreate(true);
        $result->setNameIdPolicyFormat($this->spMeta->getNameIdFormat());

        return $result;
    }


    /**
     * @param Message $message
     * @return \AerialShip\LightSaml\Binding\Response
     */
    public function send(Message $message)
    {
        $bindingType = $this->spMeta->getAuthnRequestBinding();
        if ($bindingType) {
            $detector = new BindingDetector();
            $binding = $detector->instantiate($bindingType);
        } else {
            $binding = new HttpRedirect();
        }
        $result = $binding->send($message);
        return $result;
    }


}