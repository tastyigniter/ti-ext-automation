<?php namespace Igniter\Automation;

use Admin\Widgets\Form;
use Event;
use Igniter\Automation\Classes\EventManager;
use System\Classes\BaseExtension;

/**
 * Automation Extension Information File
 */
class Extension extends BaseExtension
{
    public function boot()
    {
        EventManager::bindRules();

        $this->extendActionFormFields();
    }

    public function registerPermissions()
    {
        return [
            'Igniter.Automation.Manage' => [
                'description' => 'Create, modify and delete automations',
                'group' => 'module',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'tools' => [
                'child' => [
                    'automation' => [
                        'priority' => 5,
                        'class' => 'automation',
                        'href' => admin_url('igniter/automation/automations'),
                        'title' => lang('igniter.automation::default.text_title'),
                        'permission' => 'Igniter.Automation.*',
                    ],
                ],
            ],
        ];
    }

    public function registerAutomationRules()
    {
        return [
            'events' => [],
            'actions' => [
                \Igniter\Automation\AutomationRules\Actions\AssignToGroup::class,
                \Igniter\Automation\AutomationRules\Actions\SendMailTemplate::class,
            ],
            'conditions' => [],
        ];
    }

    protected function extendActionFormFields()
    {
        Event::listen('admin.form.extendFieldsBefore', function (Form $form) {
            if (!$form->getController() instanceof \Igniter\Automation\Controllers\Automations) return;
            if ($form->model instanceof \Igniter\Automation\Models\RuleAction) {
                $form->arrayName .= '[options]';
                $form->fields = array_get($form->model->getFieldConfig(), 'fields', []);
            }
        });
    }
}
