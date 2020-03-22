# Yii2 State Machine

This package enable state machine usage into attributes of a Model (Active Record).

[![Latest Stable Version](https://poser.pugx.org/conceptho/yii2-state-machine/v/stable)](https://packagist.org/packages/conceptho/yii2-state-machine)
[![Total Downloads](https://poser.pugx.org/conceptho/yii2-state-machine/downloads.png)](https://packagist.org/packages/conceptho/yii2-state-machine)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require conceptho/yii2-state-machine
```

or add

```json
"conceptho/yii2-state-machine": "dev-master"
```

to the `require` section of your composer.json.


Usage
------

Model definition:
```php
/// Model

namespace app\models;
 
class User extends \yii\db\ActiveRecord {
    
    public function modelLabel() {
        return 'User';
    }

    public function behaviors() {
        return \yii\helpers\ArrayHelper::merge(parent::behvaiors(), [
            [
                'class' => conceptho\state\Machine::class,
                'initial' => 'active', /// Initial status
                'attr' => 'status', /// Attribute that will use this state machine
                'namespace' => 'app\models\status\user', /// Namespace for the Status class definitions
                'model_label' => $this->modelLabel(),
                'transitions' => [
                    'active' => ['inactive', 'disabled'],
                    'inactive' => ['active', 'disabled'],
                    'disabled' => ['inactive']
                ]
            ]   
        ]);
    }   
} 
```

Status definitions:
```php
/// Active status
namespace app\models\status\user;

use conceptho\state\Status;

class Active extends Status {
    public const ID = 'active';
    public $label = 'Active';
    public $labelColor = 'primary';
    
    public function onExit($id, $event)
    {
        /// event triggered when the status is changed from Active to another status
        return true;
    }
    
    public function onEntry($id, $event)
    {
        /// event triggered when the status is changed from another status to Active
        return true;
    }

}
```

```php
/// Inactive Status
namespace app\models\status\user;

use conceptho\state\Status;

class Inactive extends Status {
    public const ID = 'inactive';
    public $label = 'Inactive';
    public $labelColor = 'danger';

    public function onExit($id, $event)
    {
        /// event triggered when the status is changed from Inactive to another status
        return true;
    }
    
    public function onEntry($id, $event)
    {
        /// event triggered when the status is changed from another status to Inactive
        return true;
    }

}
```

```php
/// Disabled Status
namespace app\models\status\user;

use conceptho\state\status;

class Disabled extends Status {
    public const ID = 'disabled';
    public $label = 'Disabled';
    public $labelColor = 'muted';

    public function onExit($id, $event)
    {
        /// event triggered when the status is changed from Disabled to another status
        return true;
    }
    
    public function onEntry($id, $event)
    {
        /// event triggered when the status is changed from another status to Disabled
        return true;
    }

}
```

### Example:
```php

$user = new User();
/// Returns the current status: new Active()
$user->status;
/// Returns the allowed status IDs that can be changed to in this case: ['inactive', 'disabled']
$user->allowedStatusChanges(); 

/// Returns a boolean value in this case: true
$user->canChangeTo('inactive');
/// in this case: false. Since this status is not defined in the transitions key values.
$user->canChangeTo('unknown');
/// Returns all the defined Status in the Model, in this case: 
/// ['active' => new Active(), 'inactive' => new Inactive(), 'disabled' => new Disabled()] 
$user->availableStatus();

/// Change from Active to Inactive triggering the events onEntry of inactive and onExit of Active
$user->changeTo('inactive');

/// Returns the current status: new Inactive()
$user->status;

/// Change from Inactive to Disabled triggering the events onExit of inactive and onEntry of Disabled
$user->changeTo('disabled');

/// Throws a error since disabled cant be changed to active.
$user->changeTo('active');
```


