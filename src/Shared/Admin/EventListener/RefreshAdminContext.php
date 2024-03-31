<?php

namespace App\Shared\Admin\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Twig\EasyAdminTwigExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Twig\Environment;

/**
 * Workaround to refresh the EasyAdmin global variables in the Twig environment,
 * because of FrankenPHP's worker model, which causes the global variables to be cached between requests.
 *
 * @see https://github.com/EasyCorp/EasyAdminBundle/issues/5986#issuecomment-1857725801
 */
final class RefreshAdminContext implements EventSubscriberInterface
{
    public function __construct(private Environment $twig)
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            //Priority is set to 1 because it should be executed before CrudResponseListener (which has no priority, so it is 0 by default).
            ViewEvent::class => ['onKernelView', 1]
        ];
    }

    public function onKernelView(ViewEvent $event): void
    {
        $extensionGlobals = $this->twig->getExtension(EasyAdminTwigExtension::class)->getGlobals();
        $twigGlobals = $this->twig->getGlobals();

        foreach ($extensionGlobals as $key => $value) {
            if (!isset($twigGlobals[$key]) || $twigGlobals[$key] === $value) {
                continue;
            }
            //Update the global variable if it exists in the Twig environment and its value is different from that in the extension.
            $this->twig->addGlobal($key, $value);
        }
    }

}