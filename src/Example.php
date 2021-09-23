<?php

namespace alcamo\openapi;

use alcamo\iana\MediaType;
use alcamo\ietf\Uri;
use Psr\Http\Message\UriInterface;

class Example extends OpenApiNode
{
    private $externalValueUrl_ = false;       /// ?Uri
    private $externalValueMediaType_ = false; /// ?alcamo::iana::MediaType

    public function getExternalValueUrl(): ?UriInterface
    {
        if ($this->externalValueUrl_ === false) {
            $this->externalValueUrl_ = isset($this->externalValue)
                ? $this->resolveUri($this->externalValue)
                : null;
        }

        return $this->externalValueUrl_;
    }

    public function getExternalValueMediaType(): ?MediaType
    {
        if ($this->externalValueMediaType_ === false) {
            $this->getExternalValueUrl();

            $this->externalValueMediaType_ = isset($this->externalValueUrl_)
                ? MediaType::newFromFilename($this->externalValueUrl_)
                : null;
        }

        return $this->externalValueMediaType_;
    }

    public function resolveExternalValue(): void
    {
        if (isset($this->externalValue))
        {
            if ($this->getExternalValueMediaType() == 'application/json')
            {
                $this->value = new OpenApiNode(
                    $this->getOwnerDocument()->getDocumentFactory()->decodeJson(
                        file_get_contents($this->externalValueUrl_)
                    ),
                    $this->getOwnerDocument(),
                    $this->getJsonPtr() . '/value',
                    $this->getBaseUri()
                );
            } else {
                $this->value = file_get_contents($this->externalValueUrl_);
            }

            unset($this->externalValue);
        }
    }
}
