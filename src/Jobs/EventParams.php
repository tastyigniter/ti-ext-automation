<?php

declare(strict_types=1);

namespace Igniter\Automation\Jobs;

use Igniter\Automation\Classes\EventManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;

class EventParams implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesAndRestoresModelIdentifiers;

    protected $params;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $eventClass, array $params)
    {
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

    /**
     * Restore the model from the model identifier instance.
     *
     * Overrides the trait method for Laravel 12 compatibility: older framework
     * builds called $value->getClass(), but Laravel 12 removed that method and
     * exposes the class name as a public $class property instead.
     */
    public function restoreModel($value)
    {
        $class = method_exists($value, 'getClass') ? $value->getClass() : $value->class;

        return $this->getQueryForModelRestoration(
            (new $class)->setConnection($value->connection), $value->id
        )->useWritePdo()->firstOrFail()->loadMissing($value->relations ?? []);
    }

    /**
     * Restore a queueable collection instance.
     *
     * Overrides the trait method for Laravel 12 compatibility (same reason as
     * restoreModel above).
     */
    protected function restoreCollection($value)
    {
        $class = method_exists($value, 'getClass') ? $value->getClass() : $value->class;

        if (!$class || count($value->id) === 0) {
            return !is_null($value->collectionClass ?? null)
                ? new $value->collectionClass
                : new \Illuminate\Database\Eloquent\Collection;
        }

        $collection = $this->getQueryForModelRestoration(
            (new $class)->setConnection($value->connection), $value->id
        )->useWritePdo()->get();

        if (is_a($class, \Illuminate\Database\Eloquent\Relations\Pivot::class, true) ||
            in_array(\Illuminate\Database\Eloquent\Relations\Concerns\AsPivot::class, class_uses($class))) {
            return $collection;
        }

        $collection = $collection->keyBy->getKey();
        $collectionClass = get_class($collection);

        return new $collectionClass(
            (new \Illuminate\Support\Collection($value->id))
                ->map(fn($id) => $collection[$id] ?? null)
                ->filter()
        );
    }
}
