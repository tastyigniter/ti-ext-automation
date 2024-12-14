<?php

namespace Igniter\Automation\Tests\Fixtures;

use Igniter\Automation\Classes\BaseCondition;

class TestCondition extends BaseCondition
{
    public function conditionDetails()
    {
        return [
            'name' => 'Test Action',
            'description' => 'Test Action description',
        ];
    }

    public function isTrue(&$params)
    {
        return false;
    }
}
