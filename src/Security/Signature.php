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
        if (!$this->signature) {
            return false;
        }

        if (empty($this->signingSecret)) {
            throw new \RuntimeException('The Shove.dev signing secret is not set in your config.');
        }

        $computedSignature = $this->computedSignature($request->getContent(), $this->signingSecret);

        return hash_equals($this->signature, $computedSignature);
    }

    public function computedSignature($value, $secret)
    {
        return hash_hmac('sha256', $value, $this->signingSecret);
    }
}