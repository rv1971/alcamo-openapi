<?php

namespace alcamo\openapi;

use alcamo\json\JsonDocumentFactory;

class OpenApiFactory extends JsonDocumentFactory
{
    public const DOCUMENT_CLASS = OpenApi::class;
}
