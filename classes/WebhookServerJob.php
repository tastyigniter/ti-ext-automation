<?php

namespace Igniter\Automation\Classes;

use Event;
use Exception;
use GuzzleHttp\Client;

class WebhookServerJob
{
    /**
     * @var string
     */
    public $webhookUrl;

    /**
     * @var string
     */
    public $httpVerb = 'post';

    /**
     * @var int
     */
    public $requestTimeout = 3;

    /**
     * @var array
     */
    public $headers = [];

    /**
     * @var bool
     */
    public $verifySsl = TRUE;

    /**
     * @var array
     */
    public $payload = [];

    /**
     * @var \GuzzleHttp\Psr7\Response|null
     */
    protected $response;

    public function dispatch()
    {
        $this->response = (new Client)->request($this->httpVerb, $this->webhookUrl, [
            'timeout' => $this->requestTimeout,
            'body' => json_encode($this->payload),
            'verify' => $this->verifySsl,
            'headers' => $this->headers,
        ]);

        if (!starts_with($this->response->getStatusCode(), 2))
            throw new Exception('Webhook call failed');

        Event::fire('igniter.automation.webhookSent', [$this]);
    }

    public function getResponse()
    {
        return $this->response;
    }
}