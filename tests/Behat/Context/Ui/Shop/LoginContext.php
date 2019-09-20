<?php

declare(strict_types=1);

namespace Tests\PTS\SyliusReferralPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;

final class LoginContext implements Context
{
    private $loginPage;

    /**
     * LoginContext constructor.
     * @param $loginPage
     */
    public function __construct($loginPage)
    {
        $this->loginPage = $loginPage;
    }

    /**
     * @Then I should be at the login page
     */
    public function iShouldBeAtTheLoginPage()
    {
        $this->loginPage->verify();
    }
}
