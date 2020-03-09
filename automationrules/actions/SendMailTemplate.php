<?php

namespace Igniter\Automation\AutomationRules\Actions;

use Admin\Models\Staff_groups_model;
use Admin\Models\Staffs_model;
use ApplicationException;
use Igniter\Automation\Classes\BaseAction;
use Mail;
use System\Models\Mail_templates_model;

class SendMailTemplate extends BaseAction
{
    public function actionDetails()
    {
        return [
            'name' => 'Compose a mail template',
            'description' => 'Send a message to a recipient',
        ];
    }

    public function defineFormFields()
    {
        return [
            'fields' => [
                'template' => [
                    'label' => 'lang:igniter.user::default.label_template',
                    'type' => 'select',
                ],
                'send_to' => [
                    'label' => 'lang:igniter.user::default.label_send_to',
                    'type' => 'select',
                ],
                'staff_group' => [
                    'label' => 'lang:igniter.user::default.label_send_to_staff_group',
                    'type' => 'select',
                    'options' => ['Admin\Models\Staff_groups_model', 'getDropdownOptions'],
                    'trigger' => [
                        'action' => 'show',
                        'field' => 'send_to',
                        'condition' => 'value[staff_group]',
                    ],
                ],
                'custom' => [
                    'label' => 'lang:igniter.user::default.label_send_to_custom',
                    'type' => 'text',
                    'trigger' => [
                        'action' => 'show',
                        'field' => 'send_to',
                        'condition' => 'value[custom]',
                    ],
                ],
            ],
        ];
    }

    public function triggerAction($params)
    {
        $template = $this->model->template;
        $recipient = $this->getRecipientAddress($params);

        if (!$recipient OR !$template) {
            throw new ApplicationException('Missing valid recipient or mail template');
        }

        Mail::sendToMany($recipient, $template, $params);
    }

    public function getTemplateOptions()
    {
        return Mail_templates_model::dropdown('label', 'code');
    }

    public function getSendToOptions()
    {
        return [
            'restaurant' => 'lang:igniter.user::default.text_send_to_restaurant',
            'location' => 'lang:igniter.user::default.text_send_to_location',
            'staff' => 'lang:igniter.user::default.text_send_to_staff_email',
            'customer' => 'lang:igniter.user::default.text_send_to_customer_email',
            'custom' => 'lang:igniter.user::default.text_send_to_custom',
            'staff_group' => 'lang:igniter.user::default.text_send_to_staff_group',
        ];
    }

    protected function getRecipientAddress($params)
    {
        $mode = $this->model->send_to;

        switch ($mode) {
            case 'custom':
                return $this->model->custom;
            case 'system':
                $name = config('mail.from.name', 'Your Site');
                $address = config('mail.from.address', 'admin@domain.tld');

                return [$address => $name];
            case 'restaurant':
                $name = setting('site_name', 'Your Site');
                $address = setting('site_email', 'admin@domain.tld');

                return [$address => $name];
            case 'location':
                $location = array_get($params, 'location');
                if (empty($location->location_email) AND empty($location->location_name))
                    return null;

                return [$location->location_email => $location->location_name];
            case 'staff_group':
                if ($groupId = $this->model->staff_group) {
                    if (!$staffGroup = Staff_groups_model::find($groupId))
                        throw new ApplicationException('Unable to find staff group with ID: '.$groupId);

                    return $staffGroup->staffs->lists('staff_name', 'staff_email');
                }
                else {
                    return Staffs_model::all()->lists('staff_name', 'staff_email');
                }
            case 'customer':
                $customer = array_get($params, 'customer');
                if (empty($customer->email) AND empty($customer->full_name))
                    return null;

                return [$customer->email => $customer->full_name];
            case 'staff':
                $staff = array_get($params, 'staff');
                if (empty($staff->staff_email) AND empty($staff->staff_name))
                    return null;

                return [$staff->staff_email => $staff->staff_name];
        }
    }
}