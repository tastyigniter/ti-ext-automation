<?php

namespace Igniter\Automation\Tests\AutomationRules\Actions;

use Igniter\Automation\AutomationException;
use Igniter\Automation\AutomationRules\Actions\SendMailTemplate;
use Igniter\Automation\Models\RuleAction;
use Igniter\Local\Models\Location;
use Igniter\System\Mail\AnonymousTemplateMailable;
use Igniter\System\Models\MailTemplate;
use Igniter\User\Models\Customer;
use Igniter\User\Models\CustomerGroup;
use Igniter\User\Models\User;
use Igniter\User\Models\UserGroup;
use Illuminate\Support\Facades\Mail;

beforeEach(function() {
    $this->mailTemplate = MailTemplate::create([
        'code' => 'test_template',
        'subject' => 'Test Subject',
        'body' => 'Test Body',
        'is_custom' => 1,
    ]);
});

it('sends mail template to custom email', function() {
    Mail::fake();

    $customEmail = 'custom@domain.tld';
    $sendMailTemplate = new SendMailTemplate(new RuleAction([
        'template' => $this->mailTemplate->code,
        'send_to' => 'custom',
        'custom' => $customEmail,
    ]));

    $sendMailTemplate->triggerAction([]);

    Mail::assertSent(AnonymousTemplateMailable::class, function($mailable) use ($customEmail) {
        return $mailable->getTemplateCode() === $this->mailTemplate->code
            && array_get($mailable->to, '0.address') === $customEmail;
    });
});

it('sends mail template to restaurant', function() {
    Mail::fake();

    $sendMailTemplate = new SendMailTemplate(new RuleAction([
        'template' => $this->mailTemplate->code,
        'send_to' => 'restaurant',
    ]));

    $sendMailTemplate->triggerAction([]);

    Mail::assertSent(AnonymousTemplateMailable::class, function($mailable) {
        return $mailable->getTemplateCode() === $this->mailTemplate->code
            && array_get($mailable->to, '0.address') === setting('site_email', config('mail.from.address'));
    });
});

it('sends mail template to staff group', function() {
    Mail::fake();

    $staff = User::factory()->create();
    $staffGroup = UserGroup::factory()->create();
    $staff->addGroups([$staffGroup->getKey()]);

    $sendMailTemplate = new SendMailTemplate(new RuleAction([
        'template' => $this->mailTemplate->code,
        'send_to' => 'staff_group',
        'staff_group' => $staffGroup->getKey(),
    ]));

    $sendMailTemplate->triggerAction([]);

    Mail::assertSent(AnonymousTemplateMailable::class, function($mailable) use ($staff) {
        return $mailable->getTemplateCode() === $this->mailTemplate->code
            && array_get($mailable->to, '0.address') === $staff->email;
    });
});

it('sends mail template to customer group', function() {
    Mail::fake();

    $customerGroup = CustomerGroup::factory()->create();
    $customer = Customer::factory()->create([
        'customer_group_id' => $customerGroup->getKey(),
    ]);
    $sendMailTemplate = new SendMailTemplate(new RuleAction([
        'template' => $this->mailTemplate->code,
        'send_to' => 'customer_group',
        'customer_group' => $customerGroup->getKey(),
    ]));

    $sendMailTemplate->triggerAction([]);

    Mail::assertSent(AnonymousTemplateMailable::class, function($mailable) use ($customer) {
        return $mailable->getTemplateCode() === $this->mailTemplate->code
            && array_get($mailable->to, '0.address') === $customer->email;
    });
});

it('sends mail template to location', function() {
    Mail::fake();

    $location = Location::factory()->create();

    $sendMailTemplate = new SendMailTemplate(new RuleAction([
        'template' => $this->mailTemplate->code,
        'send_to' => 'location',
    ]));

    $sendMailTemplate->triggerAction(['location' => $location]);

    Mail::assertSent(AnonymousTemplateMailable::class, function($mailable) use ($location) {
        return $mailable->getTemplateCode() === $this->mailTemplate->code
            && array_get($mailable->to, '0.address') === $location->location_email;
    });
});

it('throws exception when missing mail template', function() {
    $sendMailTemplate = new SendMailTemplate(new RuleAction(['template' => null]));

    $sendMailTemplate->triggerAction([]);
})->throws(AutomationException::class, 'SendMailTemplate: Missing a valid mail template');

it('throws exception when missing recipient', function() {
    $sendMailTemplate = new SendMailTemplate(new RuleAction(['template' => 'test_template']));

    $sendMailTemplate->triggerAction([]);
})->throws(AutomationException::class, 'SendMailTemplate: Missing a valid recipient from the event payload');

it('sends nothing when missing custom email', function() {
    Mail::fake();

    $sendMailTemplate = new SendMailTemplate(new RuleAction([
        'template' => 'test_template',
        'send_to' => 'custom',
        'custom' => null,
    ]));

    $sendMailTemplate->triggerAction([]);

    Mail::assertNothingSent();
});
