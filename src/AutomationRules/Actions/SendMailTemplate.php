<?php

namespace Igniter\Automation\AutomationRules\Actions;

use Igniter\Automation\AutomationException;
use Igniter\Automation\Classes\BaseAction;
use Igniter\System\Helpers\MailHelper;
use Igniter\System\Models\MailTemplate;
use Igniter\User\Models\Customer;
use Igniter\User\Models\CustomerGroup;
use Igniter\User\Models\User;
use Igniter\User\Models\UserGroup;
use Illuminate\Support\Facades\DB;

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
                'customer_group' => [
                    'label' => 'lang:igniter.automation::default.label_send_to_customer_group',
                    'type' => 'select',
                    'options' => [CustomerGroup::class, 'getDropdownOptions'],
                    'trigger' => [
                        'action' => 'show',
                        'field' => 'send_to',
                        'condition' => 'value[customer_group]',
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
            throw new AutomationException('SendMailTemplate: Missing a valid mail template');
        }

        if (!$recipient = $this->getRecipientAddress($params)) {
            throw new AutomationException('SendMailTemplate: Missing a valid recipient from the event payload');
        }

        foreach ($recipient as $address => $name) {
            if (empty($address)) continue;

            MailHelper::sendTemplate($templateCode, $params, [$address, $name]);
        }
    }

    public function getTemplateOptions()
    {
        return MailTemplate::dropdown('label', 'code');
    }

    public function getSendToOptions()
    {
        return [
            'custom' => 'lang:igniter.user::default.text_send_to_custom',
            'restaurant' => 'lang:igniter.user::default.text_send_to_restaurant',
            'location' => 'lang:igniter.user::default.text_send_to_location',
            'staff' => 'lang:igniter.user::default.text_send_to_staff_email',
            'customer' => 'lang:igniter.user::default.text_send_to_customer_email',
            'customer_group' => 'lang:igniter.user::default.text_send_to_customer_group',
            'staff_group' => 'lang:igniter.user::default.text_send_to_staff_group',
            'all_staff' => 'lang:igniter.user::default.text_send_to_all_staff',
            'all_customer' => 'lang:igniter.user::default.text_send_to_all_customer',
        ];
    }

    protected function getRecipientAddress($params)
    {
        $mode = $this->model->send_to;

        switch ($mode) {
            case 'custom':
                return [$this->model->custom => ''];
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
                        throw new AutomationException('Unable to find staff group with ID: '.$groupId);
                    }

                    return $staffGroup->users->pluck('name', 'email')->all();
                }

                return null;
            case 'customer_group':
                if ($groupId = $this->model->customer_group) {
                    if (!$customerGroup = CustomerGroup::find($groupId)) {
                        throw new AutomationException('Unable to find customer group with ID: '.$groupId);
                    }

                    return $customerGroup->customers()
                        ->select('email', DB::raw('concat(first_name, last_name) as full_name'))
                        ->whereIsEnabled()
                        ->pluck('full_name', 'email')
                        ->all();
                }

                return null;
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
            case 'all_staff':
                return User::whereIsEnabled()->pluck('staff_name', 'staff_email')->all();
            case 'all_customer':
                return Customer::whereIsEnabled()
                    ->select('email', 'concat(first_name, last_name) as full_name')
                    ->pluck('full_name', 'email')
                    ->all();
        }
    }
}
