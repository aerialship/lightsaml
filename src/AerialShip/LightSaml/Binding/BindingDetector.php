<?php

namespace AerialShip\LightSaml\Binding;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Error\InvalidBindingException;


class BindingDetector
{

    /**
     * @param string $binding
     * @return AbstractBinding
     * @throws \LogicException
     * @throws \AerialShip\LightSaml\Error\InvalidBindingException
     */
    function instantiate($binding) {
        switch ($binding) {
            case Bindings::SAML2_HTTP_REDIRECT:
                return new HttpRedirect();
            case Bindings::SAML2_HTTP_POST:
                return new HttpPost();
            case Bindings::SAML2_HTTP_ARTIFACT:
                throw new \LogicException('Artifact binding not implemented');
            case Bindings::SAML2_SOAP:
                throw new \LogicException('SOAP binding not implemented');
        }
        throw new InvalidBindingException("Unknown binding $binding");
    }


    /**
     * @param string $requestMethod $_SERVER['REQUEST_METHOD']
     * @param string $contentType $_SERVER['CONTENT_TYPE']
     * @param array $get
     * @param array $post
     * @throws \AerialShip\LightSaml\Error\InvalidBindingException
     * @return string
     */
    function getBinding($requestMethod, $contentType, array $get, array $post) {
        $requestMethod = trim(strtoupper($requestMethod));
        if ($requestMethod == 'GET') {
            if (array_key_exists('SAMLRequest', $_GET) || array_key_exists('SAMLResponse', $_GET)) {
                return Bindings::SAML2_HTTP_REDIRECT;
            } elseif (array_key_exists('SAMLart', $_GET) ){
                return Bindings::SAML2_HTTP_ARTIFACT;
            }
        } else if ($requestMethod == 'POST') {
            if (array_key_exists('SAMLRequest', $_POST) || array_key_exists('SAMLResponse', $_POST)) {
                return Bindings::SAML2_HTTP_POST;
            } elseif (array_key_exists('SAMLart', $_POST) ){
                return Bindings::SAML2_HTTP_ARTIFACT;
            } else {
                if ($contentType) {
                    $contentType = explode(';', $contentType);
                    $contentType = $contentType[0]; /* Remove charset. */
                    if ($contentType === 'text/xml') {
                        return Bindings::SAML2_SOAP;
                    }
                }
            }
        }
        throw new InvalidBindingException('Unable to determine current binding');
    }

}
