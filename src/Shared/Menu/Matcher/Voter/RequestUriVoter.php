<?php

namespace App\Shared\Menu\Matcher\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestUriVoter implements VoterInterface
{
    private string|null $uri;

    public function __construct(
        RequestStack $requestStack,
    ) {
        $this->uri = $requestStack->getMainRequest()?->getPathInfo();
    }

    public function matchItem(ItemInterface $item): ?bool
    {
        if (null === $this->uri || null === $item->getUri()) {
            return null;
        }

        return str_starts_with($this->uri, $item->getUri());
    }
}