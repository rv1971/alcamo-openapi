<?php

namespace alcamo\openapi\cli;

abstract class AbstractFlowchartEdge extends AbstractFlowchartStmt
{
    private $fromNode_; ///< AbstractFlowchartNode
    private $toNode_;   ///< AbstractFlowchartNode
    private $label_;    ///< ?string

    public function __construct(
        Flowchart $flowchart,
        AbstractFlowchartNode $fromNode,
        AbstractFlowchartNode $toNode,
        ?string $label = null
    ) {
        $id = $fromNode->getId() . '_' . $toNode->getId();

        parent::__construct($flowchart, $id);

        $this->fromNode_ = $fromNode;
        $this->toNode_ = $toNode;

        $fromNode->addOutEdge($this);
        $toNode->addInEdge($this);

        $this->label_ = $label;
    }

    public function getFromNode(): AbstractFlowchartNode
    {
        return $this->fromNode_;
    }

    public function getToNode(): AbstractFlowchartNode
    {
        return $this->toNode_;
    }

    public function getLabel(): ?string
    {
        return $this->label_;
    }

    public function createDotCode(): string
    {
        return "{$this->fromNode_->getId()} -> {$this->toNode_->getId()}";
    }
}
