<?php

namespace alcamo\openapi;

use alcamo\rdfa\{DcCreator, Node as RdfaNode, RdfaData};

class Contact extends OpenApiNode
{
    public function toDcCreator(): DcCreator
    {
        switch (true) {
            case isset($this->url):
                return new DcCreator(
                    new RdfaNode(
                        $this->url,
                        isset($this->name)
                        ? RdfaData::newFromIterable(
                            [ [ 'dc:title', $this->name ] ]
                        )
                        : null
                    )
                );

            case isset($this->email):
                return new DcCreator(
                    new RdfaNode(
                        "mailto:$this->email",
                        isset($this->name)
                        ? RdfaData::newFromIterable(
                            [ [ 'dc:title', $this->name ] ]
                        )
                        : null
                    )
                );

            default:
                return new DcCreator($this->name);
        }
    }
}
