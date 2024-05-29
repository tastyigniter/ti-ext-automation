<?php

namespace Igniter\Automation\Tests\AutomationRules\Events;

use Igniter\Automation\AutomationRules\Events\ReservationSchedule;
use Igniter\Reservation\Models\Reservation;

it('has a name and description', function() {
    $event = new ReservationSchedule();
    expect($event->eventDetails())->toHaveKeys(['name', 'description']);
});

it('returns reservation data from event', function() {
    $reservation = Reservation::factory()->create([
        'guest_num' => 10,
    ]);

    $params = ReservationSchedule::makeParamsFromEvent([$reservation]);
    expect($params)->toHaveKeys(['reservation', 'reservation_id', 'guest_num', 'reserve_date'])
        ->and($params['reservation'])->toBeInstanceOf(Reservation::class)
        ->and($params['guest_num'])->toBe(10);
});

it('returns empty array if order is not provided', function() {
    $params = ReservationSchedule::makeParamsFromEvent([]);

    expect($params)->toBeArray()
        ->and($params)->toBeEmpty();
});

it('returns empty array if order is not an instance of Order', function() {
    $params = ReservationSchedule::makeParamsFromEvent([new \stdClass()]);

    expect($params)->toBeArray()
        ->and($params)->toBeEmpty();
});

