<?php

namespace Igniter\Automation\Tests\Fixtures;

use Igniter\Automation\Classes\BaseCondition;

class TestCondition extends BaseCondition
{
    public function conditionDetails(): array
    {
        return [
            'name' => 'Test Action',
            'description' => 'Test Action description',
        ];
    }

    public function isTrue(&$params): bool
    {
        return false;
    }
}
