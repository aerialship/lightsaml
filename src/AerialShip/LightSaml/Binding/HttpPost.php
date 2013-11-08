<?php

namespace AerialShip\LightSaml\Binding;


use AerialShip\LightSaml\Error\BindingException;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Protocol\AbstractRequest;
use AerialShip\LightSaml\Model\Protocol\Message;

class HttpPost extends AbstractBinding
{

    /**
     * @param Message $message
     * @return void
     */
    function send(Message $message) {
        $data = $this->getPostData($message);
        $template = new HttpPostTemplate($data);
        $template->render();
        exit;
    }


    /**
     * @param Message $message
     * @return array
     *      destination => string
     *      post => array( name => value )
     */
    function getPostData(Message $message) {
        $destination = $message->getDestination() ?: $this->getDestination();

        $context = new SerializationContext();
        $message->getSignedXml($context->getDocument(), $context);
        $msgStr = $context->getDocument()->saveXML();
        $msgStr = base64_encode($msgStr);

        $type = $message instanceof AbstractRequest ? 'SAMLRequest' : 'SAMLResponse';

        $result = array(
            'destination' => $destination,
            'post' => array($type => $msgStr)
        );
        if ($message->getRelayState()) {
            $result['post']['RelayState'] = $message->getRelayState();
        }
        return $result;
    }


    /**
     * @param array $post
     * @throws \AerialShip\LightSaml\Error\BindingException
     * @return Message
     */
    function receive(array $post = null) {
        if (!$post) {
            $post = $_POST;
        }

        if (array_key_exists('SAMLRequest', $post)) {
            $msg = $post['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $post)) {
            $msg = $post['SAMLResponse'];
        } else {
            throw new BindingException('Missing SAMLRequest or SAMLResponse parameter');
        }

        $msg = base64_decode($msg);
        $doc = new \DOMDocument();
        $doc->loadXML($msg);
        $result = Message::fromXML($doc->firstChild);

        if (array_key_exists('RelayState', $post)) {
            $result->setRelayState($post['RelayState']);
        }

        return $result;
    }


}
