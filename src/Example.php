<?php

namespace alcamo\openapi;

use alcamo\rdfa\MediaType;
use alcamo\uri\Uri;
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

    /**
     * @brief Content of target of `externalValue`, if any
     *
     * If the target has the media type `application/json`, the target is
     * parsed and the result is an OpenApiNode, an array or a simple
     * type. Otherwise the result is a string with the literal content of the
     * target.
     */
    public function getExternalValueContent()
    {
        if ($this->externalValueContent_ === false) {
            if (isset($this->externalValue)) {
                if ($this->getExternalValueMediaType() == 'application/json') {
                    $this->externalValueContent_ =
                        $this->createNode(
                            $this->getJsonPtr()->appendSegment('value'),
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

    /**
     * @brief Either the content of `value` or the content of the target of
     * `externalValue`
     */
    public function getValue()
    {
        return $this->value ?? $this->getExternalValueContent();
    }

    /**
     * @brief If there is an `externalValue` property, replace it by an
     * equivalent `value` property
     */
    public function resolveExternalValue(): void
    {
        if (isset($this->externalValue)) {
            $this->value = $this->getExternalValueContent();

            unset($this->externalValue);
        }
    }
}
