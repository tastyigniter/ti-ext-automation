<?php

namespace Igniter\Automation\Tests\Classes;

use Igniter\Automation\Classes\BaseAction;

it('defines a name and description', function() {
    $action = new class extends BaseAction {};

    expect($action->actionDetails())->toHaveKeys(['name', 'description']);
});

it('defines form fields', function() {
    $action = new class extends BaseAction {};

    expect($action->defineFormFields())->toBeArray();
});

it('defines validation rules', function() {
    $action = new class extends BaseAction {};

    expect($action->defineValidationRules())->toBeArray();
});

it('throws an exception if triggerAction method is not implemented', function() {
    $action = new BaseAction;

    $action->triggerAction([]);
})->throws('Method Igniter\Automation\Classes\BaseAction::triggerAction() is not implemented.');

it('triggers an action', function() {
    $action = new class extends BaseAction
    {
        public $actionRan = false;

        public function triggerAction($params)
        {
            $this->actionRan = true;
        }
    };

    $action->triggerAction([]);

    expect($action->actionRan)->toBeTrue();
});
