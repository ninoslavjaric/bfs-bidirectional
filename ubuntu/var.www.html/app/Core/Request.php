<?php
namespace Htec\Core;

use Htec\Traits\SingletonTrait;

final class Request
{
    use SingletonTrait;

    private string $path;
    private array $pathParams;
    private array $headers;
    private array $params;

    public function getPath(): string
    {
        if (!isset($this->path)) {
            $uriFractions = explode('?', $_SERVER['REQUEST_URI']);
            $this->path = array_shift($uriFractions);
        }

        return $this->path;
    }

    public function getPathParams(): array
    {
        if (!isset($this->pathParams)) {
            $path = trim($this->getPath(), '/');
            $this->pathParams = explode('/', $path);
        }

        return $this->pathParams;
    }

    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    final private function getHeaders(): array
    {
        if (isset($this->headers)) {
            return $this->headers;
        }

        foreach (getallheaders() as $key => $header) {
            $this->headers[strtolower($key)] = $header;
        }

        return $this->headers;
    }

    final public function getHeader($key): string
    {
        $headers = $this->getHeaders();

        return $headers[$key] ?? '';
    }

    public function getToken(): string
    {
        return $this->getHeader('htec-token');
    }

    private function isJson(): bool
    {
        return $this->getMethod() != 'get' && $this->getHeader('content-type') == 'application/json';
    }

    private function isPlainText(): bool
    {
        return $this->getMethod() != 'get' && $this->getHeader('content-type') == 'text/plain';
    }

    public function getParams(): array
    {
        if (isset($this->params)) {
            return $this->params;
        }

        if ($this->isJson()) {
            $bodyContent = file_get_contents('php://input');
            $bodyContent = json_decode($bodyContent, true);

            $this->params = $bodyContent ?: [];
            return $this->params;
        }

        if ($this->isPlainText())
        {
            $bodyContent = file_get_contents('php://input');

            $this->params = empty($bodyContent) ? [] : ['text' => $bodyContent];
            return $this->params;
        }

        $this->params = array_merge($_GET, $_POST);

        return $this->params;
    }

    public function getParam($key)
    {
        $params = $this->getParams();

        return $params[$key] ?? null;
    }

    private function appendParams(&$params, $additionalData): void
    {
        foreach ($additionalData as $key => $value) {
            $params[$key] = $value;
        }
    }
}
