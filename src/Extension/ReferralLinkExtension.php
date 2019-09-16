<?php


namespace PTS\SyliusReferralPlugin\Extension;

use PTS\SyliusReferralPlugin\Service\ReferralManager;
use Twig\Extension\AbstractExtension;

class ReferralLinkExtension extends AbstractExtension
{

    /** @var ReferralManager */
    private $referralManager;

    /**
     * ReferralLinkExtension constructor.
     * @param ReferralManager $referralManager
     */
    public function __construct(ReferralManager $referralManager)
    {
        $this->referralManager = $referralManager;
    }


    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('referralLink', [$this, 'referralProductLinkGenerator']),
            new \Twig_SimpleFunction('referralRootLink', [$this, 'referralRootLinkGenerator'])
        ];
    }

    public function referralProductLinkGenerator($id)
    {
        return $this->referralManager->referralProductLinkGenerator($id);
    }

    public function referralRootLinkGenerator()
    {
        return $this->referralManager->referralRootLinkGenerator();
    }
}
