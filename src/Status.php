<?php

namespace conceptho\state;

use yii\base\Behavior;

class Status extends Behavior
{

    public $stateBehavior;
    public $label = "";
    public $id = "";

    public function __construct()
    {
        if (empty($this->id))
            $this->id = strtolower((new \ReflectionClass($this))->getShortName());
        if (empty($this->label))
            $this->id = (new \ReflectionClass($this))->getShortName();
        parent::__construct();

    }

    public function getAvailableStatus()
    {
        return $this->stateBehavior->availableStatus;
    }

    public function getAvailableStatusObjects()
    {
        return $this->stateBehavior->availableStatusObjects;
    }

    public function changeTo($id, $data = [], $force = false)
    {
        return $this->stateBehavior->changeTo($id, $data, $force);
    }

    public function canChangeTo($id)
    {
        return true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function onExit($id, $event)
    {
        return true;
    }

    public function onEntry($id, $event)
    {
        return true;
    }

    public function __toString()
    {
        return $this->id;
    }

}
