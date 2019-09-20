<?php

namespace Tests\PTS\SyliusReferralPlugin\Behat\Context\Ui\Shop;

use Tests\PTS\SyliusReferralPlugin\Behat\Page\Shop\Account\ReferralPage;
use PTS\SyliusReferralPlugin\Entity\Customer;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository;
use PTS\SyliusReferralPlugin\Service\ReferralManager;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use FriendsOfBehat\PageObjectExtension\Page\PageInterface;
use PHPUnit\Framework\Assert;
use Sylius\Behat\Page\Shop\Account\DashboardPageInterface;
use Sylius\Behat\Service\SharedStorage;
use Sylius\Component\Core\Model\AdminUser;
use Sylius\Component\Core\Model\ShopUser;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ReferralContext implements Context
{

    /**
     * @var ReferralManager
     */
    private $referralManager;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var DashboardPageInterface
     */
    private $dashboardPage;

    /**
     * @var SharedStorage
     */
    private $sharedStorage;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /** @var FactoryInterface */
    private $adminUserFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var RepositoryInterface */
    private $customerGroupRepository;

    /** @var FactoryInterface */
    private $customerFactory;

    /** @var FactoryInterface */
    private $shopUserFactory;

    /** @var InvitationManager */
    private $inviteManager;

    /** @var PageInterface */
    private $contactPage;

    /** @var ReferralPage */
    private $referralPage;

    /**
     * ReferralContext constructor.
     * @param ReferralManager $referralManager
     * @param CustomerRepository $customerRepository
     * @param DashboardPageInterface $homePage
     * @param SharedStorage $sharedStorage
     * @param UrlGeneratorInterface $router
     * @param FactoryInterface $adminUserFactory
     * @param EntityManager $entityManager
     * @param RepositoryInterface $customerGroupRepository
     * @param FactoryInterface $customerFactory
     * @param FactoryInterface $shopUserFactory
     * @param PageInterface $contactPage
     * @param ReferralPage $referralPage
     */
    public function __construct(ReferralManager $referralManager,
                                CustomerRepository $customerRepository,
                                DashboardPageInterface $homePage,
                                SharedStorage $sharedStorage,
                                UrlGeneratorInterface $router,
                                FactoryInterface $adminUserFactory,
                                EntityManager $entityManager,
                                RepositoryInterface $customerGroupRepository,
                                FactoryInterface $customerFactory,
                                FactoryInterface $shopUserFactory,
                                PageInterface $contactPage,
                                ReferralPage $referralPage
    ) {
        $this->referralManager = $referralManager;
        $this->customerRepository = $customerRepository;
        $this->dashboardPage = $homePage;
        $this->sharedStorage = $sharedStorage;
        $this->router = $router;
        $this->adminUserFactory = $adminUserFactory;
        $this->entityManager = $entityManager;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerFactory = $customerFactory;
        $this->shopUserFactory = $shopUserFactory;
        $this->contactPage = $contactPage;
        $this->referralPage = $referralPage;
    }


    /**
     * @Then Customer :arg1 should have an enroller with email :arg2
     */
    public function userShouldHaveAnEnrollerWithEmail($arg1, $arg2)
    {
        /** @var Customer $registeredUser */
        $registeredUser = $this->customerRepository->findOneBy(['email' => $arg1]);
        $enrollerCustomer = $this->customerRepository->findOneBy(['email' => $arg2]);

        $actualEnroller = $registeredUser->getEnroller();
        Assert::assertEquals($enrollerCustomer->getEmail(), $actualEnroller->getEmail());
    }

    /**
     * @Given I have a referral link from customer :customer
     */
    public function iHaveAReferralLinkFromCustomer(Customer $customer)
    {
        $url = $this->referralManager->generateRootLink($customer, 'en_US');
        $this->sharedStorage->set('link', $url);
    }

    /**
     * @Given I have a referral link to the :product product from customer :customer
     */
    public function iHaveAReferralLinkToTheProductFromCustomer($product, $customer)
    {
        $url = $this->referralManager->generateProductLink($product, $customer, 'en_US');
        $this->sharedStorage->set('link', $url);
    }

    /**
     * @Given the store has a user, with role :role, with name :name, with email :email and with :password password, which has an :customer enroller
     * @param $role
     * @param $name
     * @param $email
     * @param $password
     * @param Customer $customer
     */
    public function theStoreHasAEnrolledUserWithRoleWithNameAndWithEmail($role, $name, $email, $password, Customer $customer)
    {
        $this->createUser($role, $name, $email, $password, '', $customer);
    }

    /**
     * @Given the store has a user, with role :role, with name :name, with email :email and with :password password
     * @param $role
     * @param $name
     * @param $email
     * @param $password
     */
    public function theStoreHasAUserWithRoleWithNameAndWithEmail($role, $name, $email, $password)
    {
        $this->createUser($role, $name, $email, $password);
    }

    private function createUser($role, $name, $email, $password, Customer $enroller = null)
    {
        $user = null;
        $name = explode(' ', $name);
        switch ($role) {
            case 'admin': {
                /** @var AdminUser $user */
                $user = $this->adminUserFactory->createNew();
                $user->addRole(AdminUser::DEFAULT_ADMIN_ROLE);
                $user->setFirstName($name[0]);
                $user->setLastName($name[1]);
                $user->setEmail($email);
                $user->setPlainPassword($password);
                $user->setEnabled(true);
            }
                break;
            default: {
                $group = $this->customerGroupRepository->findOneBy(['code' => $role]);
                if (!$group) {
                    throw new PendingException();
                }
                /** @var Customer $customer */
                $customer = $this->customerFactory->createNew();
                $customer->setGroup($group);
                $customer->setFirstName($name[0]);
                $customer->setLastName($name[1]);
                $customer->setEmail($email);
                if ($enroller) {
                    $customer->setEnroller($enroller);
                }
                $this->sharedStorage->set("customer", $customer);
                /** @var ShopUser $user */
                $user = $this->shopUserFactory->createNew();
                $user->setCustomer($customer);
                $user->setEmail($email);
                $user->setPlainPassword($password);
                $user->setEnabled(true);
                $user->addRole(ShopUser::DEFAULT_ROLE);
                $this->entityManager->persist($customer);
            }
        }
        $this->sharedStorage->set("user", $user);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @When I open account dashboard
     */
    public function iOpenAccountDashboard()
    {
        $this->dashboardPage->open();
    }

    /**
     * @When I change page parameter to :parameter
     */
    public function IopenAccountWithParameter($parameter)
    {
        $this->referralPage->open(['page' => $parameter]);
    }

    /**
     * @When I try to open account dashboard
     */
    public function iTryOpenAccountDashboard()
    {
        $this->dashboardPage->tryToOpen();
    }

    /**
     * @When I want to request contact
     */
    public function iWantToRequestContact()
    {
        $this->contactPage->open();
    }
}
