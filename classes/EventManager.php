<?php

namespace Igniter\Automation\Classes;

use App;
use Event;
use Igniter\Automation\Jobs\EventParams;
use Igniter\Automation\Models\AutomationRule;
use Igniter\Flame\Traits\Singleton;
use Queue;

class EventManager
{
    use Singleton;

    /**
     * @var array Cache of registration callbacks.
     */
    protected $callbacks = [];

    /**
     * @var bool Internal marker to see if callbacks are run.
     */
    protected $registered = FALSE;

    /**
     * @var array List of registered global params in the system
     */
    protected $registeredGlobalParams;

    public static function bindRules()
    {
        foreach (BaseEvent::findEvents() as $eventClass => [$eventCode, $eventObj]) {
            self::bindEvent($eventCode, $eventClass);
        }
    }

    /**
     * @param array $events
     */
    public static function bindEvents(array $events)
    {
        foreach ($events as $event => $class) {
            self::bindEvent($event, $class);
        }
    }

    /**
     * @param $eventCode
     * @param $eventClass
     */
    public static function bindEvent($eventCode, $eventClass)
    {
        Event::listen($eventCode, function () use ($eventCode, $eventClass) {
            if (!method_exists($eventClass, 'makeParamsFromEvent'))
                return;

            $params = $eventClass::makeParamsFromEvent(func_get_args(), $eventCode);
            self::instance()->queueEvent($eventClass, $params);
        });
    }

    public function queueEvent($eventClass, array $params)
    {
        $params += $this->getContextParams();

        // If available, push to queue
        Queue::push(new EventParams($eventClass, $params));
    }

    public function fireEvent($eventClass, array $params)
    {
        $models = AutomationRule::listRulesForEvent($eventClass);

        $models->each(function ($model) use ($params) {
            $model->setEventParams($params);
            $model->triggerRule();
        });
    }

    /**
     * Registers a callback function that defines context variables.
     * The callback function should register context variables by calling the manager's
     * `registerGlobalParams` method. The manager instance is passed to the callback
     * function as an argument. Usage:
     *
     *     Notifier::registerCallback(function($manager){
     *         $manager->registerGlobalParams([...]);
     *     });
     *
     * @param callable $callback A callable function.
     */
    public function registerCallback(callable $callback)
    {
        $this->callbacks[] = $callback;
    }

    public function registerGlobalParams(array $params)
    {
        if (!$this->registeredGlobalParams) {
            $this->registeredGlobalParams = [];
        }

        $this->registeredGlobalParams = $params + $this->registeredGlobalParams;
    }

    public function getContextParams()
    {
        $this->processCallbacks();

        $globals = $this->registeredGlobalParams ?: [];

        return [
                'isAdmin' => App::runningInAdmin() ? 1 : 0,
                'isConsole' => App::runningInConsole() ? 1 : 0,
                'appLocale' => App::getLocale(),
            ] + $globals;
    }

    /**
     * Helper to process callbacks once and once only.
     * @return void
     */
    protected function processCallbacks()
    {
        if ($this->registered) {
            return;
        }

        foreach ($this->callbacks as $callback) {
            $callback($this);
        }

        $this->registered = TRUE;
    }
}