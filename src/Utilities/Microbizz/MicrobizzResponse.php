<?php

namespace JoelGrondrup\Microbizz;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * The response from a Microbizz API call.
 *
 * Shaped like the Httpful response this module used to return, so existing callers keep
 * working: $response->body is the JSON decoded to objects, and casting the response to a
 * string gives the raw JSON.
 */
class MicrobizzResponse {

    public $body;

    public string $raw_body;

    public int $code;

    public array $headers;

    public function __construct(ResponseInterface $response) {

        $this->raw_body = (string)$response->getBody();
        $this->code = $response->getStatusCode();
        $this->headers = $response->getHeaders();

        $this->body = null;

        if (trim($this->raw_body) !== '') {

            $this->body = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', $this->raw_body));

            if ($this->body === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Unable to parse response as JSON: ' . json_last_error_msg());
            }

        }

    }

    public function __toString(): string {

        return $this->raw_body;

    }

}
