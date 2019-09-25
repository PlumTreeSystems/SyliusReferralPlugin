<?php


namespace PTS\SyliusReferralPlugin\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class EnrollerManager
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getEnroller()
    {
        $enroller = $this->tokenStorage->getToken()->getUser()->getCustomer()->getEnroller();
        return $enroller;
    }
}