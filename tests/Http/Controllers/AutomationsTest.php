<?php

namespace Igniter\Automation\Tests\Http\Controllers;

use Igniter\Admin\Helpers\AdminHelper;
use Igniter\Automation\AutomationRules\Actions\AssignToGroup;
use Igniter\Automation\Classes\BaseCondition;
use Igniter\Automation\Models\AutomationRule;
use Igniter\User\Models\User;

it('loads index page without errors', function() {
    $this->actingAs(User::factory()->superUser()->create(), 'igniter-admin')
        ->get(route('igniter.automation.automations'))
        ->assertOk();
});

it('loads edit page without errors', function() {
    AutomationRule::syncAll();

    $this->actingAs(User::factory()->superUser()->create(), 'igniter-admin')
        ->get(route('igniter.automation.automations', ['slug' => 'edit/'.AutomationRule::first()->getKey()]))
        ->assertOk();
});

it('loads create page without errors', function() {
    $this->actingAs(User::factory()->superUser()->create(), 'igniter-admin')
        ->get(route('igniter.automation.automations', ['slug' => 'create']))
        ->assertOk();
});

it('updates record without errors', function() {
    AutomationRule::syncAll();

    $url = route('igniter.automation.automations', ['slug' => 'edit/'.AutomationRule::first()->getKey()]);
    $this->actingAs(User::factory()->superUser()->create(), 'igniter-admin')
        ->post($url, [
            'Automation' => [
                'event_class' => 'SomeClass',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ])
        ->assertJsonPath(AdminHelper::HANDLER_REDIRECT, $url);
});

it('loads connector form field for', function($field, $className, $handler) {
    AutomationRule::syncAll();

    $url = route('igniter.automation.automations', ['slug' => 'edit/'.AutomationRule::first()->getKey()]);
    $response = $this->actingAs(User::factory()->superUser()->create(), 'igniter-admin')
        ->post($url, [
            'AutomationRule' => [
                $field => $className,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => $handler,
        ]);

    expect($response->json())->toBeArray()->toHaveKey('#notification');
})->with([
    ['_action', AssignToGroup::class, 'onLoadCreateActionForm'],
    ['_condition', BaseCondition::class, 'onLoadCreateConditionForm'],
]);

it('sets is_custom and status on form before create', function() {
    AutomationRule::syncAll();

    $url = route('igniter.automation.automations', ['slug' => 'create']);
    $this->actingAs(User::factory()->superUser()->create(), 'igniter-admin')
        ->post($url, [
            'AutomationRule' => [
                'event_class' => 'SomeClass',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ])
        ->assertOk();

    $automation = AutomationRule::firstWhere('event_class', 'SomeClass');

    expect((bool)$automation->is_custom)->toBeTrue()
        ->and((bool)$automation->status)->toBeTrue();
});
