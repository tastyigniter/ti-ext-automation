<?php

declare(strict_types=1);

namespace Igniter\Automation\AutomationRules\Events;

use Igniter\Automation\Classes\BaseEvent;
use Igniter\Reservation\Models\Reservation;

class ReservationSchedule extends BaseEvent
{
    public function eventDetails(): array
    {
        return [
            'name' => 'Reservation Hourly Schedule',
            'description' => 'Performed on all recent reservations once every hour',
            'group' => 'order',
        ];
    }

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $params = [];
        $reservation = array_get($args, 0);
        if ($reservation instanceof Reservation) {
            $params = $reservation->mailGetData();
        }

        return $params;
    }
}
