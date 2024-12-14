<?php

namespace Igniter\Automation\Tests\Classes;

use Igniter\Automation\Classes\BaseCondition;
use Igniter\Flame\Database\Model;
use Igniter\System\Classes\ExtensionManager;
use Mockery;

it('initialises config data', function() {
    $model = Mockery::mock(Model::class);
    $condition = new class($model) extends BaseCondition
    {
        public function getModel()
        {
            return $this->model;
        }
    };

    expect($condition->getModel())->toBe($model);
});

it('defines a name and description', function() {
    $condition = new class extends BaseCondition
    {
    };

    expect($condition->conditionDetails())->toHaveKeys(['name', 'description']);
});

it('checks condition', function() {
    $condition = new class extends BaseCondition
    {
    };

    $params = [];

    expect($condition->isTrue($params))->toBeFalse();
});

it('skips invalid condition classes and continues processing', function() {
    $extensionManager = Mockery::mock(ExtensionManager::class);
    $extensionManager->shouldReceive('getRegistrationMethodValues')->with('registerAutomationRules')->andReturn([
        [
            'conditions' => [
                'NonExistent\Class\Name',
            ],
        ],
    ])->once();
    app()->instance(ExtensionManager::class, $extensionManager);

    BaseCondition::findConditions();
});
