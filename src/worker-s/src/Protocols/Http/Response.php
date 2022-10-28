<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Protocols\Http;

use Larmias\WorkerS\Connections\TcpConnection;
use Larmias\WorkerS\Server;
use RuntimeException;

class Response
{
    /** @var int  */
    public const SEND_MAX_FILE_SIZE = 2097152;

    /**
     * @var TcpConnection
     */
    protected TcpConnection $connection;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var string
     */
    protected string $version = '1.1';

    /**
     * @var integer
     */
    protected int $statusCode = 200;

    /**
     * @var string|null
     */
    protected ?string $reason = null;

    /**
     * @var boolean
     */
    protected bool $isSendChunk = false;

    /**
     * @var boolean
     */
    public bool $isSendEnd = false;

    /**
     * Phrases.
     *
     * @var array
     */
    protected static array $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-statusCode',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /**
     * Response __construct.
     * 
     * @param TcpConnection $connection
     */
    public function __construct(TcpConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * set Header.
     *
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function header(string $name,$value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * @param array $headers
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = \array_merge_recursive($this->headers,$headers);
        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function withoutHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getHeader(string $name,mixed $default = null): mixed
    {
        return $this->headers[$name] ?? $default;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 设置响应状态码
     *
     * @param integer     $statusCode
     * @param string|null $reason
     * @return self
     */
    public function status(int $statusCode,?string $reason = null): self
    {
        $this->statusCode = $statusCode;
        $this->reason     = $reason;
        return $this;
    }

    /**
     * 获取响应状态码
     *
     * @return integer
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * 分块传输数据
     * 
     * @param string $data
     * @return self
     */
    public function write(string $data): self
    {
        if (!$this->isSendChunk) {
            $this->isSendChunk = true;
            if (!$this->hasHeader('Transfer-Encoding')) {
                $this->header('Transfer-Encoding','chunked');
            }
            $content = $this->content($data);
        } else {
            $content = \dechex(strlen($data)) . "\r\n" . $data . "\r\n";
        }
        $this->connection->send($content);
        return $this;
    }
    
    /**
     * 发送文件.
     *
     * @param string $file
     * @param int    $offset
     * @param int    $length
     * @return void
     */
    public function sendFile(string $file,int $offset = 0,int $length = 0): void
    {
        if (!\is_file($file) || !\is_readable($file)) {
            $this->status(404)->end('<h3>404 Not Found</h3>');
            return;
        }
        
        $fileInfo  = \pathinfo($file);
        $extension = $fileInfo['extension'] ?? '';
        $basename  = $fileInfo['basename'] ?? 'unknown';

        $mimeType = MimeType::fromExtension($extension);
        
        if (!$this->hasHeader('Content-Type')) {
            $this->header('Content-Type',$mimeType ?: 'application/octet-stream');
        }
        
        if (!$this->hasHeader('Content-Disposition') && !$mimeType) {
            $this->header('Content-Disposition','attachment; filename="'. $basename .'"');
        }

        if (!$this->hasHeader('Last-Modified')) {
            $mtime = \filemtime($file);
            if ($mtime) {
                $this->header('Last-Modified',\gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
            }
        }

        $fileSize = (int)\filesize($file);
        $bodyLen  = $length > 0 ? $length : $fileSize - $offset;
        $this->withHeaders([
            'Content-Length' => $bodyLen,
            'Accept-Ranges'  => 'bytes',
        ]);
        if ($offset || $length) {
            $offsetEnd = $offset + $bodyLen - 1;
            $this->header('Content-Range', "bytes $offset - $offsetEnd / $fileSize");
        }
        if ($bodyLen <= self::SEND_MAX_FILE_SIZE) {
            $this->end(file_get_contents($file, false, null, $offset, $bodyLen));
            return;
        }
    }

    /**
     * 结束请求.
     * 
     * @param string $data
     * @return void
     */
    public function end(string $data = ''): void
    {
        if ($this->isSendEnd) {
            throw new RuntimeException('Http sending has ended and cannot be repeated');
        }
        if ($this->isSendChunk) {
            $data && $this->write($data);
            $content = "0\r\n\r\n";
        } else {
            $content = $this->content($data);
        }

        $this->isSendEnd = true;
        $this->connection->send($content);

        if ($this->isCloseConnection()) {
            $this->connection->close();
        }
    }

    /**
     * @return string
     */
    protected function content(string $body): string
    {
        $bodyLen = \strlen($body);
        $headers = $this->headers;
        $header  = sprintf("HTTP/%s %d %s\r\n",$this->version,$this->statusCode,$this->reason ?: static::$phrases[$this->statusCode]);
        
        if (!isset($headers['Server'])) {
            $headers['Server'] = 'worker-s/' . Server::VERSION;
        }
        if (!isset($headers['Connection'])) {
            $headers['Connection'] = $this->connection->request->header('connection','close');
        }

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'text/html;charset=utf-8';
        }

        if (!isset($headers['Content-Length'])) {
            $headers['Content-Length'] = $bodyLen;
        }

        foreach ($headers as $name => $value) {
            if (\is_array($value)) {
                foreach ($value as $item) {
                    $header .= "$name: $item\r\n";
                }
                continue;
            }
            $header .= "$name: $value\r\n";
        }

        if ($headers['Content-Type'] === 'text/event-stream') {
            return $header . $body;
        }
                 
        if (isset($headers['Transfer-Encoding'])) {
            return "$header\r\n" . dechex($bodyLen) . "\r\n{$body}\r\n";
        }
        
        return $header . "\r\n" . $body;
    }

    /**
     * @return boolean
     */
    protected function isCloseConnection(): bool
    {
        return strtolower($this->connection->request->header('connection','close')) === 'close';
    }
}