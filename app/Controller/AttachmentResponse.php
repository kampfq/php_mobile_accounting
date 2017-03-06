<?php
/**
 * AttachmentResponse.php
 *
 * Made with <3 with PhpStorm
 * @author kampfq
 * @copyright 2017 Benjamin Issleib
 * @license    NO LICENSE AVAILIABLE
 * @see
 * @since      File available since Release
 * @deprecated File deprecated in Release
 */

namespace Controller;

use Zend\Diactoros\Response;
use Zend\Diactoros\Response\InjectContentTypeTrait;
use Zend\Diactoros\Stream;

class AttachmentResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * Create a file attachment response.
     *
     * Produces a text response with a Content-Type of given file mime type and a default
     * status of 200.
     *
     * @param string $file Valid file path
     * @param int $status Integer status code for the response; 200 by default.
     * @param array $headers Array of headers to use at initialization.
     * @internal param StreamInterface|string $text String or stream for the message body.
     */
    public function __construct($file, $status = 200, array $headers = [],$filename = false)
    {
        $fileInfo = new \SplFileInfo($file);

        $headers = array_replace($headers, [
            'content-length' => $fileInfo->getSize(),
            'content-disposition' => sprintf('attachment; filename=%s', $filename?:$fileInfo->getFilename()),
        ]);

        parent::__construct(
            new Stream($fileInfo->getRealPath(), 'r'),
            $status,
            $this->injectContentType((new \finfo(FILEINFO_MIME_TYPE))->file($fileInfo->getRealPath()), $headers)
        );
    }
}