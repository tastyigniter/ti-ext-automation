<?php

namespace Igniter\Automation\Models;

use Exception;
use Igniter\Automation\AutomationException;
use Igniter\Automation\Classes\BaseAction;
use Igniter\Automation\Classes\BaseCondition;
use Igniter\Automation\Classes\BaseEvent;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Flame\Database\Traits\Validation;
use Throwable;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $description
 * @property string|null $event_class
 * @property array|null $config_data
 * @property bool $is_custom
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $event_description
 * @property-read mixed $event_name
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule applyClass($class)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule applyFilters(array $options = [])
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule applySorts(array $sorts = [])
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule applyStatus($status = true)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule dropdown(string $column, string $key = null)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule like(string $column, string $value, string $side = 'both', string $boolean = 'and')
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule listFrontEnd(array $options = [])
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule lists(string $column, string $key = null)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule newModelQuery()
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule newQuery()
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule orLike(string $column, string $value, string $side = 'both')
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule orSearch(string $term, string $columns = [], string $mode = 'all')
 * @method static array pluckDates(string $column, string $keyFormat = 'Y-m', string $valueFormat = 'F Y')
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule query()
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule search(string $term, string $columns = [], string $mode = 'all')
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereCode($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereConfigData($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereCreatedAt($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereDescription($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereEventClass($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereId($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereIsCustom($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereName($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereStatus($value)
 * @method static \Igniter\Flame\Database\Builder<static>|AutomationRule whereUpdatedAt($value)
 * @mixin \Igniter\Flame\Database\Model
 */
class AutomationRule extends Model
{
    use Purgeable;
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'igniter_automation_rules';

    public $timestamps = true;

    public $relation = [
        'hasMany' => [
            'conditions' => [RuleCondition::class, 'delete' => true],
            'actions' => [RuleAction::class, 'delete' => true],
            'logs' => [AutomationLog::class, 'delete' => true],
        ],
    ];

    protected $casts = [
        'config_data' => 'array',
    ];

    protected $purgeable = ['actions', 'conditions'];

    public $rules = [
        'name' => ['sometimes', 'required', 'string'],
        'code' => ['sometimes', 'required', 'alpha_dash', 'unique:igniter_automation_rules,code'],
        'event_class' => ['required'],
    ];

    /**
     * Kicks off this notification rule, fires the event to obtain its parameters,
     * checks the rule conditions evaluate as true, then spins over each action.
     */
    public function triggerRule()
    {
        try {
            if (!$this->actions || $this->actions->isEmpty()) {
                throw new AutomationException('No actions found for this rule');
            }

            $params = $this->getEventObject()->getEventParams();

            if ($this->conditions && !$this->checkConditions($params)) {
                return false;
            }

            $this->actions->each(function($action) use ($params) {
                $action->triggerAction($params);
            });
        } catch (Throwable|Exception $ex) {
            AutomationLog::createLog($this, $ex->getMessage(), false, $params ?? [], $ex);
        }
    }

    public function getEventClassOptions()
    {
        return array_map(function(BaseEvent $eventObj) {
            return $eventObj->getEventName().' - '.$eventObj->getEventDescription();
        }, BaseEvent::findEventObjects());
    }

    public function getActionOptions()
    {
        return array_map(function(BaseAction $actionObj) {
            return $actionObj->getActionName();
        }, BaseAction::findActions());
    }

    public function getConditionOptions()
    {
        return array_map(function(BaseCondition $conditionObj) {
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

    public function scopeApplyStatus($query, $status = true)
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
     * @return bool
     */
    public function applyEventClass($class = null)
    {
        $class ??= $this->event_class;

        if ($class && !$this->isClassExtendedWith($class)) {
            $this->extendClassWith($class);
        }

        $this->event_class = $class;

        return true;
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
            if ($isCustom || !$code) {
                continue;
            }

            if (!array_key_exists($code, $presets)) {
                self::whereCode($code)->delete();
            }
        }

        // Create new rules
        foreach ($newRules as $code => $preset) {
            self::createFromPreset($code, $preset);
        }
    }

    public static function createFromPreset($code, $preset)
    {
        $actions = array_get($preset, 'actions');
        if (!$actions || !is_array($actions)) {
            return;
        }

        $automation = new self;
        $automation->status = false;
        $automation->is_custom = false;
        $automation->code = $code;
        $automation->name = array_get($preset, 'name');
        $automation->event_class = array_get($preset, 'event');
        $automation->save();

        foreach ($actions as $actionClass => $config) {
            $ruleAction = new RuleAction;
            $ruleAction->options = $config;
            $ruleAction->class_name = $actionClass;
            $ruleAction->automation_rule_id = $automation->getKey();
            $ruleAction->save();
        }

        $conditions = array_get($preset, 'conditions', []);
        foreach ($conditions as $conditionClass => $config) {
            $ruleCondition = new RuleCondition;
            $ruleCondition->options = $config;
            $ruleCondition->class_name = $conditionClass;
            $ruleCondition->automation_rule_id = $automation->getKey();
            $ruleCondition->save();
        }

        return $automation;
    }

    protected function checkConditions($params)
    {
        $conditions = $this->conditions;
        if ($conditions->isEmpty()) {
            return true;
        }

        $validConditions = $conditions->sortBy('priority')->filter(function(RuleCondition $condition) use ($params) {
            return $condition->getConditionObject()->isTrue($params);
        })->values();

        $matchType = $this->config_data['condition_match_type'] ?? 'all';

        if ($matchType == 'all') {
            return $conditions->count() === $validConditions->count();
        }

        return $validConditions->isNotEmpty();
    }
}
