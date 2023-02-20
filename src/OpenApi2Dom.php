<?php

namespace alcamo\openapi;

use alcamo\json\{Json2Dom, JsonNode, JsonPtr};
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

    public function append(
        \DOMNode $domNode,
        $value,
        JsonPtr $jsonPtr,
        ?string $nsName = null,
        ?string $qName = null,
        ?string $origName = null
    ): void {
        if (!isset($nsName)) {
            parent::append($domNode, $value, $jsonPtr);

            return;
        }

        /** Convert markdown into HTML. */
        if ($qName == 'description') {
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
                "<o:$qName xmlns=\"" . static::HTML_NS
                . '" xmlns:o="' . static::OBJECT_NS . '">'
                .  $this->converter_->convertToHtml($value)
                . "</o:$qName>"
            );


            $this->addAttributes(
                $fragment->firstChild,
                $jsonPtr,
                $nsName,
                $qName,
                $origName
            );

            $fragment->firstChild->setAttribute('type', 'string');

            $domNode->appendChild($fragment);

            return;
        }

        /** Do not transform JSON nodes which are examples. */
        if ($qName == 'value') {
            if ($jsonPtr[count($jsonPtr) - 3] == 'examples') {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }
        }

        $child = $domNode->ownerDocument->createElementNS($nsName, $qName);
        $domNode->appendChild($child);

        $this->addAttributes($child, $jsonPtr, $nsName, $qName, $origName);

        parent::append($child, $value, $jsonPtr);
    }
}
