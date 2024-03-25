<?php

namespace Shove\Laravel\Queue;

use Illuminate\Http\Request;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Config;
use Shove\Connector\ShoveConnector as ShoveHttpClient;

class ShoveConnector implements ConnectorInterface
{
    public function __construct(protected Request $request)
    {
    }

    public function connect(array $config): Queue
    {
        return new ShoveQueue(
            new ShoveHttpClient(
                token: Config::get('shove.secret', ''),
                baseUrl: Config::get('shove.api_url')
            ),
            $this->request,
            Config::get('shove.default_queue')
        );
    }
}