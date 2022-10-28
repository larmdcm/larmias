<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Protocols;

use Larmias\WorkerS\Connections\Connection;
use Larmias\WorkerS\Connections\TcpConnection;
use Larmias\WorkerS\Server;

class WebSocket implements ProtocolInterface
{
    /**
     * @param string        $data
     * @param TcpConnection|null $connection
     * @return int
     */
    public static function input(string $data,?Connection $connection): int
    {
        $recvLen = \strlen($data);
        if ($recvLen < 6) {
            return 0;
        }

        if (!isset($connection->webSocketHandShake)) {
            return Http::input($data,$connection);
        }
        $firstByte  = \ord($data[0]);
        $secondByte = \ord($data[1]);
        $isFin      = ($firstByte & 0x80) == 0x80;
        $opcode     = $firstByte & 0x0F;
        switch ($opcode) {
            case 0x0:
                break;
            // Blob type.
            case 0x01:
                break;
            // Arraybuffer type.
            case 0x02:
                break;
            // Close package.
            case 0x08:
                $connection->close("\x88\x02\x03\xe8", true);
                return 0;
            // Ping package.
            case 0x09:
                break;
            // Pong package.
            case 0xa:
                break;
            // Wrong opcode.
            default :
                $connection->close();
                return 0;
        }
        $mask = ($secondByte & 0x80) == 0x80;
        if (!$mask) {
            $connection->close();
            return 0;
        }
        $headLen = 6;
        $payloadLen = $secondByte & 0x7F;
        if ($payloadLen == 126) {
            $headLen = 8;
        } else if ($payloadLen == 127) {
            $headLen = 14;
        }
        if ($recvLen < $headLen) {
            return 0;
        }
        $dataLen = 0;
        if ($payloadLen == 126) {
            $dataLen |= \ord($data[2])<<8;
            $dataLen |= \ord($data[3])<<0;
        } else if ($payloadLen == 127) {
            $dataLen |= \ord($data[2])<<56;
            $dataLen |= \ord($data[3])<<48;
            $dataLen |= \ord($data[4])<<40;
            $dataLen |= \ord($data[5])<<32;
            $dataLen |= \ord($data[6])<<24;
            $dataLen |= \ord($data[7])<<16;
            $dataLen |= \ord($data[8])<<8;
            $dataLen |= \ord($data[9])<<0;
        } else {
            $dataLen = $payloadLen;
        }
        $totalLen = $headLen + $dataLen;
        if ($recvLen < $totalLen) {
            return 0;
        }
        
        $connection->isFin   = $isFin;
        $connection->headLen = $headLen;
        $connection->dataLen = $dataLen;
        $maskKey    = [];
        $maskKey[0] = $data[$headLen - 4];
        $maskKey[1] = $data[$headLen - 3];
        $maskKey[2] = $data[$headLen - 2];
        $maskKey[3] = $data[$headLen - 1];
        $connection->maskKey = $maskKey;
        return $totalLen;
    }

    /**
     * @param string $data
     * @param TcpConnection|null $connection
     * @return mixed
     */
    public static function encode(string $data,?Connection $connection): mixed
    {
        if (!isset($connection->webSocketHandShake)) {
            $handShakeData = static::handShake($connection);
            if ($handShakeData === null) {
                $connection->close();
                return '';
            }
            return Http::encode($handShakeData,$connection);
        }
        $dataLen   = \strlen($data);
        $firstByte = 0x80 | 0x01;
        if ($dataLen < 126) {
            $frame = \chr($firstByte) . \chr($dataLen) . $data;
        } else if ($dataLen < 65536) {
            $frame = \chr($firstByte) . \chr(126) . \chr($dataLen >> 8 & 0xFF) . \chr($dataLen >> 0 & 0xFF) . $data;
        } else {
            $frame = \chr($firstByte) . \chr(127) 
            . \chr($dataLen >> 56 & 0xFF) 
            . \chr($dataLen >> 48 & 0xFF) 
            . \chr($dataLen >> 40 & 0xFF) 
            . \chr($dataLen >> 32 & 0xFF) 
            . \chr($dataLen >> 24 & 0xFF) 
            . \chr($dataLen >> 16 & 0xFF) 
            . \chr($dataLen >> 8 & 0xFF) 
            . \chr($dataLen >> 0 & 0xFF) 
            . $data;
        }
        return $frame;
    }

    /**
     * @param string        $data
     * @param TcpConnection|null $connection
     * @return mixed
     */
    public static function decode(string $data,?Connection $connection): mixed
    {
        if (!isset($connection->webSocketHandShake)) {
            return Http::decode($data,$connection);
        }
        if (!isset($connection->headLen) || !isset($connection->dataLen) || !isset($connection->maskKey)) {
            return null;
        }
        $buffer = \substr($data,$connection->headLen);
        if (\strlen($buffer) < $connection->dataLen) {
            return null;
        }
        for ($i = 0; $i < $connection->dataLen; $i++) {
            $buffer[$i] = $buffer[$i] ^ $connection->maskKey[$i & 0b00000011];
        }

        return $buffer;
    }

    /**
     * @param  TcpConnection $connection
     * @return string|null
     */
    public static function handShake(TcpConnection $connection): ?string
    {
        if (!isset($connection->request)) {
            return null;
        }
        /** @var Http\Request */
        $request = $connection->request;
        $headers = $request->header();
        if (isset($headers['connection']) && $headers['connection'] === 'Upgrade' && isset($headers['upgrade']) && $headers['upgrade'] == 'websocket') {
            $websocketKey = $headers['sec-websocket-key'] ?? '';
            if ($websocketKey) {
                $acceptKey = \base64_encode(\sha1($websocketKey."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
                $message   = "HTTP/1.1 101 Switching Protocols\r\n";
                $message  .= "Upgrade: websocket\r\n";
                $message  .= "Sec-WebSocket-Version: 13\r\n";
                $message  .= "Connection: Upgrade\r\n";
                $message  .= \sprintf("Sec-WebSocket-Accept: %s\r\n",$acceptKey);
                $message  .= "Server: worker-s/" . Server::VERSION . "\r\n";
                $message  .= "\r\n";
                return $message;
            }
        }
        return null;
    }
}