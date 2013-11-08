<?php

namespace AerialShip\LightSaml\Tests\Binding;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;


class HttpRedirectTest extends Base
{

    function testAuthnRequest() {
        $request = $this->getRequest();
        $id = $request->getID();
        $time = $request->getIssueInstant();

        $binding = new HttpRedirect();
        $url = $binding->getRedirectURL($request);
        $pos = strpos($url, '?');
        $destination = substr($url, 0, $pos);
        $queryString = substr($url, $pos+1);

        $this->assertEquals($this->destination, $destination);

        $data = $binding->parseQuery($queryString);
        $this->checkData($data);

        /** @var AuthnRequest $request */
        $request = $binding->processData($data);
        $this->assertTrue($request instanceof AuthnRequest);
        $this->checkRequest($request, $id, $time);
    }





    private function checkData(array $data) {
        $this->assertTrue(array_key_exists('SAMLRequest', $data));
        $this->assertTrue(array_key_exists('RelayState', $data));
        $this->assertTrue(array_key_exists('SigAlg', $data));
        $this->assertTrue(array_key_exists('Signature', $data));
        $this->assertTrue(array_key_exists('SignedQuery', $data));

        $this->assertEquals($this->relayState, $data['RelayState']);
        $this->assertEquals($this->sigAlg, $data['SigAlg']);
    }

}