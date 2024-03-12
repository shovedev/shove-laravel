<?php

namespace Shove\Laravel\Tests;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Testing\Constraints\ArraySubset;
use Mockery as m;
use Shove\Laravel\Queue\ShoveQueue;
use Shove\ShoveConnector;

class QueueTest extends TestCase
{
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