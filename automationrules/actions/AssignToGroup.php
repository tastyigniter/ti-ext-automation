<?php

namespace Igniter\Automation\AutomationRules\Actions;

use Admin\ActivityTypes\AssigneeUpdated;
use Admin\Models\UserGroup;
use Admin\Traits\Assignable;
use Igniter\Automation\Classes\BaseAction;
use Igniter\Flame\Exception\ApplicationException;

class AssignToGroup extends BaseAction
{
    public function actionDetails()
    {
        return [
            'name' => 'Assign to user group',
            'description' => 'Automatically assign an order/reservation to a user group',
        ];
    }

    public function defineFormFields()
    {
        return [
            'fields' => [
                'staff_group_id' => [
                    'label' => 'lang:igniter.automation::default.label_assign_to_staff_group',
                    'type' => 'select',
                    'options' => ['Admin\Models\UserGroup', 'getDropdownOptions'],
                ],
            ],
        ];
    }

    public function triggerAction($params)
    {
        if (!$groupId = $this->model->staff_group_id)
            throw new ApplicationException('Missing valid user group to assign to.');

        if (!$assigneeGroup = UserGroup::find($groupId))
            throw new ApplicationException('Invalid user group to assign to.');

        if (!$assigneeGroup->autoAssignEnabled())
            throw new ApplicationException('The user group auto assignment must be enabled to use with automations');

        $assignable = array_get($params, 'order', array_get($params, 'reservation'));
        if (!in_array(Assignable::class, class_uses_recursive(get_class($assignable))))
            throw new ApplicationException('Missing assignable model.');

        $log = $assignable->assignToGroup($assigneeGroup);

        AssigneeUpdated::log($log);
    }
}
