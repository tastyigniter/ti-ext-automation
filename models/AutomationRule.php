<?php

namespace Igniter\Automation\Models;

use ApplicationException;
use Exception;
use Igniter\Automation\Classes\BaseAction;
use Igniter\Automation\Classes\BaseCondition;
use Igniter\Automation\Classes\BaseEvent;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Facades\Log;
use Model;

class AutomationRule extends Model
{
    use Validation;
    use Purgeable;

    /**
     * @var string The database table name
     */
    protected $table = 'igniter_automation_rules';

    public $timestamps = TRUE;

    public $relation = [
        'hasMany' => [
            'conditions' => [RuleCondition::class, 'delete' => TRUE],
            'actions' => [RuleAction::class, 'delete' => TRUE],
        ],
    ];

    public $casts = [
        'config_data' => 'array',
    ];

    protected $purgeable = ['actions', 'conditions'];

    public $rules = [
        'name' => 'sometimes|required|string',
        'code' => 'sometimes|required|alpha_dash|unique:igniter_automation_rules,code',
        'event_class' => 'required',
    ];

    /**
     * Kicks off this notification rule, fires the event to obtain its parameters,
     * checks the rule conditions evaluate as true, then spins over each action.
     */
    public function triggerRule()
    {
        if (!$conditions = $this->conditions)
            throw new ApplicationException('Event rule is missing a condition');

        try {
            $params = $this->getEventObject()->getEventParams();
            if (!$this->checkConditions($params))
                return FALSE;

            $this->actions->each(function ($action) use ($params) {
                $action->triggerAction($params);
            });
        }
        catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function getEventClassOptions()
    {
        return array_map(function (BaseEvent $eventObj) {
            return $eventObj->getEventName().' - '.$eventObj->getEventDescription();
        }, BaseEvent::findEventObjects());
    }

    public function getActionOptions()
    {
        return array_map(function (BaseAction $actionObj) {
            return $actionObj->getActionName();
        }, BaseAction::findActions());
    }

    public function getConditionOptions()
    {
        return array_map(function (BaseCondition $conditionObj) {
            return $conditionObj->getConditionName();
        }, BaseCondition::findConditions());
    }

    //
    // Attributes
    //

    public function getEventNameAttribute()
    {
        return $this->getEventObject()->getEventName();
    }

    public function getEventDescriptionAttribute()
    {
        return $this->getEventObject()->getEventDescription();
    }

    //
    // Events
    //

    protected function afterFetch()
    {
        $this->applyEventClass();
    }

    //
    // Scope
    //

    public function scopeApplyStatus($query, $status = TRUE)
    {
        return $query->where('status', $status);
    }

    public function scopeApplyClass($query, $class)
    {
        if (!is_string($class)) {
            $class = get_class($class);
        }

        return $query->where('event_class', $class);
    }

    //
    // Manager
    //

    /**
     * Extends this class with the event class
     * @param string $class Class name
     * @return boolean
     */
    public function applyEventClass($class = null)
    {
        if (!$class)
            $class = $this->event_class;

        if (!$class)
            return FALSE;

        if (!$this->isClassExtendedWith($class)) {
            $this->extendClassWith($class);
        }

        $this->event_class = $class;

        return TRUE;
    }

    /**
     * Returns the event class extension object.
     * @return \Igniter\Automation\Classes\BaseEvent
     */
    public function getEventObject()
    {
        $this->applyEventClass();

        return $this->asExtension($this->getEventClass());
    }

    public function getEventClass()
    {
        return $this->event_class;
    }

    /**
     * Returns an array of rule codes and descriptions.
     * @param $eventClass
     * @return \Illuminate\Support\Collection
     */
    public static function listRulesForEvent($eventClass)
    {
        return self::applyStatus()->applyClass($eventClass)->get();
    }

    /**
     * Synchronise all file-based rules to the database.
     * @return void
     */
    public static function syncAll()
    {
        $presets = BaseEvent::findEventPresets();
        $dbRules = self::pluck('is_custom', 'code')->toArray();
        $newRules = array_diff_key($presets, $dbRules);

        // Clean up non-customized rules
        foreach ($dbRules as $code => $isCustom) {
            if ($isCustom OR !$code)
                continue;

            if (!array_key_exists($code, $presets))
                self::whereName($code)->delete();
        }

        // Create new rules
        foreach ($newRules as $code => $preset) {
            self::createFromPreset($code, $preset);
        }
    }

    public static function createFromPreset($code, $preset)
    {
        $actions = array_get($preset, 'actions');
        if (!$actions OR !is_array($actions))
            return;

        $automation = new self;
        $automation->status = 0;
        $automation->is_custom = 0;
        $automation->code = $code;
        $automation->name = array_get($preset, 'name');
        $automation->event_class = array_get($preset, 'event');
        $automation->save();

        foreach ($actions as $actionClass => $config) {
            $ruleAction = new RuleAction;
            $ruleAction->fill($config);
            $ruleAction->class_name = $actionClass;
            $ruleAction->automation_rule_id = $automation->getKey();
            $ruleAction->save();
        }

        return $automation;
    }

    protected function checkConditions($params)
    {
        $conditions = $this->conditions;
        if ($conditions->isEmpty())
            return TRUE;

        $validConditions = $conditions->sortBy('priority')->filter(function (RuleCondition $condition) use ($params) {
            return $condition->getConditionObject()->isTrue($params);
        })->values();

        $matchType = $this->config_data['condition_match_type'] ?? 'all';

        if ($matchType == 'all' AND $validConditions->isEmpty())
            return FALSE;

        return $validConditions->isNotEmpty();
    }
}