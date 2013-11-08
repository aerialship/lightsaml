<?php

namespace AerialShip\LightSaml\Tests\Binding;

use AerialShip\LightSaml\Binding\HttpPost;
use AerialShip\LightSaml\Model\Protocol\AuthnRequest;


class HttpPostTest extends Base
{
    function testAuthnRequest() {
        $request = $this->getRequest();
        $id = $request->getID();
        $time = $request->getIssueInstant();

        $binding = new HttpPost();
        $data = $binding->getPostData($request);
        $this->assertArrayHasKey('destination', $data);
        $this->assertEquals($this->destination, $data['destination']);

        $request = $binding->receive($data['post']);
        $this->assertTrue($request instanceof AuthnRequest);
        $this->checkRequest($request, $id, $time);
    }
} 