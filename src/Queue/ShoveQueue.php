<?php

namespace Shove\Laravel\Queue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\Queue;
use Shove\Connector\ShoveConnector as ShoveHttpClient;

class ShoveQueue extends Queue implements QueueContract
{
    public function __construct(
        public ShoveHttpClient $shove,
        protected Request $request,
        public $default = 'default'
    ) {
    }

    /**
     * Get the size of the queue.
     *
     * @param  string|null  $queue
     * @return int
     */
    public function size($queue = null)
    {
        return 0;
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        //
    }

    /**
     * Push a new job onto the queue after (n) seconds.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        //
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        //
    }


    public function marshal(): Response
    {
        $this->createShoveJob($this->marshalShoveJob())->fire();

        return new Response('OK');
    }

    protected function createShoveJob($job): ShoveJob
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
                $response = $this->shove->jobs()->create(
                    queue: $queue ?? 'default',
                    body: json_decode($data, associative: true),
                );

                if (!$response->successful()) {
                    throw new \Exception('Failed to push job to Shove. Received status code: '.$response->status());
                }
            }
        );
    }

    public function getQueue($queue = null)
    {
        return $queue ?? $this->default;
    }
}
