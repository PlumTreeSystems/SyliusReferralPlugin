<?php


namespace PTS\SyliusReferralPlugin\Entity;

use Sylius\Component\Core\Model\Customer as BaseCustomer;

class Customer extends BaseCustomer
{
    private $enroller;

    /**
     * Customer constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getEnroller()
    {
        return $this->enroller;
    }

    /**
     * @param mixed $enroller
     */
    public function setEnroller($enroller): void
    {
        $this->enroller = $enroller;
    }

}
