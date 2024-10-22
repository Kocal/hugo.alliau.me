<?php

declare(strict_types=1);

namespace App\PHPStan\Type;

use App\Shared\Domain\CQRS\CommandBus;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;

final readonly class CommandBusDispatchDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    #[\Override]
    public function getClass(): string
    {
        return CommandBus::class;
    }

    #[\Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'dispatch';
    }

    #[\Override]
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?\PHPStan\Type\Type
    {
        $command = $methodCall->getArgs()[0]->value;
        $commandType = $scope->getType($command);

        if (! $commandType->isObject()->yes()) {
            return null;
        }

        $commandClass = $commandType->getClassName();
        $commandHandlerClass = $this->getCommandHandlerClass($commandClass);
        $commandHandler = $this->reflectionProvider->getClass($commandHandlerClass);
        $commandInvoke = $commandHandler->getNativeMethod('__invoke');

        return ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->args, $commandInvoke->getVariants())->getReturnType();
    }

    private function getCommandHandlerClass(string $commandClass): string
    {
        $commandHandler = $commandClass . 'Handler';

        if ($this->reflectionProvider->hasClass($commandHandler)) {
            return $commandHandler;
        }

        $commandHandler = $commandClass . 'CommandHandler';

        if ($this->reflectionProvider->hasClass($commandHandler)) {
            return $commandHandler;
        }

        throw new \Exception(sprintf('Command handler for command %s not found', $commandClass));
    }
}
