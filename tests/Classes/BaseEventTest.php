<?php

namespace Igniter\Automation\Tests\Classes;

use Igniter\Automation\Classes\BaseEvent;

it('name and description', function() {
    $event = new class extends BaseEvent
    {
    };

    expect($event->eventDetails())->toHaveKeys(['name', 'description', 'group']);
});
