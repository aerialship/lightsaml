<?php

namespace AerialShip\LightSaml\Binding;


class Request
{
    /** @var  string */
    protected $requestMethod;

    /** @var  string */
    protected $queryString;

    /** @var  string */
    protected $contentType;

    /** @var  array */
    protected $get;

    /** @var  array */
    protected $post;

    /**
     * @return Request
     */
    public static function fromGlobals()
    {
        $result = new Request();
        $result->setContentType($_SERVER['CONTENT_TYPE']);
        $result->setGet($_GET);
        $result->setPost($_POST);
        $result->setQueryString($_SERVER['QUERY_STRING']);
        $result->setRequestMethod($_SERVER['REQUEST_METHOD']);

        return $result;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param array $get
     */
    public function setGet($get)
    {
        $this->get = $get;
    }

    /**
     * @return array
     */
    public function getGet()
    {
        return $this->get;
    }

    /**
     * @param array $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param string $queryString
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param string $requestMethod
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }





    public function parseQueryString($queryString = null, $urlDecodeValues = false)
    {
        if ($queryString) {
            $this->queryString = $queryString;
        }
        $result = array();
        foreach (explode('&', $this->queryString) as $e) {
            $tmp = explode('=', $e, 2);
            $name = $tmp[0];
            $value = count($tmp) === 2 ? $value = $tmp[1] : '';
            $name = urldecode($name);
            $result[$name] = $urlDecodeValues ? urldecode($value) : $value;
        }
        return $result;
    }

}