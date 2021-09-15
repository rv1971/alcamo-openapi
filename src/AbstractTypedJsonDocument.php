<?php

namespace alcamo\openapi;

use alcamo\json\JsonDocumentTrait;

abstract class AbstractTypedJsonDocument extends AbstractTypedJsonNode
{
    use JsonDocumentTrait;
}
