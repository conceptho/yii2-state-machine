<?php

namespace app\components\state;

use yii\base\Event as YiiEvent;

class Event extends YiiEvent
{
    public function getData()
    {
        return $this->params;
    }
}