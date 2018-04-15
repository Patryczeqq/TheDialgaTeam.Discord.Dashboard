<?php

namespace Home\Model\TheDialgaTeam\Discord\Table\Model;

use Zend\Hydrator\ClassMethods;
use Zend\Json\Json;

abstract class BaseTable
{
    /**
     * Table row id.
     * @var int
     */
    private $id;

    /**
     * Get table row id.
     * @return int Table row id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return (new ClassMethods())->extract($this);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return Json::encode($this);
    }
}