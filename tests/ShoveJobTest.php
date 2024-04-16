<?php

namespace Shove\Laravel\Tests;

use Illuminate\Container\Container;
use Illuminate\Queue\CallQueuedHandler;
use Mockery as m;
use Shove\Laravel\Queue\ShoveJob;

class ShoveJobTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function test_fire_properly_calls_the_job_handler()
    {
        $handler = m::mock(CallQueuedHandler::class);
        $handler->shouldReceive('fire')->once();

        $container = m::mock(Container::class);
        $container->shouldReceive('make')->once()->with(CallQueuedHandler::class)->andReturn($handler);

        $job = $this->getShoveJob($container);

        $job->fire();
    }

    public function test_can_get_the_job_id()
    {
        $container = m::mock(Container::class);
        $job = $this->getShoveJob($container);

        $this->assertEquals('67ee8eef-7747-4614-80b6-d59375a5cf4e', $job->getJobId());
    }

    public function test_can_get_the_raw_body()
    {
        $container = m::mock(Container::class);
        $job = $this->getShoveJob($container);

        $this->assertEquals(json_encode([
            'job' => CallQueuedHandler::class,
            'data' => ['data'],
        ]), $job->getRawBody());
    }

    public function test_can_get_the_number_of_attempts()
    {
        $container = m::mock(Container::class);
        $job = $this->getShoveJob($container);

        $this->assertEquals(69, $job->attempts());
    }

    protected function getShoveJob($container): ShoveJob {

        return new ShoveJob(
            $container,
            (object) [
                'id' => '67ee8eef-7747-4614-80b6-d59375a5cf4e',
                'attempts' => 69,
                'body' => json_encode([
                    'job' => CallQueuedHandler::class,
                    'data' => ['data'],
                ]),
            ]
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }
}