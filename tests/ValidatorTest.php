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
            'key "foo" differs from schema id '
            . '"tag:rv1971@web.de,2021:alcamo-openapi:schema:openapi:3.0"'
        );

        Validator::newFromSchemas(
            [
                'foo' => OpenApi::SCHEMA_DIR . 'openapi-3.0.json'
            ]
        );
    }
}
