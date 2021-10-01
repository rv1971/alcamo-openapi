<?php

namespace alcamo\openapi;

use alcamo\iana\MediaType;
use alcamo\ietf\Uri;
use Psr\Http\Message\UriInterface;

class Example extends OpenApiNode
{
    private $externalValueUrl_       = false; ///< ?Uri
    private $externalValueMediaType_ = false; ///< ?alcamo::iana::MediaType
    private $externalValueContent_   = false; ///< any

    /// Resolved URL of `externalValue`, if any
    public function getExternalValueUrl(): ?UriInterface
    {
        if ($this->externalValueUrl_ === false) {
            $this->externalValueUrl_ = isset($this->externalValue)
                ? $this->resolveUri($this->externalValue)
                : null;
        }

        return $this->externalValueUrl_;
    }

    /// Media type of target of `externalValue`, if any
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

    /// Literal content of target of `externalValue`, if any
    public function getExternalValueContent()
    {
        if ($this->externalValueContent_ === false) {
            if (isset($this->externalValue)) {
                if ($this->getExternalValueMediaType() == 'application/json') {
                    $this->externalValueContent_ =
                        $this->createNode(
                            $this->getJsonPtr() . '/value',
                            $this->getOwnerDocument()->getDocumentFactory()
                                ->decodeJson(
                                    file_get_contents($this->externalValueUrl_)
                                )
                        );
                } else {
                    $this->externalValueContent_ =
                        file_get_contents($this->externalValueUrl_);
                }
            } else {
                $this->externalValueContent_ = null;
            }
        }

        return $this->externalValueContent_;
    }

    /// Either content of `value` or content of target of `externalValue`
    public function getValue()
    {
        return $this->value ?? $this->getExternalValueContent();
    }

    /// Transform `externalValue`, if any, to a `value` property
    public function resolveExternalValue(): void
    {
        if (isset($this->externalValue)) {
            $this->value = $this->getExternalValueContent();

            unset($this->externalValue);
        }
    }
}
