<?php

namespace Shove\Laravel\Queue;

use Illuminate\Http\Request;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Queue\Queue;
use Shove\Connector\ShoveConnector as Shove;

class ShoveConnector implements ConnectorInterface
{
    public function __construct(protected Request $request, protected Shove $client)
    {
    }

    public function connect(array $config): Queue
    {
        return new ShoveQueue(
            $this->client,
            $this->request,
            $config['default_queue']
        );
    }
}