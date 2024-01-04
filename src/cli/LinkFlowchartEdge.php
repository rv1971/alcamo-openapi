<?php

namespace alcamo\openapi\cli;

use alcamo\openapi\{GraphvizHints, Link, Response};

class LinkFlowchartEdge extends AbstractFlowchartEdge
{
    private $link_;  ///< ?Link
    private $hints_; ///< GraphvizHints

    public function __construct(
        Flowchart $flowchart,
        OperationFlowchartNode $fromNode,
        OperationFlowchartNode $toNode,
        Link $link
    ) {
        $this->hints_ = $link->{'x-graphviz-hints'}
            ?? new GraphvizHints(
                (object)[],
                $link->getOwnerDocument(),
                $link->getJsonPtr()->appendSegment('x-graphviz-hints')
            );

        if (
            !($flowchart->getOptions() & Flowchart::NO_EDGE_LABELS)
            && isset($link->description)
        ) {
            $label = rtrim($link->description, '.');
        } else {
            $label = null;
        }

        parent::__construct($flowchart, $fromNode, $toNode, $label);

        $this->link_ = $link;
    }

    public function getLink(): Link
    {
        return $this->link_;
    }

    public function createDotCode(?string $baseUrl = null): string
    {
        $attrs = [ 'class' => 'link-edge' ];

        $label = $this->hints_->label ?? $this->getLabel();

        if ($label) {
            $attrs['label'] = $label;
        }

        if (isset($this->hints_->class)) {
            $attrs['class'] .= ' ' . $this->hints_->class;
        }

        return $this->createDotCodeFromAttrs($attrs);
    }
}
