<?php

namespace alcamo\openapi;

use alcamo\rdfa\{DcCreator, Node, RdfaData};

class Contact extends OpenApiNode
{
    public function toDcCreator(): DcCreator
    {
        switch (true) {
            case isset($this->url):
                return new DcCreator(
                    new Node(
                        $this->url,
                        isset($this->name)
                        ? RdfaData::newFromIterable(
                            [ 'dc:title' => $this->name ]
                        )
                        : null
                    )
                );

            case isset($this->email):
                return new DcCreator(
                    new Node(
                        "mailto:$this->email",
                        isset($this->name)
                        ? RdfaData::newFromIterable(
                            [ 'dc:title' => $this->name ]
                        )
                        : null
                    )
                );

            default:
                return new DcCreator($this->name);
        }
    }
}
