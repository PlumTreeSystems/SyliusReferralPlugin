<?php

namespace Tests\PTS\SyliusReferralPlugin\Behat\Page;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

abstract class BasePage extends SymfonyPage implements SymfonyPageInterface
{
    /**
     * Overload to verify if the current url matches the expected one. Throw an exception otherwise.
     *
     * @param array $urlParameters
     *
     * @throws UnexpectedPageException
     */
    protected function verifyUrl(array $urlParameters = []): void
    {
        $actual = $this->getRequestUri($this->getSession()->getCurrentUrl());
        $expected = $this->getRequestUri($this->getUrl($urlParameters));
        if ($actual !== $expected) {
            throw new UnexpectedPageException(sprintf(
                'Expected to be on "%s" but found "%s" instead',
                $expected,
                $actual
            ));
        }
    }

    private function getRequestUri($url)
    {
        $parts = parse_url($url);
        return $parts['path'].'?'.$parts['query'].'#'.$parts['fragment'];
    }
}