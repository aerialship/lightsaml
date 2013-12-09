<?php

namespace AerialShip\LightSaml\Meta;

use AerialShip\LightSaml\Binding\BindingDetector;
use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Model\Assertion\NameID;
use AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService;
use AerialShip\LightSaml\Model\Protocol\LogoutRequest;
use AerialShip\LightSaml\Model\Protocol\Message;


class LogoutRequestBuilder extends AbstractRequestBuilder
{

    private function getDestination() {
        $idp = $this->getIdpSsoDescriptor();
        $result = null;
        if ($this->spMeta->getLogoutRequestBinding()) {
            $arr = $idp->findSingleLogoutServices($this->spMeta->getLogoutRequestBinding());
            if ($arr) {
                $sso = array_shift($arr);
                $result = $sso->getLocation();
            }
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
     * @param string $nameIDValue
     * @param string|null $nameIDFormat
     * @param string|null $sessionIndex
     * @param string|null $reason
     * @return LogoutRequest
     */
    public function build($nameIDValue, $nameIDFormat = null, $sessionIndex = null, $reason = null)
    {
        $result = new LogoutRequest();
        $edSP = $this->getEdSP();

        $result->setID(Helper::generateID());
        $result->setDestination($this->getDestination());
        $result->setIssueInstant(time());
        if ($reason) {
            $result->setReason($reason);
        }
        if ($sessionIndex) {
            $result->setSessionIndex($sessionIndex);
        }

        $nameID = new NameID();
        $nameID->setValue($nameIDValue);
        if ($nameIDFormat) {
            $nameID->setFormat($nameIDFormat);
        }
        $result->setNameID($nameID);

        $result->setIssuer($edSP->getEntityID());
        return $result;
    }


    /**
     * @param Message $message
     * @return \AerialShip\LightSaml\Binding\RedirectResponse|\AerialShip\LightSaml\Binding\Response
     */
    public function send(Message $message)
    {
        $bindingType = $this->spMeta->getLogoutRequestBinding();
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