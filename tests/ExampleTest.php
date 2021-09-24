<?php

namespace alcamo\openapi;

use alcamo\ietf\Uri;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public const OPENAPI_FILENAME =
        __DIR__ . DIRECTORY_SEPARATOR . 'openapi.json';

    public function testConstruct()
    {
        $factory = new OpenApiFactory();

        $openApi = $factory->createFromUrl(
            Uri::newFromFilesystemPath(self::OPENAPI_FILENAME)
        );

        $examples = $openApi->paths->{'/pet/findByStatus'}
        ->get->responses->{'200'}->content->{'application/json'}->examples;

        $this->assertInstanceOf(Examples::class, $examples);

        $this->assertInstanceOf(
            OpenApiNode::class,
            $examples->inline->value[0]
        );

        $this->assertIsString($examples->external->externalValue);

        $this->assertIsString($examples->external_xml->externalValue);

        $openApi->resolveExternalValues();

        $this->assertFalse(isset($examples->external->externalValue));

        $this->assertInstanceOf(
            OpenApiNode::class,
            $examples->external->value[0]
        );

        $this->assertEquals(
            'Elephant A',
            $examples->external->value[0]->name
        );

        $this->assertFalse(isset($examples->external_xml->externalValue));

        $this->assertEquals(
            '<?xml ',
            substr($examples->external_xml->value, 0, 6)
        );

        $this->assertEquals(
            'text/xml; charset="us-ascii"',
            $examples->external_xml->getExternalValueMediaType()
        );

        $this->assertEquals(
            dirname($examples->external_xml->getBaseUri())
            . DIRECTORY_SEPARATOR . 'Pet.example.unicorns.xml',
            $examples->external_xml->getExternalValueUrl()
        );
    }
}
