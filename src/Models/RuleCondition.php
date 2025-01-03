<?php

namespace Igniter\Automation\Models;

use Igniter\Flame\Database\Model;

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
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition applyFilters(array $options = [])
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition applySorts(array $sorts = [])
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition dropdown(string $column, string $key = null)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition like(string $column, string $value, string $side = 'both', string $boolean = 'and')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition listFrontEnd(array $options = [])
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition lists(string $column, string $key = null)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition newModelQuery()
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition newQuery()
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition orLike(string $column, string $value, string $side = 'both')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition orSearch(string $term, string $columns = [], string $mode = 'all')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition pluckDates(string $column, string $keyFormat = 'Y-m', string $valueFormat = 'F Y')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition query()
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition search(string $term, string $columns = [], string $mode = 'all')
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition whereAutomationRuleId($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition whereClassName($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition whereCreatedAt($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition whereId($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition whereOptions($value)
 * @method static \Igniter\Flame\Database\Builder<static>|RuleCondition whereUpdatedAt($value)
 * @mixin \Igniter\Flame\Database\Model
 */
class RuleCondition extends Model
{
    /**
     * @var string The database table name
     */
    protected $table = 'igniter_automation_rule_conditions';

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
        return $this->getConditionObject()->getConditionName();
    }

    public function getDescriptionAttribute()
    {
        return $this->getConditionObject()->getConditionDescription();
    }

    //
    // Events
    //

    protected function afterFetch()
    {
        $this->applyConditionClass();
    }

    /**
     * Extends this model with the condition class
     * @param string $class Class name
     * @return bool
     */
    public function applyConditionClass($class = null)
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
     * @return \Igniter\Automation\Classes\BaseCondition
     */
    public function getConditionObject()
    {
        $this->applyConditionClass();

        return $this->asExtension($this->getConditionClass());
    }

    public function getConditionClass()
    {
        return $this->class_name;
    }
}
