<?php

namespace Rockschtar\Soccr\Models;

class RemoteResponse
{
    private int $status;

    private string $body;

    public function __construct(int $status = 200, string $body = '')
    {
        $this->status = $status;
        $this->body = $body;
    }

    public function isError(): bool
    {
        return !($this->getStatus() >= 200 && $this->getStatus() < 300);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): RemoteResponse
    {
        $this->status = $status;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): RemoteResponse
    {
        $this->body = $body;
        return $this;
    }
}
