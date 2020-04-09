<?php

namespace Igniter\Automation\Classes;

use InvalidArgumentException;

class WebhookServer
{
    /**
     * @var \Igniter\Automation\Classes\WebhookServerJob
     */
    protected $job;

    protected $secret;

    protected $headers = [];

    protected $payload = [];

    protected static $signatureHeaderName = 'Signature';

    public function __construct()
    {
        $this->job = app(WebhookServerJob::class);
    }

    /**
     * @return \Igniter\Automation\Classes\WebhookServer
     */
    public static function create()
    {
        return (new static())->verifySsl();
    }

    public function url(string $url)
    {
        $this->job->webhookUrl = $url;

        return $this;
    }

    public function payload(array $payload)
    {
        $this->payload = $payload;
        $this->job->payload = $payload;

        return $this;
    }

    public function useHttpVerb(string $verb)
    {
        $this->job->httpVerb = $verb;

        return $this;
    }

    public function timeoutInSeconds(int $timeoutInSeconds)
    {
        $this->job->requestTimeout = $timeoutInSeconds;

        return $this;
    }

    public function verifySsl(bool $verifySsl = TRUE)
    {
        $this->job->verifySsl = $verifySsl;

        return $this;
    }

    public function useSecret(string $secret)
    {
        $this->secret = $secret;

        return $this;
    }

    public function withHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public function dispatch()
    {
        if (!$this->job->webhookUrl)
            throw new InvalidArgumentException('Could not call the webhook because the url has not been set.');

        if (empty($this->secret))
            throw new InvalidArgumentException('Could not call the webhook because no secret has been set.');

        $this->job->headers = $this->getAllHeaders();

        $this->job->dispatch();
    }

    protected function getAllHeaders()
    {
        $headers = $this->headers;
        $signature = $this->calculateSignature($this->payload, $this->secret);
        $headers[static::$signatureHeaderName] = $signature;

        return $headers;
    }

    protected function calculateSignature(array $payload, $secret)
    {
        $jsonPayload = json_encode($payload);

        return hash_hmac('sha256', $jsonPayload, $secret);
    }
}