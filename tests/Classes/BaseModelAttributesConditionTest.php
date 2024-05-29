<?php

use Igniter\Automation\Classes\BaseModelAttributesCondition;

it('defines model attributes', function() {
    $condition = new BaseModelAttributesCondition();

    expect($condition->defineModelAttributes())->toBeArray();
});

it('evalIsTrue returns false when no subconditions', function() {
    $modelToEval = new stdClass();
    $baseModelAttributesCondition = new BaseModelAttributesCondition();
    expect($baseModelAttributesCondition->evalIsTrue($modelToEval))->toBeFalse();
});

it('evalIsTrue evaluates string type attribute correctly', function($evalValue, $operator, $value, $eval) {
    $modelToEval = new stdClass();
    $modelToEval->attribute = $evalValue;

    $baseModelAttributesCondition = new class($operator, $value) extends BaseModelAttributesCondition {
        public static $testOperator = '';
        public static $testValue = '';

        public function __construct($operator, $value)
        {
            $this->model = new class([
                'options' => [
                    ['attribute' => 'attribute', 'operator' => $operator, 'value' => $value]
                ]
            ]) extends \Igniter\Flame\Database\Model {
            };
        }

        public function defineModelAttributes()
        {
            return [
                'attribute' => [
                    'label' => 'Attribute label'
                ]
            ];
        }
    };

    expect($baseModelAttributesCondition->evalIsTrue($modelToEval))->toBe($eval);
})->with([
    ['test', 'is', 'test', true],
    ['test', 'is', 'wrong', false],
    ['test', 'is_not', 'test', false],
    ['test', 'is_not', 'wrong', true],
    ['test', 'contains', 'es', true],
    ['test', 'contains', 'wrong', false],
    ['test', 'does_not_contain', 'es', false],
    ['test', 'does_not_contain', 'wrong', true],
    [10, 'equals_or_greater', 10, true],
    [10, 'equals_or_greater', 5, true],
    [10, 'equals_or_greater', 15, false],
    [10, 'equals_or_less', 10, true],
    [10, 'equals_or_less', 15, true],
    [10, 'equals_or_less', 5, false],
    [10, 'greater', 5, true],
    [10, 'greater', 15, false],
    [10, 'less', 15, true],
    [10, 'less', 5, false],
]);
