<?php

namespace Shove\Laravel\Tests;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\CallQueuedHandler;
use Mockery as m;
use Shove\Connector\ShoveConnector;
use Shove\Laravel\Queue\ShoveQueue;

class ShoveQueueTest extends TestCase
{
    public function test_can_marshal_an_incoming_job()
    {
        $job = json_encode([
            'uuid' => '67ee8eef-7747-4614-80b6-d59375a5cf4e',
            'displayName' => 'App\Jobs\BanUser',
            'job' => 'Illuminate\Queue\CallQueuedHandler@call',
            'maxTries' => null,
            'maxExceptions' => null,
            'failOnTimeout' => false,
            'backoff' => null,
            'timeout' => null,
            'retryUntil' => null,
            'data' => [
                'commandName' => 'App\Jobs\BanUser',
                'command' => 'O:16:"App\Jobs\BanUser":1:{s:4:"user";O:45:"Illuminate\Contracts\Database\ModelIdentifier":5:{s:5:"class";s:15:"App\Models\User";s:2:"id";i:1;s:9:"relations";a:0:{}s:10:"connection";s:6:"sqlite";s:15:"collectionClass";N;}'
            ]
        ]);

        $request = m::mock(Request::class);
        $request->shouldReceive('getContent')->once()->andReturn($job);
        $request->shouldReceive('header')->once()->with('Shove-Job-Id')->andReturn(42069);
        $request->shouldReceive('header')->once()->with('Shove-Job-Attempt-Number')->andReturn(1);

        $queue = m::mock(ShoveQueue::class, [
            m::mock(ShoveConnector::class), $request, 'default'
        ])->makePartial();

        $handler = m::mock(CallQueuedHandler::class);
        $handler->shouldReceive('call')->once()->andReturnNull();

        $queue->setContainer($container = m::mock(Container::class));
        $container->shouldReceive('make')->once()->with(CallQueuedHandler::class)->andReturn($handler);

        $response = $queue->marshal();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_can_push_a_job_to_the_queue()
    {
        $jobs = new class {
            public function create($queue, $headers, $body)
            {
            }
        };

        $connector = m::mock(ShoveConnector::class);
        $connector->shouldReceive('jobs')->once()->andReturn($jobs);

        $request = m::mock(Request::class);

        $queue = new ShoveQueue(
            $connector,
            $request,
            'default'
        );

        $queue->setContainer($container = m::mock(Container::class));

        $container->shouldReceive('bound')->times(2)->andReturnTrue();
        $container->shouldReceive('offsetGet')->times(2)->andReturn($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('dispatch')->times(2);

        $queue->push('foo', [1, 2, 3]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }
}