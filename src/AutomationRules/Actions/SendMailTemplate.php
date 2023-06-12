<?php

namespace Igniter\Automation\AutomationRules\Actions;

use Igniter\Automation\Classes\BaseAction;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\System\Models\MailTemplate;
use Igniter\User\Models\UserGroup;
use Illuminate\Support\Facades\Mail;

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
                    'options' => [\Igniter\User\Models\UserGroup::class, 'getDropdownOptions'],
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
        if (!$templateCode = $this->model->template) {
            throw new ApplicationException('SendMailTemplate: Missing a valid mail template');
        }

        if (!$recipient = $this->getRecipientAddress($params)) {
            throw new ApplicationException('SendMailTemplate: Missing a valid recipient from the event payload');
        }

        Mail::sendToMany($recipient, $templateCode, $params);
    }

    public function getTemplateOptions()
    {
        return MailTemplate::dropdown('label', 'code');
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
                $name = setting('site_name', config('app.name'));
                $address = setting('site_email', config('mail.from.address'));

                return [$address => $name];
            case 'location':
                $location = array_get($params, 'location');
                if (empty($location->location_email) && empty($location->location_name)) {
                    return null;
                }

                return [$location->location_email => $location->location_name];
            case 'staff_group':
                if ($groupId = $this->model->staff_group) {
                    if (!$staffGroup = UserGroup::find($groupId)) {
                        throw new ApplicationException('Unable to find staff group with ID: '.$groupId);
                    }

                    return $staffGroup->staffs->lists('staff_name', 'staff_email');
                }

                return Staff::all()->lists('staff_name', 'staff_email');
            case 'customer':
                $customer = array_get($params, 'customer');
                if (!empty($customer->email) && !empty($customer->full_name)) {
                    return [$customer->email => $customer->full_name];
                }

                $fullName = array_get($params, 'first_name').' '.array_get($params, 'last_name');
                if (array_key_exists('email', $params)) {
                    return [$params['email'] => $fullName];
                }

                return null;
            case 'staff':
                $staff = array_get($params, 'staff');
                if (!empty($staff->staff_email) && !empty($staff->staff_name)) {
                    return [$staff->staff_email => $staff->staff_name];
                }

                $orderOrReservation = array_get($params, 'order', array_get($params, 'reservation'));
                if ($orderOrReservation && $staff = $orderOrReservation->assignee) {
                    return [$staff->staff_email => $staff->staff_name];
                }
        }
    }
}
