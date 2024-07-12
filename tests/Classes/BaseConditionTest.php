<?php

namespace Igniter\Automation\Tests\Classes;

use Igniter\Automation\Classes\BaseCondition;

it('defines a name and description', function() {
    $condition = new class extends BaseCondition {};

    expect($condition->conditionDetails())->toHaveKeys(['name', 'description']);
});

it('checks condition', function() {
    $condition = new class extends BaseCondition
    {
        public function isTrue(&$params)
        {
            return true;
        }
    };

    $params = [];

    expect($condition->isTrue($params))->toBeTrue();
});
