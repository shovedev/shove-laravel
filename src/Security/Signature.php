<?php

namespace Shove\Laravel\Security;

use Illuminate\Http\Request;

class Signature
{
    public function __construct(public string $signature, public string $signingSecret)
    {
    }

    public function isValid(Request $request): bool
    {
        if (empty($this->signingSecret)) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), $this->signingSecret);

        return hash_equals($this->signature, $computedSignature);
    }
}