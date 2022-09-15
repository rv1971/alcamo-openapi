<?php

namespace alcamo\openapi;

use alcamo\json\{Json2Dom, JsonNode};
use League\CommonMark\CommonMarkConverter;

class OpenApi2Dom extends Json2Dom
{
    public const HTML_NS = 'http://www.w3.org/1999/xhtml';

    private $converter_;

    public function __construct(int $flags = null)
    {
        parent::__construct($flags);

        $this->converter_ = new CommonMarkConverter();
    }

    public function appendJsonNode(
        \DOMNode $domNode,
        JsonNode $jsonNode,
        string $nsName,
        string $qName,
        ?string $origName = null
    ): void {
        /** Do not transform JSON objects which are examples. */
        if ($qName == 'value') {
            $a = explode('/', $jsonNode->getJsonPtr());

            if ($a[count($a) - 3] == 'examples') {
                $this->appendValue(
                    $domNode,
                    json_encode($jsonNode, JSON_PRETTY_PRINT),
                    $nsName,
                    $qName,
                    $jsonNode->getJsonPtr(),
                    $origName
                );

                return;
            }
        }

        parent::appendJsonNode(
            $domNode,
            $jsonNode,
            $nsName,
            $qName,
            $origName
        );
    }

    public function appendArray(
        \DOMNode $domNode,
        array $jsonArray,
        string $nsName,
        string $qName,
        string $jsonPtr,
        ?string $origName = null
    ): void {
        /** Do not transform JSON arrays which are examples. */
        if ($qName == 'value') {
            $a = explode('/', $jsonPtr);

            if ($a[count($a) - 3] == 'examples') {
                $this->appendValue(
                    $domNode,
                    json_encode($jsonArray, JSON_PRETTY_PRINT),
                    $nsName,
                    $qName,
                    $jsonPtr,
                    $origName
                );

                return;
            }
        }

        parent::appendArray(
            $domNode,
            $jsonArray,
            $nsName,
            $qName,
            $jsonPtr,
            $origName
        );
    }

    public function appendValue(
        \DOMNode $domNode,
        $value,
        string $nsName,
        string $localName,
        string $jsonPtr,
        ?string $origName = null
    ): void {
        /** For `description` attributes, convert CommonMark to HTML. */

        if ($localName == 'description') {
            $fragment = $domNode->ownerDocument->createDocumentFragment();

            /* This approach implies that the namespace prefix `o` is declared
             * again and again in each `description` element. This would even
             * be the case if the prefix was already declared in the document
             * element. It seems that there is no means to avoid that. The
             * alternative to create the `description` element with
             * createElementNS() and to decare the HTML namespace in each
             * top-level HTML element might result in a slightly smaller
             * output in many cases but would make the implementation much
             * more complex. */
            $fragment->appendXML(
                "<o:$localName xmlns=\"" . static::HTML_NS
                . '" xmlns:o="' . static::OBJECT_NS . '">'
                .  $this->converter_->convertToHtml($value)
                . "</o:$localName>"
            );

            $this->addAttributes($fragment->firstChild, $value, $jsonPtr, $origName);

            $domNode->appendChild($fragment);
        } else {
            parent::appendValue(
                $domNode,
                $value,
                $nsName,
                $localName,
                $jsonPtr,
                $origName
            );
        }
    }
}
