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
     * @param Request $request
     * @throws \AerialShip\LightSaml\Error\InvalidBindingException
     * @return string|null
     */
    public function getBinding(Request $request)
    {
        $requestMethod = trim(strtoupper($request->getRequestMethod()));
        if ($requestMethod == 'GET') {
            return $this->processGET($request);
        } else if ($requestMethod == 'POST') {
            return $this->processPOST($request);
        }
        return null;
    }


    /**
     * @param Request $request
     * @return null|string
     */
    private function processGET(Request $request)
    {
        $get = $request->getGet();
        if (array_key_exists('SAMLRequest', $get) || array_key_exists('SAMLResponse', $get)) {
            return Bindings::SAML2_HTTP_REDIRECT;
        } elseif (array_key_exists('SAMLart', $get) ){
            return Bindings::SAML2_HTTP_ARTIFACT;
        }
        return null;
    }


    /**
     * @param Request $request
     * @return null|string
     */
    private function processPOST(Request $request)
    {
        $post = $request->getPost();
        if (array_key_exists('SAMLRequest', $post) || array_key_exists('SAMLResponse', $post)) {
            return Bindings::SAML2_HTTP_POST;
        } elseif (array_key_exists('SAMLart', $post) ){
            return Bindings::SAML2_HTTP_ARTIFACT;
        } else {
            if ($request->getContentType()) {
                $contentType = explode(';', $request->getContentType());
                $contentType = $contentType[0]; /* Remove charset. */
                if ($contentType === 'text/xml') {
                    return Bindings::SAML2_SOAP;
                }
            }
        }
        return null;
    }

}
