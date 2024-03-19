<?php

namespace Shove\Laravel\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Config;
use Shove\Laravel\Security\Signature;

class WebhookController
{
    public function __invoke(Request $request, QueueManager $manager)
    {
        $this->ensureValidSignature($request);

        $connection = $manager->connection('shove');

        return $connection->marshal();
    }

    protected function ensureValidSignature(Request $request): void
    {
        $signature = new Signature(
            $request->header('Shove-Signature'), Config::get('shove.signing_secret')
        );

        abort_unless($signature->isValid($request), 403, 'Invalid signature');
    }
}