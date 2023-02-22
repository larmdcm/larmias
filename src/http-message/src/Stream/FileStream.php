<?php

declare(strict_types=1);

namespace Larmias\Http\Message\Stream;

use Larmias\Contracts\FileInterface;
use Larmias\Http\Message\Exceptions\FileException;
use Psr\Http\Message\StreamInterface;
use Stringable;
use Throwable;
use SplFileInfo;
use BadMethodCallException;

class FileStream implements StreamInterface, Stringable, FileInterface
{
    protected SplFileInfo $file;

    public function __construct(string|SplFileInfo $file)
    {
        if (!$file instanceof SplFileInfo) {
            $file = new SplFileInfo($file);
        }
        if (!$file->isReadable()) {
            throw new FileException('File must be readable.');
        }

        $this->file = $file;
    }

    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (Throwable $e) {
            return '';
        }
    }

    public function close()
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function detach()
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getSize(): int
    {
        return $this->file->getSize();
    }

    public function tell()
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function eof()
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function isSeekable()
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function rewind()
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function isWritable()
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function write($string)
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function isReadable()
    {
        return true;
    }

    public function read($length)
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getContents(): string
    {
        return $this->getFilename();
    }

    public function getMetadata($key = null)
    {
        // TODO: Implement getMetadata() method.
    }

    public function getFilename(): string
    {
        return $this->file->getPathname();
    }
}