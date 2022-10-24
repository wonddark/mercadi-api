<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RetireBid implements ProcessorInterface
{
    private ProcessorInterface $processor;

    public function __construct(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void {
        if ($data->isIsDeleteable()) {
            $this->processor->process(
                $data,
                $operation,
                $uriVariables,
                $context
            );
        } else {
            throw new UnprocessableEntityHttpException("Bid is not deletable");
        }
    }
}
