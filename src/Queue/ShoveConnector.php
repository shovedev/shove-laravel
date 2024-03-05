<?php

namespace Shove\Laravel\Queue;

use Illuminate\Http\Request;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Queue\Queue;
use Shove\ShoveConnector as ShoveHttpClient;

class ShoveConnector implements ConnectorInterface
{
    public function __construct(protected Request $request)
    {
    }

    public function connect(array $config): Queue
    {
        return new ShoveQueue(
            new ShoveHttpClient(
                config('shove.secret', ''),
                config('shove.api_url')
            ),
            $this->request,
            config('shove.default_queue')
        );
    }
}