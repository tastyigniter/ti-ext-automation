<?php

namespace Igniter\Automation\Tests;

use Igniter\Automation\AutomationRules\Actions\AssignToGroup;
use Igniter\Automation\AutomationRules\Actions\SendMailTemplate;
use Igniter\Automation\AutomationRules\Events\OrderSchedule;
use Igniter\Automation\AutomationRules\Events\ReservationSchedule;
use Igniter\Automation\Classes\BaseAction;
use Igniter\Automation\Classes\BaseEvent;

it('loads registered automation events', function() {
    $events = BaseEvent::findEvents();

    expect($events)->toHaveKeys([
        OrderSchedule::class,
        ReservationSchedule::class,
    ]);
});

it('loads registered automation actions', function() {
    $actions = BaseAction::findActions();

    expect($actions)->toHaveKeys([
        AssignToGroup::class,
        SendMailTemplate::class,
    ]);
});

