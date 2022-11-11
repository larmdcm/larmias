<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Protocols;

use Larmias\WorkerS\Connections\Connection;
use Larmias\WorkerS\Connections\TcpConnection;
use Larmias\WorkerS\Protocols\Http\Request;
use Larmias\WorkerS\Protocols\Http\Response;
use Larmias\WorkerS\Manager;

class Http implements ProtocolInterface
{
    /**
     * @var string
     */
    protected static string $uploadTmpDir = '';

    /**
     * @var string|null
     */
    protected static ?string $requestClass = null;

    /**
     * @var string|null
     */
    protected static ?string $responseClass = null;

    /**
     * @param string     $data
     * @param Connection $connection
     * @return int
     */
    public static function input(string $data,?Connection $connection): int
    {
        $pos = \strpos($data,"\r\n\r\n");
        if ($pos === false) {
            return 0;
        }
        $dataLen   = \strlen($data);
        $headerLen = $pos + 4;
        $bodyLen   = 0;
        if (preg_match("/\r\nContent-Length: ?(\d+)/i",$data,$matches)) {
            $bodyLen = intval($matches[1]);
        }
        $totalLen = $headerLen + $bodyLen;
        if ($dataLen > $totalLen) {
            return 0;
        }
        return $totalLen;
    }

    /**
     * @param string $data
     * @param Connection $connection
     * @return mixed
     */
    public static function encode(string $data,?Connection $connection): string
    {
        return $data;
    }

    /**
     * @param string     $data
     * @param Connection $connection
     * @return Request
     */
    public static function decode(string $data,?Connection $connection): Request
    {
        $request = static::createRequest($connection,$data);
        $connection->request = $request;
        return $request;
    }

    /**
     * Set or get uploadTmpDir.
     *
     * @param string|null $dir
     * @return string
     */
    public static function uploadTmpDir(?string $dir = null): string
    {
        if ($dir !== null) {
            static::$uploadTmpDir = $dir;
        } else if (static::$uploadTmpDir === '') {
            if ($uploadTmpDir = \ini_get('upload_tmp_dir')) {
                static::$uploadTmpDir = $uploadTmpDir;
            } else if ($uploadTmpDir = \sys_get_temp_dir()) {
                static::$uploadTmpDir = $uploadTmpDir;
            }
        }
        return static::$uploadTmpDir;
    }

    /**
     * 创建请求对象
     *
     * @param TcpConnection $connection
     * @param string $data
     * @return Request
     */
    public static function createRequest(TcpConnection $connection,string $data): Request
    {
        try {
            if (static::$requestClass !== null) {
                return new static::$requestClass($connection,$data);
            }
        } catch (\Throwable $e) {
            Manager::stopAll($e->getMessage());
            throw $e;
        }
        return new Request($connection,$data);
    }

    /**
     * 创建响应对象
     *
     * @param TcpConnection $connection
     * @return Response
     */
    public static function createResponse(TcpConnection $connection): Response
    {
        if (static::$responseClass !== null) {
            return new static::$responseClass($connection);
        }
        return new Response($connection);
    }

    /**
     * Get the value of requestClass
     *
     * @return  string|null
     */
    public static function getRequestClass(): ?string
    {
        return static::$requestClass;
    }
    /**
     * Set the value of requestClass
     *
     * @param  string|null $requestClass  
     * @return void
     */
    public static function setRequestClass(?string $requestClass): void
    {
        static::$requestClass = $requestClass;
    }

    /**
     * Get the value of responseClass
     *
     * @return  string|null
     */
    public static function getResponseClass(): ?string
    {
        return static::$responseClass;
    }
    /**
     * Set the value of responseClass
     *
     * @param  string|null  $responseClass  
     * @return void
     */
    public static function setResponseClass(?string $responseClass): void
    {
        static::$responseClass = $responseClass;
    }
}