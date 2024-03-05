<?php

namespace Shove\Laravel\Queue;

use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

class ShoveJob extends Job implements JobContract
{
    public function __construct(
        public $container,
        public $job
    ) {
    }

    public function getJobId()
    {
        return $this->job->id;
    }

    public function getRawBody()
    {
        return $this->job->body;
    }

    public function attempts()
    {
        return $this->job->attempts;
    }
}