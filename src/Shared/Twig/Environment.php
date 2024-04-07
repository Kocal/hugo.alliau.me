<?php

namespace App\Shared\Twig;

use EasyCorp\Bundle\EasyAdminBundle\Twig\EasyAdminTwigExtension;
use Twig\Extension\GlobalsInterface;

/**
 * @see https://github.com/EasyCorp/EasyAdminBundle/issues/3715#issuecomment-1828630373
 */
final class Environment extends \Twig\Environment
{
    /**
     * @var class-string<GlobalsInterface>[]
     */
    private $extensionsToRefreshGlobals = [EasyAdminTwigExtension::class];

    public function mergeGlobals(array $context): array
    {
        foreach ($this->getExtensions() as $class => $extension) {
            if ($extension instanceof GlobalsInterface && in_array($class, $this->extensionsToRefreshGlobals, true)) {
                $this->refreshGlobals($extension->getGlobals());
            }
        }

        return parent::mergeGlobals($context);
    }

    private function refreshGlobals(array $freshGlobals): void
    {
        $ref = (new \ReflectionObject($this))->getParentClass();

        $globalsProperty = $ref->getProperty('globals');

        $resolvedGlobalsProperty = $ref->getProperty('resolvedGlobals');

        $extensionSetProperty = $ref->getProperty('extensionSet');
        $extensionSet = $extensionSetProperty->getValue($this);

        $refExtensionSet = (new \ReflectionObject($extensionSet));
        $extensionSetGlobalsProperty = $refExtensionSet->getProperty('globals');

        $globals = $globalsProperty->getValue($this);
        $resolvedGlobals = $resolvedGlobalsProperty->getValue($this);
        $extensionSetGlobals = $extensionSetGlobalsProperty->getValue($extensionSet);

        $globals = array_merge($globals, $freshGlobals);
        if ($resolvedGlobals !== null) {
            $resolvedGlobals = array_merge($resolvedGlobals, $freshGlobals);
        }
        if ($extensionSetGlobals !== null) {
            $extensionSetGlobals = array_merge($extensionSetGlobals, $freshGlobals);
        }

        $globalsProperty->setValue($this, $globals);
        $resolvedGlobalsProperty->setValue($this, $resolvedGlobals);
        $extensionSetGlobalsProperty->setValue($this, $extensionSetGlobals);
    }
}
