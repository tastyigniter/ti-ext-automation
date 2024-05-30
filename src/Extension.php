<?php

namespace Igniter\Automation;

use Igniter\Admin\Widgets\Form;
use Igniter\Automation\Classes\EventManager;
use Igniter\Automation\Models\AutomationLog;
use Igniter\Flame\Igniter;
use Igniter\System\Classes\BaseExtension;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;

/**
 * Automation Extension Information File
 */
class Extension extends BaseExtension
{
    public function register()
    {
        $this->app->singleton(EventManager::class);

        $this->registerConsoleCommand('automation.cleanup', Console\Cleanup::class);
    }

    public function boot()
    {
        EventManager::bindRules();

        $this->extendActionFormFields();

        Igniter::prunableModel(AutomationLog::class);
    }

    public function registerPermissions(): array
    {
        return [
            'Igniter.Automation.Manage' => [
                'description' => 'Create, modify and delete automations',
                'group' => 'igniter::admin.permissions.name',
            ],
        ];
    }

    public function registerNavigation(): array
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
            'events' => [
                'automation.order.schedule.hourly' => \Igniter\Automation\AutomationRules\Events\OrderSchedule::class,
                'automation.reservation.schedule.hourly' => \Igniter\Automation\AutomationRules\Events\ReservationSchedule::class,
            ],
            'actions' => [
                \Igniter\Automation\AutomationRules\Actions\AssignToGroup::class,
                \Igniter\Automation\AutomationRules\Actions\SendMailTemplate::class,
            ],
            'conditions' => [],
        ];
    }

    /**
     * Registers scheduled tasks that are executed on a regular basis.
     */
    public function registerSchedule(Schedule $schedule)
    {
        $schedule->call(function() {
            // Pull orders created within the last 30days
            EventManager::fireOrderScheduleEvents();
        })->name('automation-order-schedule')->withoutOverlapping(5)->runInBackground()->hourly();

        $schedule->call(function() {
            // Pull reservations booked within the last 30days
            EventManager::fireReservationScheduleEvents();
        })->name('automation-reservation-schedule')->withoutOverlapping(5)->runInBackground()->hourly();

        $schedule->command('automation:cleanup')->name('Automation Log Cleanup')->daily();
    }

    protected function extendActionFormFields()
    {
        Event::listen('admin.form.extendFieldsBefore', function(Form $form) {
            if (!$form->getController() instanceof \Igniter\Automation\Http\Controllers\Automations) {
                return;
            }
            if ($form->model instanceof \Igniter\Automation\Models\RuleAction) {
                $form->arrayName .= '[options]';
                $form->fields = array_get($form->model->getFieldConfig(), 'fields', []);
            }
        });
    }
}
