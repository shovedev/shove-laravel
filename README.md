# Shove.dev Laravel Package

The official Laravel integration for [Shove.dev](https://shove.dev), a cloud message queue service designed for simplicity and productivity. This package provides seamless integration with Laravel's queue system, allowing you to use Shove.dev as a queue driver.

## Installation

Install the package via Composer:

```bash
composer require shovedev/shove-laravel
```

## Configuration

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag=shove-config
```

This will create a `config/shove.php` file with the following content:

```php
<?php

return [
    'secret' => env('SHOVE_API_TOKEN', ''),

    'signing_secret' => env('SHOVE_SIGNING_SECRET', ''),

    'default_queue' => env('SHOVE_DEFAULT_QUEUE', 'default'),

    'api_url' => env('SHOVE_API_URL', 'https://shove.dev/api'),
];
```

Add the following variables to your `.env` file:

```
SHOVE_API_TOKEN=your-api-token
SHOVE_SIGNING_SECRET=your-signing-secret
QUEUE_CONNECTION=shove
```

## Service Provider

The package's service provider is automatically registered thanks to Laravel's package auto-discovery feature. It registers the queue driver and sets up the necessary configuration.

## Features

### 1. Using Shove as a Queue Driver

Once configured, you can use Laravel's queue system as you normally would, and the jobs will be processed by Shove.dev.

```php
// Dispatch a job as usual
YourJob::dispatch($someData);

// Or dispatch with a specific queue
YourJob::dispatch($someData)->onQueue('emails');
```

### 2. The Shove Facade

The package provides a `Shove` facade that gives you direct access to the Shove.dev API client:

```php
use Shove\Laravel\Facades\Shove;
use Shove\Enums\QueueType;

// Create a new queue
Shove::queues()->create(
    name: 'emails',
    type: QueueType::Unicast
);

// Delete a queue
Shove::queues()->delete(name: 'emails');

// Create a job directly
Shove::jobs()->create(
    queue: 'emails',
    body: [
        'to' => 'user@example.com',
        'subject' => 'Hello!',
        'content' => 'This is a test email.'
    ]
);

// Get a job by ID
$job = Shove::jobs()->get(id: 'job-id');
```

### 3. Setting Up Webhook Routes

To process jobs, you need to set up a webhook endpoint that Shove.dev can call. The package makes this easy:

```php
// In your routes/web.php file
use Shove\Laravel\Facades\Shove;

Shove::routes(); // This creates a POST /shove endpoint
// or
Shove::routes('/custom-webhook-path'); // For a custom path
```

This automatically:

- Creates the webhook route
- Configures CSRF protection to ignore the webhook path
- Sets up signature verification for security

### 4. Securing Webhooks

The package automatically verifies that incoming webhook requests are actually from Shove.dev using your signing secret:

```php
// This happens automatically when a webhook is received
$signature = new Signature(
    $request->header('Shove-Signature'),
    config('shove.signing_secret')
);

abort_unless($signature->isValid($request), 403, 'Invalid signature');
```

## Queue Types

Shove.dev supports two types of queues:

1. **Unicast Queues**: Each message is consumed by a single worker (traditional queues)
2. **Multicast Queues**: Each message is consumed by all workers (pub/sub model)

```php
use Shove\Laravel\Facades\Shove;
use Shove\Enums\QueueType;

// Create a unicast queue (traditional queue)
Shove::queues()->create(
    name: 'emails',
    type: QueueType::Unicast
);

// Create a multicast queue (pub/sub)
Shove::queues()->create(
    name: 'notifications',
    type: QueueType::Multicast
);
```

## Testing

The package automatically switches to the synchronous queue driver in the testing environment, making it easy to test your queue jobs without setting up a mock for the Shove client.

## Error Handling

The package includes error handling for common scenarios:

- Missing signing secret: `RuntimeException` with a message indicating the config issue
- Invalid webhook signature: 403 Forbidden response
- Failed job creation: Exception with the status code from Shove.dev API

## Example Usage

### Creating and Handling Jobs

#### 1. Create a Job

```php
// app/Jobs/SendWelcomeEmail.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $email)
    {
    }

    public function handle()
    {
        // Logic to send welcome email
    }
}
```

#### 2. Dispatch the Job

```php
// In your controller
use App\Jobs\SendWelcomeEmail;

SendWelcomeEmail::dispatch($user->email);
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
