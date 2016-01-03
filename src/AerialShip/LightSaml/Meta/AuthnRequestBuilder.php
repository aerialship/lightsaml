<?php

namespace AerialShip\LightSaml\Meta;

use AerialShip\LightSaml\Binding\BindingDetector;
use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Error\BuildRequestException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;
use AerialShip\LightSaml\Model\Protocol\Message;

class AuthnRequestBuilder extends AbstractRequestBuilder
{

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

        $asc = $this->getAssertionConsumerService();
        $result->setAssertionConsumerServiceURL($asc->getLocation());
        $result->setProtocolBinding($asc->getBinding());

        $result->setIssuer($edSP->getEntityID());

        if ($this->spMeta->getNameIdFormat()) {
            $result->setNameIdPolicyFormat($this->spMeta->getNameIdFormat());
        }

        $result->setSignature($this->signature);

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


    /**
     * @return AssertionConsumerService
     * @throws BuildRequestException
     */
    protected function getAssertionConsumerService()
    {
        $sp = $this->getSpSsoDescriptor();
        $arr = $sp->findAssertionConsumerServices();
        if (empty($arr)) {
            throw new BuildRequestException('SPSSODescriptor does not have any AssertionConsumerService');
        }
        $result = $this->findServiceByBinding($arr, $this->spMeta->getResponseBinding());
        if (!$result) {
            throw new BuildRequestException('SPSSODescriptor does not have AssertionConsumerService with binding '.$this->spMeta->getResponseBinding());
        }
        return $result;
    }

    /**
     * @return string
     * @throws \AerialShip\LightSaml\Error\BuildRequestException
     */
    protected function getDestination()
    {
        $idp = $this->getIdpSsoDescriptor();
        $arr = $idp->findSingleSignOnServices();
        if (empty($arr)) {
            throw new BuildRequestException('IDPSSODescriptor does not have any SingleSignOnService');
        }
        $result = $this->findServiceByBinding($arr, $this->spMeta->getAuthnRequestBinding());
        if (!$result) {
            throw new BuildRequestException('IDPSSODescriptor does not have SingleSignOnService with binding '.$this->spMeta->getAuthnRequestBinding());
        }

        return $result->getLocation();
    }


    /**
     * @return string
     * @throws \AerialShip\LightSaml\Error\BuildRequestException
     */
    protected function getProtocolBinding()
    {
        $result = $this->spMeta->getAuthnRequestBinding();
        if (!$result) {
            $asc = $this->getAssertionConsumerService();
            if ($asc) {
                $result = $asc->getBinding();
            }
        }
        if (!$result) {
            throw new BuildRequestException('Unable to determine protocol binding');
        }
        return $result;
    }

}