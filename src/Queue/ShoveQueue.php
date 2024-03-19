<?php

namespace Shove\Laravel\Queue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\Queue;
use Shove\Connector\ShoveConnector as ShoveHttpClient;
use Shove\Laravel\Facades\Shove;
use Shove\Requests\Jobs\CreateRequest;

class ShoveQueue extends Queue implements QueueContract
{
    public function __construct(
        public ShoveHttpClient $shove,
        protected Request $request,
        public $default = 'default'
    ) {
    }

    public function size($queue = null)
    {
        //
    }

    public function marshal(): Response
    {
        $this->createShoveJob($this->marshalShoveJob())->fire();

        return new Response('OK');
    }

    private function createShoveJob($job): ShoveJob
    {
        return new ShoveJob($this->container, $job);
    }

    protected function marshalShoveJob(): object
    {
        $r = $this->request;

        $body = $r->getContent();

        return (object) [
            'id' => $r->header('Shove-Job-Id'),
            'attempts' => $r->header('Shove-Job-Attempt-Number'),
            'body' => $body,
        ];
    }

    public function push($job, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);

        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $queue, $data),
            $queue,
            0,
            function ($data, $queue): void {
                Shove::jobs()->create(
                    queue: $queue ?? 'default',
                    headers: [],
                    body: json_decode($data, true)
                );
            }
        );
    }

    public function getQueue($queue = null)
    {
        return $queue ?? $this->default;
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        //
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        //
    }

    public function pop($queue = null)
    {
        //
    }

    public function pushOn($queue, $job, $data = '')
    {
        // TODO: Implement pushOn() method.
    }

    public function laterOn($queue, $delay, $job, $data = '')
    {
        // TODO: Implement laterOn() method.
    }

    public function bulk($jobs, $data = '', $queue = null)
    {
        // TODO: Implement bulk() method.
    }

    public function getConnectionName()
    {
        // TODO: Implement getConnectionName() method.
    }

    public function setConnectionName($name)
    {
        // TODO: Implement setConnectionName() method.
    }
}