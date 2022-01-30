1<?php
declare(strict_types=1);
namespace Test;

use Api\Utilities\ApiHttpClientInterface;
use Api\Utilities\ApiHttpResponse;
use JsonException;
use RuntimeException;

/**
 * Class ApiHttpClientStubTest
 * @package Test
 */
class ApiHttpClientStubTest extends TestCase
{

    public function testAddParamRequest():void
    {
        $stub=new ApiHttpClientStub();
        $stub->addResult(false, json_encode(
            [
                "test" => "c'est moi qui fait",
                "testBis" => "c'est plus moi"
            ],
            JSON_THROW_ON_ERROR
        ));
        $clef=$stub->addParamRequest("", [], ApiHttpClientInterface::GET);
        self::assertIsString($clef);
        self::assertEquals(8, strlen($clef));
    }

    /**
     * @throws JsonException
     */
    public function testAddResult():void
    {
        $stub=new ApiHttpClientStub();
        $stub->addResult(true, json_encode(
            [
                "test" => "c'est moi qui fait",
                "testBis" => "c'est plus moi"
            ],
            JSON_THROW_ON_ERROR
        ));
        $clef=$stub->addParamRequest("", [], ApiHttpClientInterface::GET);
        $result = $stub->getResult($clef);
        self::assertInstanceOf(ApiHttpResponse::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertEquals(
            [
                "test" => "c'est moi qui fait",
                "testBis" => "c'est plus moi"
            ],
            $result->getData()
        );
        $stub->addResult(false, json_encode(
            [
                "test" => "ok c'est une erreur "
            ],
            JSON_THROW_ON_ERROR
        ));
        $clef=$stub->addParamRequest("", [], ApiHttpClientInterface::GET);
        $result = $stub->getResult($clef);
        self::assertInstanceOf(ApiHttpResponse::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertEquals(
            [
                "test" => "ok c'est une erreur "
            ],
            $result->getData()
        );
    }

    /**
     * @throws JsonException
     */
    public function testResetResult():void
    {
        $this->expectException(RuntimeException::class);
        $stub=new ApiHttpClientStub();
        $stub->addResult(true, json_encode(
            [
                "test" => "c'est moi qui fait",
                "testBis" => "c'est plus moi"
            ],
            JSON_THROW_ON_ERROR
        ));
        $stub->resetResult();
        $stub->addParamRequest("", [], ApiHttpClientInterface::GET);
    }

    /**
     * @throws JsonException
     */
    public function testCurlUnique():void
    {
        $stub=new ApiHttpClientStub();
        $stub->addResult(true, json_encode(
            [
                "test" => "c'est moi qui fait",
                "testBis" => "c'est plus moi"
            ],
            JSON_THROW_ON_ERROR
        ));
        $result=$stub->curlUnique("", [], ApiHttpClientInterface::GET);
        self::assertInstanceOf(ApiHttpResponse::class, $result);
        self::assertTrue($result->isSuccess());
        self::assertEquals(
            [
                "test" => "c'est moi qui fait",
                "testBis" => "c'est plus moi"
            ],
            $result->getData()
        );
        $stub->addResult(false, json_encode(
            [
                "test" => "ok c'est une erreur "
            ],
            JSON_THROW_ON_ERROR
        ));
        $result=$stub->curlUnique("", [], ApiHttpClientInterface::GET);
        self::assertInstanceOf(ApiHttpResponse::class, $result);
        self::assertFalse($result->isSuccess());
        self::assertEquals(
            [
                "test" => "ok c'est une erreur "
            ],
            $result->getData()
        );
    }
}
