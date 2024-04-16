<?php

namespace Shove\Laravel\Tests;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Testing\Constraints\ArraySubset;
use Mockery as m;
use Shove\Connector\ShoveConnector;
use Shove\Laravel\Queue\ShoveJob;
use Shove\Laravel\Queue\ShoveQueue;
use Shove\Laravel\Security\Signature;

class SignatureTest extends TestCase
{
    public function test_a_signature_can_be_validated()
    {
        $signer = new Signature('f9e66e179b6747ae54108f82f8ade8b3c25d76fd30afde6c395822c530196169', 'secret');

        $request = m::mock(Request::class);
        $request->shouldReceive('getContent')->andReturn('');

        $this->assertTrue($signer->isValid($request));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }
}