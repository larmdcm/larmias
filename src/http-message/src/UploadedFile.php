<?php

declare(strict_types=1);

namespace Larmias\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use SplFileInfo;
use Stringable;
use RuntimeException;
use InvalidArgumentException;
use function fopen;
use function json_encode;
use function explode;
use function is_string;
use function in_array;
use function gettype;
use function is_int;
use function dirname;
use function is_dir;
use function mkdir;
use function php_sapi_name;
use function rename;
use function move_uploaded_file;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_PARTIAL;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;

class UploadedFile extends SplFileInfo implements UploadedFileInterface, Stringable
{
    /**
     * @var int[]
     */
    protected static array $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    /**
     * @var StreamInterface
     */
    protected StreamInterface $stream;

    /**
     * @var int
     */
    protected int $error;

    /**
     * @var bool
     */
    protected bool $moved = false;

    /**
     * @var string|null
     */
    protected ?string $mimeType = null;

    /**
     * @var string
     */
    protected string $file;

    /**
     * @var int
     */
    protected int $size;

    /**
     * @var string|null
     */
    protected ?string $clientFilename;

    /**
     * @var string|null
     */
    protected ?string $clientMediaType;

    /**
     * UploadedFile constructor.
     *
     * @param string $file
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(string $file, ?int $size, int $error, ?string $clientFilename = null, ?string $clientMediaType = null)
    {
        parent::__construct($file);
        $this->setFile($file)
            ->setError($error)
            ->setSize($size)
            ->setClientFilename($clientFilename)
            ->setClientMediaType($clientMediaType);
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('uploaded file is moved');
        }

        return Stream::create(fopen($this->file, 'r+'));
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws InvalidArgumentException if the $targetPath specified is invalid.
     * @throws RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath): void
    {
        $this->validateActive();

        $dirname = dirname($targetPath);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        if (!$this->isStringNotEmpty($targetPath)) {
            throw new InvalidArgumentException('Invalid path provided for move operation');
        }

        if ($this->file) {
            $this->moved = php_sapi_name() == 'cli' ? rename($this->file, $targetPath) : move_uploaded_file($this->file, $targetPath);
        }

        if (!$this->moved) {
            throw new RuntimeException(sprintf('Uploaded file could not be move to %s', $targetPath));
        }
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getClientFilename(),
            'type' => $this->getClientMediaType(),
            'tmp_file' => $this->file,
            'error' => $this->getError(),
            'size' => $this->getSize(),
        ];
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        $clientName = $this->getClientFilename();
        $segments = explode('.', $clientName);
        return end($segments) ?? null;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        if (is_string($this->mimeType)) {
            return $this->mimeType;
        }
        return $this->mimeType = (mime_content_type($this->file) ?: '');
    }

    /**
     * @param mixed $param
     * @return bool
     */
    protected function isStringOrNull(mixed $param): bool
    {
        return in_array(gettype($param), ['string', 'NULL']);
    }

    /**
     * @param mixed $param
     * @return bool
     */
    protected function isStringNotEmpty(mixed $param): bool
    {
        return is_string($param) && empty($param) === false;
    }

    /**
     * if the temp file is moved.
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * Depending on the value set file or stream variable.
     */
    protected function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function setError(int $error): self
    {
        if (in_array($error, UploadedFile::$errors) === false) {
            throw new InvalidArgumentException('Invalid error status for UploadedFile');
        }

        $this->error = $error;
        return $this;
    }

    /**
     * @param null|int $size
     * @throws InvalidArgumentException
     */
    protected function setSize(?int $size): self
    {
        if (is_int($size) === false) {
            throw new InvalidArgumentException('Upload file size must be an integer');
        }

        $this->size = $size;
        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function setClientFilename(?string $clientFilename): self
    {
        if ($this->isStringOrNull($clientFilename) === false) {
            throw new InvalidArgumentException('Upload file client filename must be a string or null');
        }

        $this->clientFilename = $clientFilename;
        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function setClientMediaType(?string $clientMediaType): self
    {
        if ($this->isStringOrNull($clientMediaType) === false) {
            throw new InvalidArgumentException('Upload file client media type must be a string or null');
        }

        $this->clientMediaType = $clientMediaType;
        return $this;
    }

    /**
     * Return true if there is no upload error.
     */
    protected function isOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * @throws RuntimeException
     */
    protected function validateActive()
    {
        if ($this->isOk() === false) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->isMoved()) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }
}