<?php

namespace alcamo\openapi;

use alcamo\rdfa\DcCreator;

class Contact extends OpenApiNode
{
    public function toDcCreator(): DcCreator
    {
        switch (true) {
            case isset($this->url):
                return new DcCreator($this->url, $this->name ?? true);

            case isset($this->email):
                return
                    new DcCreator("mailto:$this->email", $this->name ?? true);

            default:
                return new DcCreator($this->name);
        }
    }
}
