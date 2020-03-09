<?php

namespace Igniter\Automation\Classes;

class BaseModelAttributesCondition extends BaseCondition
{
    protected $modelClass;

    protected $operators = [
        'is' => 'is',
        'is_not' => 'is not',
        'contains' => 'contains',
        'does_not_contain' => 'does not contain',
        'equals_or_greater' => 'equals or greater than',
        'equals_or_less' => 'equals or less than',
        'greater' => 'greater than',
        'less' => 'less than',
    ];

    public function initConfigData($model)
    {
        $model->operator = 'is';
    }

    public function defineModelAttributes()
    {
        return [];
    }

    public function getConditionDescription()
    {
        $model = $this->model;
        $attributes = $this->listModelAttributes();
        $subConditions = $model->options ?? [];

        $result = collect($subConditions)->sortBy('priority')->map(function ($subCondition) use ($attributes) {
            $attribute = array_get($subCondition, 'attribute');
            $operator = array_get($subCondition, 'operator');
            $value = array_get($subCondition, 'value');

            $result = $this->getConditionAttributePrefix($attribute, $attributes);
            $result .= ' <b>'.array_get($this->operators, $operator, $operator).'</b> ';
            $result .= $value;

            return $result;
        })->toArray();

        return implode(' - ', $result);
    }

    protected function getConditionAttributePrefix($attribute, $attributes)
    {
        $result = [];
        if (isset($attributes[$attribute]))
            $result = $attributes[$attribute];

        return array_get($result, 'label', 'Unknown attribute');
    }

    public function getAttributeOptions()
    {
        return array_map(function ($attribute) {
            return array_get($attribute, 'label');
        }, $this->listModelAttributes());
    }

    public function getOperatorOptions()
    {
        return $this->operators;
    }

    /**
     * Checks whether the condition is TRUE for a specified model
     * @param $modelToEval
     * @return bool
     */
    public function evalIsTrue($modelToEval)
    {
        $model = $this->model;
        $attributes = $this->listModelAttributes();
        $subConditions = $model->options ?? [];

        collect($subConditions)->sortBy('priority')->each(function ($subCondition) use (&$success, $modelToEval, $attributes) {
            $attribute = array_get($subCondition, 'attribute');
            $attributeType = array_get($attributes, $attribute.'.type');

            if ($attributeType == 'string')
                $success = $this->evalAttributeStringType($modelToEval, $subCondition);

            return $success;
        });

        return $success;
    }

    protected function listModelAttributes()
    {
        if ($this->modelAttributes)
            return $this->modelAttributes;

        $attributes = array_map(function (&$info) {
            isset($info['type']) OR $info['type'] = 'string';

            return $info;
        }, $this->defineModelAttributes());

        return $this->modelAttributes = $attributes;
    }

    protected function evalAttributeStringType($model, $subCondition)
    {
        $attribute = array_get($subCondition, 'attribute');
        $operator = array_get($subCondition, 'operator');
        $conditionValue = mb_strtolower(trim(array_get($subCondition, 'value')));
        $modelValue = mb_strtolower(trim($model->{$attribute}));

        if ($operator == 'is')
            return $modelValue == $conditionValue;

        if ($operator == 'is_not')
            return $modelValue != $conditionValue;

        if ($operator == 'contains')
            return mb_strpos($modelValue, $conditionValue) !== FALSE;

        if ($operator == 'does_not_contain')
            return mb_strpos($modelValue, $conditionValue) === FALSE;

        if ($operator == 'equals_or_greater')
            return $modelValue >= $conditionValue;

        if ($operator == 'equals_or_less')
            return $modelValue <= $conditionValue;

        if ($operator == 'greater')
            return $modelValue > $conditionValue;

        if ($operator == 'less')
            return $modelValue < $conditionValue;

        return FALSE;
    }
}