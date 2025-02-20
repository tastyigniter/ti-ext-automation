<?php

declare(strict_types=1);

namespace Igniter\Automation\Jobs;

use Igniter\Automation\Classes\EventManager;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;

class EventParams
{
    use InteractsWithQueue;
    use SerializesAndRestoresModelIdentifiers;

    protected $eventClass;

    protected $params;

    /**
     * Create a new job instance.
     */
    public function __construct($eventClass, array $params)
    {
        $this->eventClass = $eventClass;

        $this->params = $this->serializeParams($params);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        resolve(EventManager::class)->fireEvent(
            $this->eventClass,
            $this->unserializeParams()
        );

        $this->delete();
    }

    protected function serializeParams($params)
    {
        $result = [];

        foreach ($params as $param => $value) {
            $result[$param] = $this->getSerializedPropertyValue($value);
        }

        return $result;
    }

    protected function unserializeParams()
    {
        $result = [];

        foreach ($this->params as $param => $value) {
            $result[$param] = $this->getRestoredPropertyValue($value);
        }

        return $result;
    }
}
