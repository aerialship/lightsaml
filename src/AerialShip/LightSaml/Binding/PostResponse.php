<?php

namespace AerialShip\LightSaml\Binding;


class PostResponse extends Response
{
    /** @var  array */
    protected $data;


    /**
     * @param string $destination
     * @param array $data
     */
    public function __construct($destination, array $data = array())
    {
        parent::__construct($destination);
        $this->data = $data;
    }


    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }




    /**
     * @return string
     */
    public function render()
    {
        $template = new HttpPostTemplate($this->getDestination(), $this->getData());
        ob_start();
        $template->render();
        $result = ob_get_clean();
        return $result;
    }

} 