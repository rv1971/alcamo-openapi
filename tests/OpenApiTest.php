<?php

namespace alcamo\openapi;

use alcamo\exception\{AbsoluteUriNeeded, DataValidationFailed};
use alcamo\ietf\UriFactory;
use PHPUnit\Framework\{Exception, TestCase};

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
            (new UriFactory())->createFromFilesystemPath(self::OPENAPI_FILENAME)
        );

        $this->assertInstanceOf(OpenApi::class, $openApi);

        $this->assertInstanceOf(Info::class, $openApi->info);

        $this->assertInstanceOf(Contact::class, $openApi->info->contact);

        $this->assertInstanceOf(License::class, $openApi->info->license);

        $this->assertInstanceOf(License::class, $openApi->info->license);

        $this->assertInstanceOf(Server::class, $openApi->servers[0]);

        $this->assertInstanceOf(Server::class, $openApi->servers[1]);

        $this->assertInstanceOf(
            Schema::class,
            $openApi->components->schemas->Order->properties->id
        );
    }

    public function testConstructUriException()
    {
        $factory = new OpenApiFactory();

        $this->expectException(AbsoluteUriNeeded::class);

        $factory->createFromUrl(self::OPENAPI_FILENAME);
    }

    public function testMinimal()
    {
        $factory = new OpenApiFactory();

        $openApi = $factory->createFromUrl(
            (new UriFactory())->createFromFilesystemPath(
                __DIR__ . DIRECTORY_SEPARATOR . 'openapi-minimal.json'
            )
        );

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testInvalidOpenApiVersion()
    {
        $this->expectException(Exception::class);

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

    public function testGetValidator()
    {
        $factory = new OpenApiFactory();

        $validator = $factory
            ->createFromUrl(
                (new UriFactory())
                    ->createFromFilesystemPath(self::OPENAPI_FILENAME)
            )
            ->getValidator();

        $validator->validate(
            json_decode('{"name": "foo", "photoUrls": []}'),
            '#/components/schemas/Pet'
        );

        $this->expectException(DataValidationFailed::class);
        $this->expectExceptionMessage(
            "the required properties (photoUrls) are missing"
        );
        $validator->validate(
            json_decode('{"name": "foo"}'),
            '#/components/schemas/Pet'
        );
    }

    private function createFromUrl($path)
    {
        return (new OpenApiFactory())->createFromUrl(
            (new UriFactory())->createFromFilesystemPath($path)
        );
    }
}
