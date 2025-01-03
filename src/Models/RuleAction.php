<?php

namespace Igniter\Automation\Models;

use Igniter\Automation\AutomationException;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Validation;

/**
 *
 *
 * @property int $id
 * @property int|null $automation_rule_id
 * @property string $class_name
 * @property array $options
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $description
 * @property-read mixed $name
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction applyFilters(array $options = [])
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction applySorts(array $sorts = [])
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction dropdown(string $column, string $key = null)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction like(string $column, string $value, string $side = 'both', string $boolean = 'and')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction listFrontEnd(array $options = [])
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction lists(string $column, string $key = null)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction newModelQuery()
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction newQuery()
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction orLike(string $column, string $value, string $side = 'both')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction orSearch(string $term, string $columns = [], string $mode = 'all')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction pluckDates(string $column, string $keyFormat = 'Y-m', string $valueFormat = 'F Y')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction query()
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction search(string $term, string $columns = [], string $mode = 'all')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction whereAutomationRuleId($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction whereClassName($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction whereCreatedAt($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction whereId($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction whereOptions($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleAction whereUpdatedAt($value)
 * @mixin \Igniter\Flame\Database\Model
 */
class RuleAction extends Model
{
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'igniter_automation_rule_actions';

    public $timestamps = true;

    protected $guarded = [];

    public $relation = [
        'belongsTo' => [
            'automation_rule' => [AutomationRule::class, 'key' => 'automation_rule_id'],
        ],
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public $rules = [
        'class_name' => 'required',
    ];

    //
    // Attributes
    //

    public function getNameAttribute()
    {
        return $this->getActionObject()->getActionName();
    }

    public function getDescriptionAttribute()
    {
        return $this->getActionObject()->getActionDescription();
    }

    //
    // Events
    //

    protected function afterFetch()
    {
        $this->applyActionClass();
        $this->loadCustomData();
    }

    protected function beforeSave()
    {
        $this->setCustomData();
    }

    public function applyCustomData()
    {
        $this->setCustomData();
        $this->loadCustomData();
    }

    /**
     * Extends this model with the action class
     * @param string $class Class name
     * @return bool
     */
    public function applyActionClass($class = null)
    {
        if (!$class) {
            $class = $this->class_name;
        }

        if ($class && !$this->isClassExtendedWith($class)) {
            $this->extendClassWith($class);
        }

        $this->class_name = $class;

        return true;
    }

    /**
     * @return \Igniter\Automation\Classes\BaseAction
     */
    public function getActionObject()
    {
        $this->applyActionClass();

        return $this->asExtension($this->getActionClass());
    }

    public function getActionClass()
    {
        return $this->class_name;
    }

    protected function loadCustomData()
    {
        $this->setRawAttributes((array)$this->getAttributes() + (array)$this->options, true);
    }

    protected function setCustomData()
    {
        if (!$actionObj = $this->getActionObject()) {
            throw new AutomationException(sprintf('Unable to find action object [%s]', $this->getActionClass()));
        }

        $config = $actionObj->getFieldConfig();
        if ($fields = array_get($config, 'fields')) {
            $fieldAttributes = array_keys($fields);
            $this->options = array_only($this->getAttributes(), $fieldAttributes);
            $this->setRawAttributes(array_except($this->getAttributes(), $fieldAttributes));
        }
    }
}
