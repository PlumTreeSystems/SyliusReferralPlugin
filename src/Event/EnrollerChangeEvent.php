<?php


namespace PTS\SyliusReferralPlugin\Event;


use PTS\SyliusReferralPlugin\Entity\Customer;
use Symfony\Contracts\EventDispatcher\Event;

class EnrollerChangeEvent extends Event
{
    public const NAME = 'enroller.changed';

    protected $customer;

    protected $enroller;

    public function __construct(Customer $customer, Customer $enroller)
    {
        $this->customer = $customer;
        $this->enroller = $enroller;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function getEnroller()
    {
        return $this->enroller;
    }
}