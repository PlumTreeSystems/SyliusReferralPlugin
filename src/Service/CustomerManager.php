<?php


namespace PTS\SyliusReferralPlugin\Service;


use Doctrine\ORM\EntityManager;
use PTS\SyliusReferralPlugin\Entity\Customer;
use Sylius\Component\Core\Model\ShopUser;
use Symfony\Component\HttpFoundation\Request;

class CustomerManager
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getCustomerReferrals(Request $request, $route, $id)
    {
        $customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['id' => $id]);
        if ($customer) {
            $page = $request->get('page');
            $data = $this->getPaginatedCustomers($customer, $page, $route, $id);
            return $data;
        }
    }
    public function getEnroller($id) {
        $customerRepo = $this->entityManager->getRepository(Customer::class);
        /** @var Customer $customer */
        $customer = $customerRepo->findOneBy(['id' => $id]);
        if ($enroller = $customer->getEnroller()) {
            return $enroller;
        }
        return null;
    }

    public function getPaginatedCustomers($enroller, $page, $routeName, $id = null)
    {
        if (!$page || $page < 0) {
            $page = 1;
        }
        $limit = 7;
        $customers = $this->entityManager
            ->getRepository(Customer::class)
            ->getEnrolled($enroller, $limit, floor($page));
        $maxPages = ceil($customers->count() / $limit);
        $thisPage = (int)$page;
        if ($maxPages < $thisPage) {
            $thisPage = $maxPages;
        }

        return [
            'customers' => iterator_to_array($customers->getIterator()),
            'pagination' => [
                'maxPages' => $maxPages,
                'thisPage' => $thisPage,
                'route' => $routeName,
                'id' => $id
            ]
        ];
    }
    public function changeCustomerEnroller($customerId, $enrollerId)
    {
        $customerRepo = $this->entityManager
            ->getRepository(Customer::class);
        /** @var Customer $customer */
        $customer = $customerRepo
            ->findOneBy(['id' => $customerId]);
        $enroller = $customerRepo
            ->findOneBy(['id' => $enrollerId]);
        $customer->setEnroller($enroller);
        $this->entityManager->flush();
    }

}