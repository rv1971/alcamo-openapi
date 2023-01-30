<?php

namespace alcamo\openapi\cli;

use alcamo\openapi\Operation;

class SpecialFlowchartNode extends AbstractFlowchartNode
{
    public function __construct(Flowchart $flowchart, string $label)
    {
        parent::__construct($flowchart, $this->name2Id($label), $label);
    }

    public function createDotCode(): string
    {
        $attrs = [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'class' => 'special-node',
        ];

        return $this->getId() . $this->createDotAttrs($attrs);
    }
}
