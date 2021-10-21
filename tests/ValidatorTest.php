<?php

namespace alcamo\openapi;

use alcamo\exception\DataValidationFailed;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testNewFromSchemasException()
    {
        $this->expectException(DataValidationFailed::class);
        $this->expectExceptionMessage(
            'schema id '
            . '"tag:rv1971@web.de,2021:alcamo-openapi:schema:openapi:3.0" '
            . 'differs from key "foo"'
        );

        Validator::newFromSchemas(
            [
                'foo' => OpenApi::SCHEMA_DIR . 'openapi-3.0.json'
            ]
        );
    }
}
