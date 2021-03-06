<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Uri\Retrievers;

use JsonSchema\Validator;

/**
 * Tries to retrieve JSON schemas from a URI using cURL library
 *
 * @author Sander Coolen <sander@jibber.nl>
 */
class Curl extends AbstractRetriever
{
    private $decorated;
    protected $messageBody;

    public function __construct(UriRetrieverInterface $decorated = null)
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException("cURL not installed");
        }

        $this->decorated = $decorated;
    }

    /**
     * {@inheritDoc}
     * @see \JsonSchema\Uri\Retrievers\UriRetrieverInterface::retrieve()
     */
    public function retrieve($uri)
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if (!$scheme) {
            return $this->decorated->retrieve($uri);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: ' . Validator::SCHEMA_MEDIA_TYPE));

        $response = curl_exec($ch);
        if (false === $response) {
            throw new \JsonSchema\Exception\ResourceNotFoundException('JSON schema not found');
        }

        $curlInfo = curl_getinfo($ch);
        $headers = substr($response, 0, $curlInfo['header_size']);
        $this->fetchContentType($headers, $curlInfo);
        $this->messageBody = substr($response, $curlInfo['header_size']);

        curl_close($ch);

        return $this->messageBody;
    }

    /**
     * @param string $response cURL HTTP response
     * @return boolean Whether the Content-Type header was found or not
     */
    private function fetchContentType($headers, $curlInfo)
    {
        //https://www.phpliveregex.com/p/xYE
        if (0 < preg_match_all("/content-type: (.+?(?=;|$))/ims", $headers, $match, PREG_SET_ORDER)) {
            $this->contentType = trim(end($match[1]));

            return true;
        }

        return false;
    }
}
