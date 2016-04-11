<?php

namespace app\components\state;

use yii\base\Behavior;

class Status extends Behavior {

    public $stateBehavior;
    public $label = "Não definido";
    public $id = "undefined";

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