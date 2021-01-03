<?php
namespace Htec\Core;


final class JsonResponse
{
    private array $data;

    public function __construct(array $data, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;

        http_response_code($statusCode);

        $headers['Content-type'] = 'application/json';
        $this->setHeaders($headers);
    }

    private function setHeaders(array $headers)
    {
        foreach ($headers as $headerKey => $headerValue) {
            header("{$headerKey}: {$headerValue}");
        }
    }

    public function __toString()
    {
        return json_encode($this->data);
    }
}
