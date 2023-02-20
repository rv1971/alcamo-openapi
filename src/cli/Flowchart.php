<?php

namespace alcamo\openapi\cli;

use alcamo\json\JsonPtr;
use alcamo\openapi\OpenApi;

class Flowchart
{
    public const NO_EDGE_LABELS = 1;

    public const NO_HTTP_METHOD_LABELS = 2;

    public const NO_SPECIAL_NODES = 4;

    public const HTTP_METHODS = [
        'get', 'put', 'post', 'delete', 'options', 'head', 'path', 'trace'
    ];

    private $openApi_;    ///< OpenApi
    private $options_;    ///< int
    private $baseUrl_;    ///< ?string
    private $nodes_ = []; ///< array of AbstractFlowchartNode
    private $edges_ = []; ///< array of AbstractFlowchartEdge

    public function __construct(
        OpenApi $openApi,
        int $options,
        ?string $baseUrl
    ) {
        $this->openApi_ = $openApi;

        $this->options_ = (int)$options;

        $this->baseUrl_ = $baseUrl;

        $this->collectNodesAndEdges();

        if (!($this->options_ & self::NO_SPECIAL_NODES)) {
            $this->addSpecialNodes();
        }
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi_;
    }

    public function getOptions(): int
    {
        return $this->options_;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl_;
    }

    public function getNodes(): array
    {
        return $this->nodes_;
    }

    public function getEdges(): array
    {
        return $this->edges_;
    }

    public function addSpecialNodes(): void
    {
        $startNode = new SpecialFlowchartNode($this, 'START');
        $startNodeId = $startNode->getId();

        $endNode = new SpecialFlowchartNode($this, 'END');
        $endNodeId = $endNode->getId();

        $this->nodes_[$startNodeId] = $startNode;

        $this->nodes_[$endNodeId] = $endNode;

        foreach ($this->nodes_ as $id => $node) {
            if ($node instanceof OperationFlowchartNode) {
                if (!$node->getInEdges()) {
                    $edge = new SpecialFlowchartEdge(
                        $this,
                        $startNode,
                        $node
                    );

                    $this->edges_[$edge->getId()] = $edge;
                } elseif (!$node->getOutEdges()) {
                    $edge = new SpecialFlowchartEdge(
                        $this,
                        $node,
                        $endNode
                    );

                    $this->edges_[$edge->getId()] = $edge;
                }
            }
        }
    }

    public function createDotCode(?string $include = null): string
    {
        $result = [ 'digraph {', (string)$include ];

        foreach ($this->nodes_ as $node) {
            $result[] = $node->createDotCode();
        }

        foreach ($this->edges_ as $edge) {
            $result[] = $edge->createDotCode();
        }

        $result[] = '}';

        return implode("\n\n", $result);
    }

    protected function collectNodesAndEdges(): void
    {
        foreach ($this->openApi_->getRoot()->paths as $path) {
            foreach (static::HTTP_METHODS as $method) {
                if (isset($path->$method)) {
                    foreach ($path->$method->responses as $response) {
                        if (isset($response->links)) {
                            $fromOperation = $path->$method;

                            if (
                                isset($fromOperation->{'x-graphviz-hints'})
                                && isset($fromOperation->{'x-graphviz-hints'}->ignore)
                                && $fromOperation->{'x-graphviz-hints'}->ignore
                            ) {
                                continue;
                            }

                            $fromOperationPtr =
                                (string)$fromOperation->getJsonPtr();

                            $fromNode =
                                $this->nodes_[$fromOperationPtr] ?? null;

                            if (!isset($fromNode)) {
                                $fromNode = new OperationFlowchartNode(
                                    $this,
                                    $fromOperation
                                );

                                $this->nodes_[$fromOperationPtr] = $fromNode;
                            }

                            foreach ($response->links as $link) {
                                if (isset($link->operationRef)) {
                                    $toOperationPtr =
                                        ltrim($link->operationRef, '#');
                                    $toOperation = $this->openApi_->getNode(
                                        JsonPtr::newFromString($toOperationPtr)
                                    );
                                } else {
                                    $toOperation = $this->openApi_
                                        ->getOperation($link->operationId);
                                    $toOperationPtr =
                                        (string)$toOperation->getJsonPtr();
                                }

                                if (
                                    isset($toOperation->{'x-graphviz-hints'})
                                    && isset($toOperation->{'x-graphviz-hints'}->ignore)
                                    && $toOperation->{'x-graphviz-hints'}->ignore
                                ) {
                                    continue;
                                }

                                $toNode =
                                    $this->nodes_[$toOperationPtr] ?? null;

                                if (!isset($toNode)) {
                                    $toNode = new OperationFlowchartNode(
                                        $this,
                                        $toOperation
                                    );

                                    $this->nodes_[$toOperationPtr] = $toNode;
                                }

                                $edge = new LinkFlowchartEdge(
                                    $this,
                                    $fromNode,
                                    $toNode,
                                    $response,
                                    $link
                                );

                                $this->edges_[$edge->getId()] = $edge;
                            }
                        }
                    }
                }
            }
        }
    }
}
