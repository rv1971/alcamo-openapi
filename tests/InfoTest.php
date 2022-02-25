<?php

namespace alcamo\openapi;

use alcamo\uri\FileUriFactory;
use alcamo\xml_creation\Nodes;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    /**
     * @dataProvider getRdfaDataProvider
     */
    public function testGetRdfaData($openApi, $expectedHtml)
    {
        $html = [];

        foreach ($openApi->info->getRdfaData() as $stmt) {
            $html[] = $stmt->toVisibleHtmlNodes(true);
        }

        $this->assertEquals(
            $expectedHtml,
            (string)(new Nodes($html))
        );
    }

    public function getRdfaDataProvider()
    {
        $factory = new OpenApiFactory();

        $minimal = $factory->createFromUrl(
            (new FileUriFactory())->create(
                __DIR__ . DIRECTORY_SEPARATOR . 'openapi-minimal.json'
            )
        );

        $minimal31 = $factory->createFromUrl(
            (new FileUriFactory())->create(
                __DIR__ . DIRECTORY_SEPARATOR . 'openapi-3.1.json'
            )
        );

        return [
            [
                $minimal,
                '<span property="dc:title">Minimal OpenAPI document</span>'
                . '<span property="owl:versionInfo">1.0.0</span>'
                . '<a rel="dc:conformsTo" '
                . 'href="https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.0.3.md">OpenAPI 3.0.3</a>'
                . '<a rel="dc:creator author" href="mailto:alice@example.com">Creator</a>'
                . '<span property="dc:type">Text</span>'
                . '<span property="dc:identifier">minimal</span>'
                . '<span property="dc:created">2021-09-24T00:00:00+00:00</span>'
                . '<span property="dc:modified">2021-10-01T00:00:00+00:00</span>'
                . '<span property="dc:language">en</span>'
            ],
            [
                $minimal31,
                '<span property="dc:title">Minimal OpenAPI 3.1 document</span>'
                . '<span property="owl:versionInfo">1.2.3</span>'
                . '<a rel="dc:conformsTo" '
                . 'href="https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.1.0.md">OpenAPI 3.1.0</a>'
                . '<a rel="dc:creator author" href="mailto:bob@example.com">Creator</a>'
                . '<span property="dc:type">Text</span>'
                . '<span property="dc:identifier">minimal-3.1</span>'
                . '<span property="dc:created">2021-10-04T00:00:00+00:00</span>'
                . '<span property="dc:modified">2021-10-04T00:00:00+00:00</span>'
                . '<span property="dc:language">en</span>'
            ]
        ];
    }
}
