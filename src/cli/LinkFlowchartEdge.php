<?php

namespace alcamo\openapi\cli;

use alcamo\openapi\{Link, Response};

class LinkFlowchartEdge extends AbstractFlowchartEdge
{
    private $response_; ///< ?Response
    private $link_;     ///< ?Link
    private $hints_;     ///< JsonNode|stdClass

    public function __construct(
        Flowchart $flowchart,
        OperationFlowchartNode $fromNode,
        OperationFlowchartNode $toNode,
        Response $response,
        Link $link
    ) {
        $this->hints_ = $link->{'x-graphviz-hints'} ?? (object)[];

        if (
            !($flowchart->getOptions() & Flowchart::NO_EDGE_LABELS)
            && isset($link->description)
        ) {
            $label = rtrim($link->description, '.');
        } else {
            $label = null;
        }

        parent::__construct($flowchart, $fromNode, $toNode, $label);

        $this->response_ = $response;
        $this->link_ = $link;
    }

    public function getResponse(): Response
    {
        return $this->response_;
    }

    public function getLink(): Link
    {
        return $this->link_;
    }

    public function createDotCode(?string $baseUrl = null): string
    {
        $attrs = [
            'id' => $this->getId(),
            'class' => 'link-edge'
        ];

        if ($this->getLabel()) {
            $attrs['label'] = $this->getLabel();
        }

        if (isset($this->hints_->class)) {
            $attrs['class'] .= ' ' . $this->hints_->class;
        }

        return parent::createDotCode() . $this->createDotAttrs($attrs);
    }
}
