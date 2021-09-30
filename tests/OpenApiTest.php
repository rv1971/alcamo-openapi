<?php

namespace alcamo\openapi;

use alcamo\exception\{AbsoluteUriNeeded, DataValidationFailed};
use alcamo\ietf\Uri;
use PHPUnit\Framework\TestCase;

class OpenApiTest extends TestCase
{
    public const OPENAPI_FILENAME =
        __DIR__ . DIRECTORY_SEPARATOR . 'openapi.json';

    public const OPENAPI_INVALID_DIR =
        __DIR__ . DIRECTORY_SEPARATOR . 'invalid' . DIRECTORY_SEPARATOR;

    public function testConstruct()
    {
        $factory = new OpenApiFactory();

        $openApi = $factory->createFromUrl(
            Uri::newFromFilesystemPath(self::OPENAPI_FILENAME)
        );

        $this->assertInstanceOf(OpenApi::class, $openApi);

        $this->assertInstanceOf(Info::class, $openApi->info);

        $this->assertInstanceOf(Contact::class, $openApi->info->contact);

        $this->assertInstanceOf(License::class, $openApi->info->license);

        $this->assertInstanceOf(License::class, $openApi->info->license);

        $this->assertInstanceOf(Server::class, $openApi->servers[0]);

        $this->assertInstanceOf(Server::class, $openApi->servers[1]);
    }

    public function testConstructUriException()
    {
        $factory = new OpenApiFactory();

        $this->expectException(AbsoluteUriNeeded::class);
        $this->expectExceptionMessage(
            'Relative URI "'
            . self::OPENAPI_FILENAME
            . '" given where absolute URI is needed'
        );

        $factory->createFromUrl(self::OPENAPI_FILENAME);
    }

    public function testMinimal()
    {
        $factory = new OpenApiFactory();

        $openApi = $factory->createFromUrl(
            Uri::newFromFilesystemPath(
                __DIR__ . DIRECTORY_SEPARATOR . 'openapi-minimal.json'
            )
        );

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testInvalidOpenApiVersion()
    {
        $this->expectException(\RuntimeException::class);

        $this->createFromUrl(
            self::OPENAPI_INVALID_DIR . 'openapi-version.json'
        );
    }

    public function testInvalidInfoVersion()
    {
        $this->expectException(DataValidationFailed::class);
        $this->expectExceptionMessage(
            "the data (integer) must match the type: string"
        );

        $this->createFromUrl(
            self::OPENAPI_INVALID_DIR . 'info-version.json'
        );
    }

    public function testInvalidSchemaExample()
    {
        $this->expectException(DataValidationFailed::class);
        $this->expectExceptionMessage(
            "the data (array) must match the type: object"
        );

        $this->createFromUrl(
            self::OPENAPI_INVALID_DIR . 'schema-example.json'
        );
    }

    public function testInvalidMediaTypeExample()
    {
        $this->expectException(DataValidationFailed::class);
        $this->expectExceptionMessage(
            "the required properties (baz) are missing"
        );

        $doc = $this->createFromUrl(
            self::OPENAPI_INVALID_DIR . 'media-type-example.json'
        );
    }

    public function testInvalidMediaTypeExamples()
    {
        $this->expectException(DataValidationFailed::class);
        $this->expectExceptionMessage(
            "the data (string) must match the type: number"
        );

        $doc = $this->createFromUrl(
            self::OPENAPI_INVALID_DIR . 'media-type-examples.json'
        );
    }

    public function testInvalidMediaTypeExternalExamples()
    {
        $this->expectException(DataValidationFailed::class);
        $this->expectExceptionMessage(
            "the data (string) must match the type: boolean"
        );

        $doc = $this->createFromUrl(
            self::OPENAPI_INVALID_DIR . 'media-type-external-examples.json'
        );
    }

    public function testInvalidInforMetadata()
    {
        $this->expectException(DataValidationFailed::class);
        $this->expectExceptionMessage(
            "the required properties (x-dc-language) are missing"
        );

        $doc = $this->createFromUrl(
            self::OPENAPI_INVALID_DIR . 'info-metadata.json'
        );
    }

    private function createFromUrl($path)
    {
        return (new OpenApiFactory())->createFromUrl(
            Uri::newFromFilesystemPath($path)
        );
    }
}
