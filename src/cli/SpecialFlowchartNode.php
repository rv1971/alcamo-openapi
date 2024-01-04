<?php

namespace alcamo\openapi\cli;

use alcamo\openapi\Operation;

class SpecialFlowchartNode extends AbstractFlowchartNode
{
    public function __construct(Flowchart $flowchart, string $label)
    {
        parent::__construct($flowchart, self::name2Id($label), $label);
    }

    public function createDotCode(): string
    {
        return $this->createDotCodeFromAttrs([ 'class' => 'special-node' ]);
    }
}
