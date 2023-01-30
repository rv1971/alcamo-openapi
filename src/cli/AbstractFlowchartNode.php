<?php

namespace alcamo\openapi\cli;

use alcamo\openapi\Operation;

abstract class AbstractFlowchartNode extends AbstractFlowchartStmt
{
    private $label_;         ///< string
    private $inEdges_ = [];  ///< array of AbstractFlowchartEdge
    private $outEdges_ = []; ///< array of AbstractFlowchartEdge

    public function __construct(Flowchart $flowchart, string $id, string $label)
    {
        parent::__construct($flowchart, $id);

        $this->label_ = $label;
    }

    public function getLabel(): string
    {
        return $this->label_;
    }

    public function getInEdges(): array
    {
        return $this->inEdges_;
    }

    public function getOutEdges(): array
    {
        return $this->outEdges_;
    }

    public function addInEdge(AbstractFlowchartEdge $edge): void
    {
        $this->inEdges_[$edge->getId()] = $edge;
    }

    public function addOutEdge(AbstractFlowchartEdge $edge): void
    {
        $this->outEdges_[$edge->getId()] = $edge;
    }
}
