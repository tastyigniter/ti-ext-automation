<?php

namespace Igniter\Automation\Tests\Classes;

use Igniter\Automation\Classes\BaseAction;
use Igniter\System\Classes\ExtensionManager;
use Mockery;

it('defines a name and description', function() {
    $action = new class extends BaseAction
    {
    };

    expect($action->actionDetails())->toHaveKeys(['name', 'description']);
});

it('defines form fields', function() {
    $action = new class extends BaseAction
    {
    };

    expect($action->defineFormFields())->toBeArray();
});

it('defines validation rules', function() {
    $action = new class extends BaseAction
    {
    };

    expect($action->defineValidationRules())->toBeArray();
});

it('returns true when fieldConfig is not empty', function() {
    $action = new class extends BaseAction
    {
        public function defineFormFields()
        {
            return [
                'fields' => [
                    'field1' => [
                        'label' => 'Field 1',
                        'type' => 'text',
                    ],
                ],
            ];
        }
    };

    $result = $action->hasFieldConfig();

    expect($result)->toBeTrue();
});

it('returns fieldConfig when it is set', function() {
    $action = new class extends BaseAction
    {
        public function defineFormFields()
        {
            return [
                'fields' => [
                    'field1' => [
                        'label' => 'Field 1',
                        'type' => 'text',
                    ],
                ],
            ];
        }
    };

    $result = $action->getFieldConfig();

    expect($result['fields'])->toHaveKey('field1');
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

it('skips invalid action classes and continues processing', function() {
    $extensionManager = Mockery::mock(ExtensionManager::class);
    $extensionManager->shouldReceive('getRegistrationMethodValues')->with('registerAutomationRules')->andReturn([
        [
            'actions' => [
                'NonExistent\Class\Name',
            ],
        ],
    ])->once();
    app()->instance(ExtensionManager::class, $extensionManager);

    BaseAction::findActions();
});
