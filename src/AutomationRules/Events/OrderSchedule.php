<?php

namespace Igniter\Automation\AutomationRules\Events;

use Igniter\Automation\Classes\BaseEvent;
use Igniter\Cart\Models\Order;

class OrderSchedule extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Order Hourly Schedule',
            'description' => 'Performed on all recent orders once every hour',
            'group' => 'order',
        ];
    }

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $params = [];
        $order = array_get($args, 0);
        if ($order instanceof Order) {
            $params = $order->mailGetData();
        }

        return $params;
    }
}
