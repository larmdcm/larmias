<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Protocols\Http;

use Larmias\WorkerS\Connections\TcpConnection;
use Larmias\WorkerS\Protocols\Http;
use Larmias\WorkerS\Support\Arr;

class Request
{
    /**
     * @var TcpConnection
     */
    protected TcpConnection $connection;

    /**
     * @var string
     */
    protected string $buffer;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Request __construct.
     * 
     * @param TcpConnection $connection
     * @param string        $buffer
     */
    public function __construct(TcpConnection $connection,string $buffer)
    {
        $this->connection = $connection;
        $this->buffer     = $buffer;
    }

    /**
     * get 'Header' request data.
     * 
     * @param string|null $key
     * @param mixed       $default
     * @return mixed
     */
    public function header(?string $key = null,$default = null)
    {
        if (!isset($this->data['headers'])) {
            $this->parseHeaders();
        }
        return Arr::get($this->data['headers'],$key,$default);
    }
    
    /**
     * get 'Get' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $key = null,$default = null)
    {
        if (!isset($this->data['get'])) {
            $this->parseGet();
        }
        return Arr::get($this->data['get'],$key,$default);
    }
    
    /**
     * get 'Post' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function post(?string $key = null,$default = null)
    {
        if (!isset($this->data['post'])) {
            $this->parsePost();
        }
        return Arr::get($this->data['post'],$key,$default);
    }

    /**
     * get Upload files.
     *
     * @param string|null $name
     * @return array|null
     */
    public function file(?string $name = null): ?array
    {
        if (!isset($this->data['files'])) {
            $this->parsePost();
        }
        if ($name === null) {
            return $this->data['files'];
        }
        return $this->data['files'][$name] ?? null;
    }

    /**
     * @return string
     */
    public function rawHeader(): string
    {
        if (!isset($this->data['raw_header'])) {
            $this->data['raw_header'] = \strstr($this->buffer, "\r\n\r\n", true);
        }
        return $this->data['raw_header'];
    }
    
    /**
     * Get http raw body.
     *
     * @return string
     */
    public function rawBody(): string
    {
        if (!isset($this->data['raw_body'])) {
            $this->data['raw_body'] = \substr($this->buffer, \strpos($this->buffer, "\r\n\r\n") + 4);
        }
        return $this->data['raw_body'];
    }


    /**
     * @return void
     */
    protected function parseHeaders(): void
    {
        $this->data['headers'] = [];
        $rawHeader = $this->rawHeader();
        $pos = \strpos($rawHeader,"\r\n");
        if ($pos === false) {
            return;
        }

        $headerBuffer = \substr($rawHeader,$pos + 2);
        $list = \explode("\r\n",$headerBuffer);

        foreach ($list as $item) {
            if (empty($item) || strpos($item,': ') === false) {
                continue;
            }
            [$key,$value] = explode(": ",$item,2);
            $key = strtolower($key);
            $this->data['headers'][$key] = rtrim($value);
        }
    }
    

    /**
     * @return void
     */
    protected function parseFirstHeader(): void
    {
        $firstLine = \strstr($this->buffer, "\r\n", true);
        $result    = \explode(' ', $firstLine, 3);
        $this->data['method'] = $result[0];
        $this->data['uri']    = $result[1] ?? '/';
        $this->data['schema'] = $result[2] ?? '';
    }

    /**
     * @return string
     */
    public function method(): string
    {
        if (!isset($this->data['method'])) {
            $this->parseFirstHeader();
        }
        return $this->data['method'];
    }
    
    /**
     * @return string
     */
    public function uri(): string
    {
        if (!isset($this->data['uri'])) {
            $this->parseFirstHeader();
        }
        return $this->data['uri'];
    }

    /**
     * @return string
     */
    public function schema(): string
    {
        if (!isset($this->data['schema'])) {
            $this->parseFirstHeader();
        }
        return $this->data['schema'];
    }

    /**
     * @return string
     */
    public function queryString(): string
    {
        if (!isset($this->data['query_string'])) {
            $this->data['query_string'] = parse_url($this->uri(),PHP_URL_QUERY);
        }
        return $this->data['query_string'] ?: '';
    }

    /**
     * @return void
     */
    protected function parseGet(): void
    {
        $this->data['get'] = [];
        $queryString = $this->queryString();
        if ($queryString === '') {
            return;
        }
        parse_str($queryString,$this->data['get']);
    }


    /**
     * @return void
     */
    protected function parsePost(): void
    {
        $this->data['post'] = $this->data['files'] = [];
        $rawBody = $this->rawBody();
        if ($rawBody === '') {
            return;
        }
        $contentType = $this->header('content-type','');
        if (preg_match('/boundary="?(\S+)"?/',$contentType,$matches)) {
            $boundary = "--" . $matches[1];
            $this->parseFormData($boundary,$rawBody);
            return;
        }

        if (\preg_match('/\bjson\b/i', $contentType)) {
            $this->data['post'] = \json_decode($rawBody,true);
        } else {
            \parse_str($rawBody,$this->data['post']);
        }
    }   

    /**
     * parse form data.
     *
     * @param string $boundary
     * @param string $rawBody
     * @return void
     */
    protected function parseFormData(string $boundary,string $rawBody): void
    {
        $boundary = \trim($boundary,'"');
        $rawBody  = \substr($rawBody,0,\strlen($rawBody) - (\strlen($boundary) + 4));
        $formData = \explode($boundary . "\r\n",$rawBody);
        if ($formData[0] === '' || $formData[0] === "\r\n") {
            unset($formData[0]);
        }
        $index    = -1;
        $files    = [];
        $postStr  = '';

        foreach ($formData as $dataBuffer) {
            [$bufferHeader,$bufferValue] = explode("\r\n\r\n",$dataBuffer,2);
            $bufferValue = \substr($bufferValue,0,-2);
            $index++;
            foreach (\explode("\r\n",$bufferHeader) as $item) {
                [$headerKey,$headerValue] = explode(": ",$item);
                $headerKey = \strtolower($headerKey);
                switch ($headerKey) {
                    case 'content-disposition':
                        if (\preg_match('/name="(.*?)"; filename="(.*?)"/i', $headerValue, $match)) {
                            $error = 0;
                            $tmpFile = '';
                            $size = \strlen($bufferValue);
                            $tmpUploadDir = Http::uploadTmpDir();
                            if (!$tmpUploadDir) {
                                $error = \UPLOAD_ERR_NO_TMP_DIR;
                            } else if ($bufferValue === '') {
                                $error = \UPLOAD_ERR_NO_FILE;
                            } else {
                                $tmpFile = \tempnam($tmpUploadDir, 'worker-s.upload.');
                                if ($tmpFile === false || false == \file_put_contents($tmpFile, $bufferValue)) {
                                    $error = \UPLOAD_ERR_CANT_WRITE;
                                }
                            }
                            if (!isset($files[$index])) {
                                $files[$index] = [];
                            }
                            $files[$index] += [
                                'key'      => $match[1],
                                'name'     => $match[2],
                                'tmp_name' => $tmpFile,
                                'size'     => $size,
                                'error'    => $error,
                                'type'     => null,
                            ];
                        } else {
                            if (\preg_match('/name="(.*?)"$/', $headerValue, $match)) {
                                $postStr .= \urlencode($match[1]) . "=" . \urlencode($bufferValue) . '&';
                            }
                        }
                        break;
                    case 'content-type':
                        if (!isset($files[$index])) {
                            $files[$index] = [];
                        }
                        $files[$index]['type'] = \trim($headerValue);
                        break;
                }
            }
        }
        $unqFiles = [];
        foreach ($files as $index => $file) {
            $key = $file['key'];
            if (\substr($key, -2) === '[]') {
                $key = $index;
            }
            $unqFiles[$key] = $file;
        }

        foreach ($unqFiles as $key => $file) {
            $key = $file['key'];
            unset($file['key']);
            $str = \urlencode($key) . "=1";
            $result = [];
            \parse_str($str, $result);
            \array_walk_recursive($result, function (&$value) use ($file) {
                $value = $file;
            });
            $this->data['files'] = \array_merge_recursive($this->data['files'], $result);
        }

        if ($postStr) {
            \parse_str($postStr, $this->data['post']);
        }
    }
}