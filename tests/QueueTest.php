<?php

namespace Shove\Laravel\Tests;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Testing\Constraints\ArraySubset;
use Mockery as m;
use Shove\Connector\ShoveConnector;
use Shove\Laravel\Queue\ShoveJob;
use Shove\Laravel\Queue\ShoveQueue;

class QueueTest extends TestCase
{
    public function testFireProperlyCallsTheJobHandler()
    {
        $job = $this->getJob();
        $job->getContainer()->shouldReceive('make')->once()->with('foo')->andReturn($handler = m::mock('StdClass'));
        $handler->shouldReceive('fire')->once()->with($job, ['data']);

        $job->fire();
    }

    public function testDeleteRemovesTheJobFromIron()
    {
        $job = $this->getJob();
//        $job->getIron()->shouldReceive('deleteMessage')->once()->with('default', 1, 2);

        $job->delete();
    }

//    public function testDeleteNoopsOnPushedQueues()
//    {
//        $job = new Collective\IronQueue\Jobs\IronJob(
//            m::mock('Illuminate\Container\Container'),
//            m::mock('Collective\IronQueue\IronQueue'),
//            (object) ['id' => 1, 'body' => json_encode(['job' => 'foo', 'data' => ['data']]), 'timeout' => 60, 'pushed' => true],
//            'default'
//        );
//        $job->getIron()->shouldReceive('deleteMessage')->never();
//
//        $job->delete();
//    }
//
//    public function testReleaseProperlyReleasesJobOntoIron()
//    {
//        $job = $this->getJob();
//        $job->getIron()->shouldReceive('deleteMessage')->once();
//        $job->getIron()->shouldReceive('recreate')->once()->with(json_encode(['job' => 'foo', 'data' => ['data'], 'attempts' => 2, 'queue' => 'default']), 'default', 5);
//
//        $job->release(5);
//    }

    protected function getJob()
    {
        return new ShoveJob(
            m::mock('Illuminate\Container\Container'),
            (object) [
                'id' => 1, 'reservation_id' => 2,
                'body' => json_encode([
                    'job' => 'foo',
                    'data' => ['data'],
                    'attempts' => 1,
                    'queue' => 'default'
                ]),
                'timeout' => 60
            ]
        );
    }

    public function test_push_properly_pushes_job_onto_shove_queue()
    {
        $shoveMock = m::mock(ShoveConnector::class);
        $shoveMock->shouldReceive('send')->once()->withArgs(function ($request) {
            return $request->getData()->queue === 'default'
                && $request->getData()->headers === []
                && (new ArraySubset([
                    'displayName' => 'foo',
                    'job' => 'foo',
                    'backoff' => null,
                ]))->evaluate($request->getData()->body, '', true);
        });

        $mockRequest = m::mock(Request::class);

        $queue = new ShoveQueue($shoveMock, $mockRequest);
        $queue->setContainer(m::mock(Container::class)->shouldReceive('bound')->times(2)->getMock());
        $queue->push('foo', ['boo' => 'baz']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }
}