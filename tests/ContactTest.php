<?php

namespace alcamo\openapi;

use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    /**
     * @dataProvider toDcCreatorProvider
     */
    public function testToDcCreator($jsonData, $expectedHtml)
    {
        $contact = new Contact(json_decode($jsonData));

        $this->assertEquals(
            $expectedHtml,
            (string)$contact->toDcCreator()->toVisibleHtmlNodes()
        );
    }

    public function toDcCreatorProvider()
    {
        return [
            [
                '{"name": "Alice"}',
                'Alice'
            ],
            [
                '{"name": "Alice", "url": "https://alice.example.info"}',
                '<a href="https://alice.example.info">Alice</a>'
            ],
            [
                '{"name": "Bob", "email": "bob@example.info"}',
                '<a href="mailto:bob@example.info">Bob</a>'
            ],
            [
                '{"name": "Bob", "email": "bob@example.info", "url": "https://bob.example.info"}',
                '<a href="https://bob.example.info">Bob</a>'
            ],
            [
                '{"url": "https://alice.example.info"}',
                '<a href="https://alice.example.info">Creator</a>'
            ],
            [
                '{"email": "alice@example.info"}',
                '<a href="mailto:alice@example.info">Creator</a>'
            ]
        ];
    }
}
