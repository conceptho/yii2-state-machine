<?php

namespace profissa\state;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;
use yii\base\Exception;

class Machine extends Behavior
{

    public $attr = 'status';
    public $initial = '';
    public $model_label = '';
    public $namespace = '';
    public $transitions = array();
    private $options = array();
    private $temporary = null;


    public function events()
    {
        if($this->owner->scenario == "search")
            return [];

        return [
            ActiveRecord::EVENT_INIT => [$this, 'onAfterFind'],
            ActiveRecord::EVENT_AFTER_FIND => [$this, 'onAfterFind'],
            ActiveRecord::EVENT_BEFORE_VALIDATE => [$this, 'convertToString'],
            ActiveRecord::EVENT_AFTER_VALIDATE => [$this, 'restoreObject'],
        ];
    }

    public function convertToString($event)
    {
        $this->temporary = $this->owner->{$this->attr};
        $this->owner->{$this->attr} = $this->temporary."";
        return true;
    }

    public function restoreObject($event)
    {
        $this->owner->{$this->attr} = $this->temporary;
        return true;
    }

    public function onAfterFind($event)
    {
        $this->setStatus($this->owner->{$this->attr});
    }

    public function attach($owner)
    {
        parent::attach($owner);

        if($this->owner->scenario == "search")
            return true;

        if (empty($this->initial))
            throw new Exception("It's required to set an initial state");

        if (empty($this->model_label))
            throw new Exception("It's required to set a model label");

        if (empty($this->namespace))
            $this->namespace = strtolower(get_class($this->owner)).'\\status';


        $this->options = array_keys($this->transitions);

        foreach ($this->transitions as $k => $t)
        {
            if (!is_array($t))
                $this->transitions[$k] = explode(",", $t);
            else
                $this->transitions[$k] = $t;
        }

        $this->getStatus();
    }

    private function getStatus()
    {
        if ($this->owner->{$this->attr})
            return $this->owner->{$this->attr};
        else
            return $this->setStatus($this->getStatusId());
    }

    public function getClassName($id)
    {
        return $this->namespace."\\".str_replace(" ", "", ucwords(str_replace(array("_", "-"), array(" "," "), $id)));
    }

    public function getStatusObject($id)
    {
        $className = $this->getClassName($id);
        return new $className;
    }

    private function setStatus($id)
    {
        if (!in_array($id, $this->options))
            throw new Exception('Status not avaiable ('.$id.')');

        $this->owner->{$this->attr} = $this->getStatusObject($id);
        $this->owner->{$this->attr}->stateBehavior = $this;
        $this->owner->detachBehavior($this->attr);
        $this->owner->attachBehavior($this->attr, $this->owner->{$this->attr});

        return $this->owner->{$this->attr};
    }

    private function getStatusId()
    {
        if ($this->owner->{$this->attr} instanceof Status)
            return $this->owner->{$this->attr}->id;
        else
            return $this->initial;
    }

    public function canChangeTo($id)
    {
        return in_array($id, $this->options) &&
        in_array($id, $this->transitions[$this->getStatusId()]) && $this->owner->{$this->attr}->canChangeTo($id);
    }

    public function allowedStatusChanges()
    {
        return $this->transitions[$this->getStatusId()];
    }

    public function getAvailableStatus()
    {
        $availableStatus = [];
        foreach($this->transitions as $status=>$transitions)
        {
            $availableStatus[$status] = $this->getStatusObject($status)->label;
        }

        return $availableStatus;
    }

    public function getAvailableStatusObjects()
    {
        $availableStatus = [];
        foreach($this->transitions as $status=>$transitions)
        {
            $availableStatus[$status] = $this->getStatusObject($status);
        }

        return $availableStatus;
    }

    public function changeTo($id, $data=array(), $force = false)
    {
        $oldStatusId = $this->getStatusId();


        if (!$this->canChangeTo($id) && $force === false)
            throw new BadRequestHttpException('Não é possível alterar '. $this->model_label .' do estado '.$this->getStatus()->label.' para o estado '.$this->getStatusObject($id)->label);

        $event = new Event(['data' => $data]);
        if($this->owner->{$this->attr}->onExit($id, $event))
        {
            $this->setStatus($id);
            if(!$this->owner->{$this->attr}->onEntry($id, $event))
            {
                $this->setStatus($oldStatusId);
                return false;
            }

            return true;
        }

    }

}
