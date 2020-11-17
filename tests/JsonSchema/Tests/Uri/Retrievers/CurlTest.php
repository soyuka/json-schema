<?php

namespace JsonSchema\Tests\Uri\Retrievers;

use JsonSchema\Uri\Retrievers\Curl;
use JsonSchema\Uri\Retrievers\FileGetContents;

/**
 * @group Curl
 */
class CurlTest extends \PHPUnit_Framework_TestCase
{
    public function testCurlFallbackFileGetContents()
    {
        if (!extension_loaded('curl')) {
            static::markTestSkipped();
        }

        $retriever = new Curl(new FileGetContents());
        $result = $retriever->retrieve(__DIR__.'/../Fixture/child.json');
        $this->assertNotEmpty($result);
    }

    /**
     * @expectedException JsonSchema\Exception\ResourceNotFoundException
     */
    public function testFetchMissingFile()
    {
        $retriever = new Curl(new FileGetContents());
        $retriever->retrieve(__DIR__.'/Fixture/missing.json');
    }
}
