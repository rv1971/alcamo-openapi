<?php

namespace alcamo\openapi\cli;

abstract class AbstractFlowchartStmt
{
    public static function name2Id(string $name): string
    {
        return preg_replace('/[^0-9_a-z]/', '_', strtolower($name));
    }

    private $flowchart_; ///< Flowchart
    private $id_;      ///< string

    public function __construct(Flowchart $flowchart, string $id)
    {
        $this->flowchart_ = $flowchart;
        $this->id_ = $id;
    }

    public function getFlowchart(): Flowchart
    {
        return $this->flowchart_;
    }

    public function getId(): string
    {
        return $this->id_;
    }

    public function createDotAttrs(array $attrs): ?string
    {
        if (!$attrs) {
            return null;
        }

        $result = [];

        foreach ($attrs as $key => $value) {
            $result[] = "$key=\"$value\"";
        }

        return '[' . implode(', ', $result) . ']';
    }

    abstract public function createDotCode(): string;
}
