<?php

namespace alcamo\openapi;

use alcamo\json\{JsonNode, ReferenceResolver};
use PHPUnit\Framework\TestCase;

class OpenApiTest extends TestCase
{
    public const OPENAPI_FILENAME =
        __DIR__ . DIRECTORY_SEPARATOR . 'openapi.json';

    public function testConstruct()
    {
        $factory = new OpenApiFactory();

        $openApi = $factory->createFromUrl(self::OPENAPI_FILENAME);

        $this->assertInstanceOf(JsonNode::class, $openApi->servers[0]);

        $this->assertNotInstanceOf(Server::class, $openApi->servers[0]);

        $openApi->resolveReferences(ReferenceResolver::RESOLVE_EXTERNAL);

        $this->assertInstanceOf(OpenApi::class, $openApi);

        $this->assertInstanceOf(Info::class, $openApi->info);

        $this->assertInstanceOf(Contact::class, $openApi->info->contact);

        $this->assertInstanceOf(License::class, $openApi->info->license);

        $this->assertInstanceOf(License::class, $openApi->info->license);

        $this->assertInstanceOf(Server::class, $openApi->servers[0]);

        $this->assertInstanceOf(Server::class, $openApi->servers[1]);
    }
}
