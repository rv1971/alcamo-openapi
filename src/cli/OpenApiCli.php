<?php

namespace alcamo\openapi\cli;

use alcamo\cli\AbstractCli;
use alcamo\openapi\{OpenApi, OpenApiFactory};
use alcamo\uri\FileUriFactory;
use GetOpt\{GetOpt, Operand};

class OpenApiCli extends AbstractCli
{
    public const COMMANDS = [
        'create-flowchart' => [
            'createFlowchart',
            [
                'base-url' => [
                    'b',
                    self::REQUIRED_ARGUMENT,
                    'Base URL for links in the output'
                ],
                'css' => [
                    'c',
                    self::REQUIRED_ARGUMENT,
                    'Reference the indicated style sheet for the graph'
                ],
                'include' => [
                    'i',
                    self::REQUIRED_ARGUMENT,
                    'Include the contents of the indicated file into the output'
                    . ' before the nodes and edges'
                ],
                'no-edge-labels' => [
                    null,
                    self::NO_ARGUMENT,
                    'Do not output labels for edges'
                ],
                'no-http-method-labels' => [
                    null,
                    self::NO_ARGUMENT,
                    'Do include HTTP methods in node labels'
                ],
                'no-special-nodes' => [
                    null,
                    self::NO_ARGUMENT,
                    'Do not add START/END nodes'
                ]
            ],
            [
                'infile' => Operand::REQUIRED,
                'outfile' => Operand::REQUIRED
            ],
            'Extract a flow chart from the links properties'
        ]
    ];

    private $openApiFactory_;   ///< OpenApiFactory
    private $flowChartFactory_; ///< FlowchartFactory

    public function getOpenApiFactory(): OpenApiFactory
    {
        if (!isset($this->openApiFactory_)) {
            $this->openApiFactory_ = new OpenApiFactory();
        }

        return $this->openApiFactory_;
    }

    public function getFlowchartFactory(): FlowchartFactory
    {
        if (!isset($this->flowchartFactory_)) {
            $this->flowchartFactory_ = new FlowchartFactory();
        }

        return $this->flowchartFactory_;
    }

    public function process($arguments = null): int
    {
        parent::process($arguments);

        if (!$this->getCommand()) {
            $this->showHelp();
            return 0;
        }

        return $this->{$this->getCommand()->getHandler()}();
    }

    public function createFlowchart(): int
    {
        $openApi = $this->getOpenApiFactory()
            ->createFromUrl(
                (new FileUriFactory())->create($this->getOperand('infile'))
            );

        $flowchartOptions = 0;

        if ($this->getOption('no-edge-labels')) {
            $flowchartOptions |= Flowchart::NO_EDGE_LABELS;
        }

        if ($this->getOption('no-http-method-labels')) {
            $flowchartOptions |= Flowchart::NO_HTTP_METHOD_LABELS;
        }

        if ($this->getOption('no-special-nodes')) {
            $flowchartOptions |= Flowchart::NO_SPECIAL_NODES;
        }

        $flowchart = $this->getFlowchartFactory()->create(
            $openApi,
            $flowchartOptions,
            $this->getOption('base-url')
        );

        $include = [];

        if ($this->getOption('css')) {
            $include[] = "graph [stylesheet=\"{$this->getOption('css')}\"]";
        }

        if ($this->getOption('include')) {
            $include[] = file_get_contents($this->getOption('include'));
        }

        file_put_contents(
            $this->getOperand('outfile'),
            $flowchart->createDotCode(implode("\n\n", $include))
        );

        $this->reportProgress(
            sprintf(
                'Created %d nodes and %d edges',
                count($flowchart->getNodes()),
                count($flowchart->getEdges())
            )
        );

        return 0;
    }
}
