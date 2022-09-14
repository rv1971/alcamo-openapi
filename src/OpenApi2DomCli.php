<?php

namespace alcamo\openapi;

use alcamo\json\{Json2Dom, Json2DomCli};

class OpenApi2DomCli extends Json2DomCli
{
    public function createConverter(?int $options = null): Json2Dom
    {
        return new OpenApi2Dom($options);
    }
}
