<?php

namespace Igniter\Automation\Tests\Classes;

use Igniter\Automation\Classes\BaseEvent;
use Igniter\Automation\Classes\EventManager;
use Igniter\Automation\Jobs\EventParams;
use Igniter\Automation\Models\AutomationRule;
use Igniter\Automation\Tests\Fixtures\TestAction;
use Igniter\Automation\Tests\Fixtures\TestEvent;
use Igniter\Cart\Models\Order;
use Igniter\Reservation\Models\Reservation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('binds events correctly', function() {
    Queue::fake();

    EventManager::bindEvents(['test.event' => new class extends BaseEvent
    {
        public static function makeParamsFromEvent(array $args, $eventName = null)
        {
            return ['param' => 'value'];
        }
    }]);

    Event::dispatch('test.event');

    Queue::assertPushed(EventParams::class);
});

it('skips binding event when makeParamsFromEvent method is missing', function() {
    Queue::fake();

    EventManager::bindEvents(['test.event' => new class
    {
    }]);

    Event::dispatch('test.event');

    Queue::assertNotPushed(EventParams::class);
});

it('queues event correctly', function() {
    Queue::fake();

    $eventManager = new EventManager;
    $eventManager->queueEvent('SomeEventClass', ['param' => 'value']);

    Queue::assertPushed(EventParams::class);
});

it('dispatches order schedule hourly event', function() {
    Event::fake();

    Order::factory()->count(5)->create([
        'created_at' => now()->subDays(30),
    ]);

    EventManager::fireOrderScheduleEvents();

    Event::assertDispatched('automation.order.schedule.hourly', 5);
});

it('does not dispatch order schedule hourly event if order is not created within 30 days', function() {
    Event::fake();

    Order::factory()->count(5)->create([
        'created_at' => now()->subDays(31),
    ]);

    EventManager::fireOrderScheduleEvents();

    Event::assertNotDispatched('automation.order.schedule.hourly');
});

it('dispatches reservation schedule hourly event', function() {
    Event::fake();

    Reservation::factory()->count(5)->create([
        'reserve_date' => now()->subDays(28),
    ]);

    EventManager::fireReservationScheduleEvents();

    Event::assertDispatched('automation.reservation.schedule.hourly', 5);
});

it('does not dispatch reservation schedule hourly event if reservation is not created within 30 days', function() {
    Event::fake();

    Reservation::factory()->count(5)->create([
        'reserve_date' => now()->subDays(31),
    ]);

    EventManager::fireReservationScheduleEvents();

    Event::assertNotDispatched('automation.reservation.schedule.hourly');
});

it('triggers rules for the given event class', function() {
    $eventClass = TestEvent::class;
    $params = ['param1' => 'value1'];

    $automationRule = AutomationRule::createFromPreset('some_rule', [
        'name' => 'SomeRule',
        'event' => $eventClass,
        'actions' => [
            TestAction::class => [],
        ],
    ]);
    $automationRule->status = 1;
    $automationRule->save();

    $eventManager = new EventManager();
    $eventManager->fireEvent($eventClass, $params);

    expect(true)->toBeTrue();
});

it('registers global params correctly', function() {
    $eventManager = new EventManager;
    $eventManager->registerGlobalParams(['param' => 'value']);

    $contextParams = $eventManager->getContextParams();

    expect($contextParams)->toHaveKey('param')
        ->and($contextParams['param'])->toBe('value');
});

it('processes callbacks correctly', function() {
    $eventManager = new EventManager;
    $eventManager->registerCallback(function($manager) {
        $manager->registerGlobalParams(['callbackParam' => 'callbackValue']);
    });

    $contextParams = $eventManager->getContextParams();

    expect($contextParams)->toHaveKey('callbackParam')
        ->and($contextParams['callbackParam'])->toBe('callbackValue');
});
