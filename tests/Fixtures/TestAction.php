<?php

namespace Igniter\Automation\Tests\Fixtures;

use Igniter\Automation\Classes\BaseAction;

class TestAction extends BaseAction
{
    public function actionDetails()
    {
        return [
            'name' => 'Test Action',
            'description' => 'Test Action description',
        ];
    }

    public function triggerAction($params)
    {
        expect($params)->toHaveKey('param1')
            ->and($params['param1'])->toBe('value1');
    }
}
