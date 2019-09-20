<?php

declare(strict_types=1);

namespace Tests\PTS\SyliusReferralPlugin\Behat\Page\Shop\Account;

use Tests\PTS\SyliusReferralPlugin\Behat\Page\BasePage as SymfonyPage;

class ReferralPage extends SymfonyPage
{
    /**
     * {@inheritdoc}
     */
    public function getRouteName(): string
    {
        return 'app_account_customers';
    }
}
