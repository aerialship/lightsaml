<?php

namespace AerialShip\LightSaml\EntityDescriptor;


class EntityDescriptor
{
    /** @var string */
    protected $entityID;

    /** @var EntityDescriptorItem[] */
    protected $items = array();



    /**
     * @param string $entityID
     */
    public function setEntityID($entityID) {
        $this->entityID = $entityID;
    }

    /**
     * @return string
     */
    public function getEntityID() {
        return $this->entityID;
    }

    /**
     * @param EntityDescriptorItem[] $items
     */
    public function setItems(array $items) {
        $this->items = $items;
    }

    /**
     * @return EntityDescriptorItem[]
     */
    public function getItems() {
        return $this->items;
    }


    /**
     * @return string
     */
    public function toXmlString() {
        $entityID = htmlspecialchars($this->getEntityID());
        $result = <<<EOF
<?xml version="1.0"?>
<md:EntityDescriptor
    xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
    entityID="{$entityID}"
>
EOF;
        foreach ($this->getItems() as $item) {
            $result .= $item->toXmlString();
        }

        $result .= <<<EOF
</md:EntityDescriptor>
EOF;

        return $result;
    }

}