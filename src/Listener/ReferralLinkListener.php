<?php

namespace PTS\SyliusReferralPlugin\Listener;

use PTS\SyliusReferralPlugin\Entity\Customer;
use PTS\SyliusReferralPlugin\Service\ReferralManager;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ReferralLinkListener
{

    /**
     * @var ReferralManager
     */
    private $referralManager;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * ReferralLinkListener constructor.
     * @param ReferralManager $referralManager
     * @param TokenStorage $tokenStorage
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        ReferralManager $referralManager,
        TokenStorage $tokenStorage,
        CustomerRepository $customerRepository
    ) {
        $this->referralManager = $referralManager;
        $this->tokenStorage = $tokenStorage;
        $this->customerRepository = $customerRepository;
    }


    public function onRequest(GetResponseEvent $event)
    {
        $this->checkUser();
        $this->referralManager->checkReferralValidity();
        $token = $this->tokenStorage->getToken();
        if (isset($token) &&
            $token->getUser() === 'anon.'
        ) {
            if ($event->getRequest()->query->has('refId')) {
                $id = $event->getRequest()->query->get('refId');
                $this->checkIdValidity($id);
                $this->referralManager->setReferral($id);
            }
        }
    }

    private function checkUser()
    {
        $token = $this->tokenStorage->getToken();
        if (isset($token) &&
            $token->getUser() !== 'anon.'
        ) {
            $this->referralManager->removeReferral();
        }
    }

    private function checkIdValidity($id)
    {
        /** @var Customer $idOwner */
        $idOwner = $this->customerRepository->findOneBy(['id' => $id]);
        if (!$idOwner) {
            throw new HttpException(400, 'Invalid referral');
        }
    }
}
