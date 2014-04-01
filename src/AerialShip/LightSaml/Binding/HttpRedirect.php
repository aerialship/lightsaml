<?php

namespace AerialShip\LightSaml\Binding;

use AerialShip\LightSaml\Error\BindingException;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Protocol\AbstractRequest;
use AerialShip\LightSaml\Model\Protocol\Message;
use AerialShip\LightSaml\Model\XmlDSig\SignatureCreator;
use AerialShip\LightSaml\Model\XmlDSig\SignatureStringValidator;
use AerialShip\LightSaml\Protocol;


class HttpRedirect extends AbstractBinding
{

    /**
     * @param Message $message
     * @return RedirectResponse
     */
    public function send(Message $message)
    {
        $url = $this->getRedirectURL($message);
        $result = new RedirectResponse($url);
        return $result;
    }


    /**
     * @param Request $request
     * @throws \RuntimeException
     * @throws \AerialShip\LightSaml\Error\BindingException
     * @return Message
     */
    public function receive(Request $request)
    {
        $data = $this->parseQuery($request);
        return $this->processData($data);
    }

    /**
     * @param array $data
     * @throws \RuntimeException
     * @throws \AerialShip\LightSaml\Error\BindingException
     * @return Message
     */
    private function processData(array $data)
    {
        $msg = $this->getMessageStringFromData($data);
        $encoding = $this->getEncodingFromData($data);
        $msg = $this->decodeMessageString($msg, $encoding);

        $this->dispatchReceive($msg);

        $message = $this->loadMessageFromXml($msg);

        $this->loadRelayState($message, $data);
        $this->loadSignature($message, $data);

        return $message;
    }


    /**
     * @param array $data
     * @return string
     * @throws \RuntimeException
     */
    private function getMessageStringFromData(array $data)
    {
        if (array_key_exists('SAMLRequest', $data)) {
            return $data['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $data)) {
            return $data['SAMLResponse'];
        } else {
            throw new \RuntimeException('Missing SAMLRequest or SAMLResponse parameter');
        }

    }

    /**
     * @param array $data
     * @return string
     */
    private function getEncodingFromData(array $data)
    {
        if (array_key_exists('SAMLEncoding', $data)) {
            return $data['SAMLEncoding'];
        } else {
            return Protocol::ENCODING_DEFLATE;
        }
    }

    /**
     * @param string $msg
     * @param string $encoding
     * @return string
     * @throws \RuntimeException
     */
    private function decodeMessageString($msg, $encoding)
    {
        $msg = base64_decode($msg);
        switch ($encoding) {
            case Protocol::ENCODING_DEFLATE:
                $msg = gzinflate($msg);
                break;
            default:
                throw new \RuntimeException("Unknown encoding: $encoding");
        }
        return $msg;
    }


    /**
     * @param string $msg
     * @return Message
     */
    private function loadMessageFromXml($msg)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($msg);
        $message = Message::fromXML($doc->firstChild);

        return $message;
    }

    private function loadRelayState(Message $message, array $data)
    {
        if (array_key_exists('RelayState', $data)) {
            $message->setRelayState($data['RelayState']);
        }
    }

    private function loadSignature(Message $message, array $data)
    {
        if (array_key_exists('Signature', $data)) {
            if (!array_key_exists('SigAlg', $data)) {
                throw new BindingException('Missing signature algorithm.');
            }
            $message->setSignature(
                new SignatureStringValidator($data['Signature'], $data['SigAlg'], $data['SignedQuery'])
            );
        }
    }


    /**
     * @param Message $message
     * @return string
     * @throws \RuntimeException
     */
    private function getRedirectURL(Message $message)
    {
        $xml = $this->getMessageEncodedXml($message);
        $msg = $this->addMessageToUrl($message, $xml);
        $this->addRelayStateToUrl($msg, $message);
        $this->addSignatureToUrl($msg, $message);

        return $this->getDestinationUrl($msg, $message);
    }


    /**
     * @param Message $message
     * @return \AerialShip\LightSaml\Model\XmlDSig\SignatureCreator|null
     * @throws \RuntimeException
     */
    private function getMessageSignature(Message $message)
    {
        $signature = $message->getSignature();
        if ($signature && !$signature instanceof SignatureCreator) {
            throw new \RuntimeException('Signature must be SignatureCreator');
        }

        return $signature;
    }


    /**
     * @param Message $message
     * @return string
     */
    private function getMessageEncodedXml(Message $message)
    {
        $context = new SerializationContext();
        $message->getXml($context->getDocument(), $context);
        $xml = $context->getDocument()->saveXML();

        $this->dispatchSend($xml);

        $xml = gzdeflate($xml);
        $xml = base64_encode($xml);

        return $xml;
    }

    /**
     * @param Message $message
     * @param string $xml
     * @return string
     */
    private function addMessageToUrl(Message $message, $xml)
    {
        if ($message instanceof AbstractRequest) {
            $msg = 'SAMLRequest=';
        } else {
            $msg = 'SAMLResponse=';
        }
        $msg .= urlencode($xml);

        return $msg;
    }


    /**
     * @param string $msg
     * @param Message $message
     */
    private function addRelayStateToUrl(&$msg, Message $message)
    {
        if ($message->getRelayState() !== null) {
            $msg .= '&RelayState=' . urlencode($message->getRelayState());
        }
    }


    private function addSignatureToUrl(&$msg, Message $message)
    {
        $signature = $this->getMessageSignature($message);
        /** @var $key \XMLSecurityKey */
        $key = $signature ? $signature->getXmlSecurityKey() : null;

        if ($key !== NULL) {
            $msg .= '&SigAlg=' . urlencode($key->type);
            $signature = $key->signData($msg);
            $msg .= '&Signature=' . urlencode(base64_encode($signature));
        }
    }

    /**
     * @param string $msg
     * @param Message $message
     * @return string
     */
    private function getDestinationUrl($msg, Message $message)
    {
        $destination = $message->getDestination() ?: $this->getDestination();
        if (strpos($destination, '?') === FALSE) {
            $destination .= '?' . $msg;
        } else {
            $destination .= '&' . $msg;
        }

        return $destination;
    }


    /**
     * @param Request $request
     * @return array
     */
    private function parseQuery(Request $request)
    {
        /*
         * Parse the query string. We need to do this ourself, so that we get access
         * to the raw (urlencoded) values. This is required because different software
         * can urlencode to different values.
         */
        $sigQuery = $relayState = $sigAlg = '';
        $data = $request->parseQueryString(null, false);
        $result = array();
        foreach ($data as $name=>$value) {
            $result[$name] = urldecode($value);
            switch ($name) {
                case 'SAMLRequest':
                case 'SAMLResponse':
                    $sigQuery = $name . '=' . $value;
                    break;
                case 'RelayState':
                    $relayState = '&RelayState=' . $value;
                    break;
                case 'SigAlg':
                    $sigAlg = '&SigAlg=' . $value;
                    break;
            }
        }
        $result['SignedQuery'] = $sigQuery . $relayState . $sigAlg;
        return $result;
    }

} 