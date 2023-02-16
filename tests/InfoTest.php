<?php

namespace alcamo\openapi;

use alcamo\rdfa\{Node, RdfaData};
use alcamo\uri\FileUriFactory;
use alcamo\xml_creation\Nodes;
use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{
    /**
     * @dataProvider getRdfaDataProvider
     */
    public function testGetRdfaData($openApi, $expectedRdfaInputData): void
    {
        $this->assertEquals(
            RdfaData::newFromIterable($expectedRdfaInputData),
            $openApi->getRoot()->info->getRdfaData()
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
                [
                    'dc:title' => 'Minimal OpenAPI document',
                    'owl:versionInfo' => '1.0.0',
                    'dc:conformsTo' => new Node(
                        'https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.0.3.md',
                        RdfaData::newFromIterable(
                            [ 'dc:title' => 'OpenAPI 3.0.3' ]
                        )
                    ),
                    'dc:type' => 'Text'
                ]
            ],
            [
                $minimal31,
                [
                    'dc:title' => 'Minimal OpenAPI 3.1 document',
                    'owl:versionInfo' => '1.2.3',
                    'dc:conformsTo' => new Node(
                        'https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.1.0.md',
                        RdfaData::newFromIterable(
                            [ 'dc:title' => 'OpenAPI 3.1.0' ]
                        )
                    ),
                    'dc:type' => 'Text',
                    'dc:identifier' => 'minimal-3.1',
                    'dc:created' => '2021-10-04Z',
                    'dc:modified' => '2021-10-04Z',
                    'dc:language' => 'en',
                    'dc:creator' => new Node('mailto:bob@example.com')
                ]
            ]
        ];
    }
}
