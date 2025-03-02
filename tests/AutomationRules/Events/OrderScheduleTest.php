<?php

namespace Igniter\Automation\Tests\AutomationRules\Events;

use Igniter\Automation\AutomationRules\Events\OrderSchedule;
use Igniter\Cart\Models\Order;

it('has a name and description', function() {
    $event = new OrderSchedule();
    expect($event->eventDetails())->toHaveKeys(['name', 'description']);
});

it('returns order data from event', function() {
    $order = Order::factory()->create([
        'order_type' => 'delivery',
        'order_total' => 10.0,
    ]);

    $params = OrderSchedule::makeParamsFromEvent([$order]);
    expect($params)->toHaveKeys(['order', 'order_id', 'order_type', 'order_total'])
        ->and($params['order'])->toBeInstanceOf(Order::class)
        ->and($params['order_type'])->toBe('Delivery')
        ->and($params['order_total'])->toBe(10.0);
});

it('returns empty array if order is not provided', function() {
    $params = OrderSchedule::makeParamsFromEvent([]);

    expect($params)->toBeArray()
        ->and($params)->toBeEmpty();
});

it('returns empty array if order is not an instance of Order', function() {
    $params = OrderSchedule::makeParamsFromEvent([new \stdClass()]);

    expect($params)->toBeArray()
        ->and($params)->toBeEmpty();
});

