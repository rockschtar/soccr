<?php

namespace Rockschtar\WordPress\Soccr\Utils;

use Rockschtar\WordPress\Soccr\Exceptions\RemoteRequestException;
use Rockschtar\WordPress\Soccr\Models\RemoteResponse;

class RemoteRequest
{
    private string $method = 'GET';

    private string $url;

    private mixed $body = null;

    private ?string $message = null;

    private array $headers = [];

    private ?int $timeout = null;

    private ?string $userAgent = null;

    private ?bool $sslVerify = null;

    public function __construct(string $url)
    {
        $this->url = $url;
    }


    public static function request(string $method, string $url): RemoteRequest
    {
        $remoteRequest = new self($url);
        $remoteRequest->setMethod($method);
        return $remoteRequest;
    }

    public static function post(string $path): RemoteRequest
    {
        return self::request('POST', $path);
    }

    public static function put(string $path): RemoteRequest
    {
        return self::request('PUT', $path);
    }

    public static function delete(string $path): RemoteRequest
    {
        return self::request('DELETE', $path);
    }

    public static function get(string $path): RemoteRequest
    {
        return new self($path);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): RemoteRequest
    {
        $this->url = $url;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): RemoteRequest
    {
        $this->message = $message;
        return $this;
    }

    public function setTimeout(float $timeout): RemoteRequest
    {
        $this->timeout = $timeout;
        return $this;
    }


    public function setUserAgent(string $userAgent): RemoteRequest
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function setSslVerify(bool $sslVerify): RemoteRequest
    {
        $this->sslVerify = $sslVerify;
        return $this;
    }

    public function setBody(string $body): RemoteRequest
    {
        $this->body = $body;
        return $this;
    }


    public function setHeaders(array $headers): RemoteRequest
    {
        $this->headers = $headers;
        return $this;
    }

    public function addHeader(string $key, string $value): RemoteRequest
    {
        $this->headers[$key] = $value;
        return $this;
    }

    private function getArgs(): array
    {
        $args = [
            'method' => $this->method,
            'body' => $this->body,
            'headers' => $this->headers,
        ];

        if ($this->timeout) {
            $args['timeout'] = $this->timeout;
        }

        if ($this->userAgent) {
            $args['user-agent'] = $this->userAgent;
        }

        if ($this->sslVerify) {
            $args['sslverify'] = $this->sslVerify;
        }

        return $args;
    }

    public function execute(): RemoteResponse
    {
        $response = wp_remote_request($this->url, $this->getArgs());

        if (!$response) {
            throw new RemoteRequestException('Error when sending request');
        }

        if (is_wp_error($response)) {
            throw new RemoteRequestException(
                wp_remote_retrieve_response_message($response),
            );
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        $message = wp_remote_retrieve_response_message($response);
        $body = wp_remote_retrieve_body($response);

        if ($responseCode >= 200 && $responseCode < 300) {
            return new RemoteResponse($responseCode, $body);
        }

        throw new RemoteRequestException($message ?? $body, $responseCode);
    }

    /**
     * @param string $method
     * @return RemoteRequest
     */
    public function setMethod(string $method): RemoteRequest
    {
        $this->method = $method;
        return $this;
    }
}
