<?php

namespace Rockschtar\WordPress\Soccr\Models;

class RemoteResponse
{
    private int $status;

    private string $body;

    /**
     * RemoteResponse constructor.
     * @param int $status
     * @param string $body
     */
    public function __construct(int $status = 200, string $body = '')
    {
        $this->status = $status;
        $this->body = $body;
    }

    public function isError(): bool
    {
        return !($this->getStatus() >= 200 && $this->getStatus() < 300);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return RemoteResponse
     */
    public function setStatus(int $status): RemoteResponse
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return RemoteResponse
     */
    public function setBody(string $body): RemoteResponse
    {
        $this->body = $body;
        return $this;
    }
}
