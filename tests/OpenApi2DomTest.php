<?php

namespace alcamo\openapi;

use alcamo\uri\FileUriFactory;
use PHPUnit\Framework\TestCase;

class OpenApi2DomTest extends TestCase
{
    public const OPENAPI_FILENAME =
        __DIR__ . DIRECTORY_SEPARATOR . 'openapi-markdown.json';

    public const XML_FILENAME =
        __DIR__ . DIRECTORY_SEPARATOR . 'openapi-markdown.xml';

    public function testConversion()
    {
        $factory = new OpenApiFactory();

        $openApi = $factory->createFromUrl(
            (new FileUriFactory())->create(self::OPENAPI_FILENAME)
        );

        $domDocument = new \DOMDocument();

        $domText = str_replace(
            '$(base)',
            (new FileUriFactory())->create(self::OPENAPI_FILENAME),
            file_get_contents(self::XML_FILENAME)
        );

        $domDocument->loadXML($domText);

        $domDocument->formatOutput = true;

        $openApi2Dom = new OpenApi2Dom(OpenApi2Dom::JSON_PTR_ATTRS);

        $domDocument2 = $openApi2Dom->convert($openApi);

        $domDocument2->formatOutput = true;

        $this->assertSame(
            $domDocument->saveXML(),
            $domDocument2->saveXML()
        );
    }
}
