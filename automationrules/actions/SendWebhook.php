<?php

namespace Igniter\Automation\AutomationRules\Actions;

use ApplicationException;
use Igniter\Automation\Classes\BaseAction;
use Igniter\Automation\Classes\WebhookServer;

class SendWebhook extends BaseAction
{
    public function actionDetails()
    {
        return [
            'name' => 'Send payload to Webhooks',
            'description' => 'Send HTTP POST payload to the webhook\'s URL',
        ];
    }

    public function defineFormFields()
    {
        return [
            'fields' => [
                'webhooks' => [
                    'label' => 'lang:igniter.automation::default.webhook.label_webhooks',
                    'type' => 'section',
                    'comment' => 'lang:igniter.automation::default.webhook.help_webhooks',
                ],
                'url' => [
                    'label' => 'lang:igniter.automation::default.webhook.label_url',
                    'type' => 'text',
                    'comment' => 'lang:igniter.automation::default.webhook.help_url',
                ],
            ],
        ];
    }

    public function triggerAction($params)
    {
        $webhookUrl = $this->model->url;

        if (!$webhookUrl)
            throw new ApplicationException('Send Webhook event rule is missing a valid webhook url');

        $webhookServer = WebhookServer::create();
        $webhookServer
            ->url($webhookUrl)
            ->payload($params)
            ->useSecret(str_random())
            ->dispatch();
    }
}