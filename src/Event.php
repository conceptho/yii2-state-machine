<?php

namespace profissa\state;

use yii\base\Event as YiiEvent;

class Event extends YiiEvent
{
    public function getData()
    {
        return $this->data;
    }
}
