<?php

namespace alcamo\openapi;

use alcamo\rdfa\{DcCreator, Node, RdfaData};
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    /**
     * @dataProvider toDcCreatorProvider
     */
    public function testToDcCreator($jsonData, $expectedStmt): void
    {
        $contact = new Contact(json_decode($jsonData));

        $this->assertEquals(
            $expectedStmt,
            $contact->toDcCreator()
        );
    }

    public function toDcCreatorProvider(): array
    {
        return [
            [
                '{"name": "Alice"}',
                new DcCreator('Alice')
            ],
            [
                '{"name": "Alice", "url": "https://alice.example.info"}',
                new DcCreator(
                    new Node(
                        'https://alice.example.info',
                        RdfaData::newFromIterable([ 'dc:title' => 'Alice' ])
                    )
                )
            ],
            [
                '{"name": "Bob", "email": "bob@example.info"}',
                new DcCreator(
                    new Node(
                        'mailto:bob@example.info',
                        RdfaData::newFromIterable([ 'dc:title' => 'Bob' ])
                    )
                )
            ],
            [
                '{"name": "Bob", "email": "bob@example.info", "url": "https://bob.example.info"}',
                new DcCreator(
                    new Node(
                        'https://bob.example.info',
                        RdfaData::newFromIterable([ 'dc:title' => 'Bob' ])
                    )
                )
            ],
            [
                '{"url": "https://alice.example.info"}',
                new DcCreator(new Node('https://alice.example.info'))
            ],
            [
                '{"email": "alice@example.info"}',
                new DcCreator(new Node('mailto:alice@example.info'))
            ]
        ];
    }
}
