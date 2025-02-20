<?php

declare(strict_types=1);

namespace Igniter\Automation\Tests\Fixtures;

use Igniter\Automation\Classes\BaseAction;

class TestAction extends BaseAction
{
    public function actionDetails(): array
    {
        return [
            'name' => 'Test Action',
            'description' => 'Test Action description',
        ];
    }

    public function triggerAction($params): void
    {
        expect($params)->toHaveKey('param1')
            ->and($params['param1'])->toBe('value1');
    }
}
