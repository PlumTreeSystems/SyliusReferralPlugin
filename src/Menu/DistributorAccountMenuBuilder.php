<?php

namespace PTS\SyliusReferralPlugin\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DistributorAccountMenuBuilder
{
    public const EVENT_NAME = 'sylius.menu.shop.account';

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param FactoryInterface $factory
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(FactoryInterface $factory, EventDispatcherInterface $eventDispatcher)
    {
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     */
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setLabel('sylius.menu.shop.account.header');

        $menu
            ->addChild('dashboard', ['route' => 'sylius_shop_account_dashboard'])
            ->setLabel('sylius.menu.shop.account.dashboard')
            ->setLabelAttribute('icon', 'home')
        ;
        $menu
            ->addChild('personal_information', ['route' => 'sylius_shop_account_profile_update'])
            ->setLabel('sylius.menu.shop.account.personal_information')
            ->setLabelAttribute('icon', 'user')
        ;
        $menu
            ->addChild('change_password', ['route' => 'sylius_shop_account_change_password'])
            ->setLabel('sylius.menu.shop.account.change_password')
            ->setLabelAttribute('icon', 'lock')
        ;
        $menu
            ->addChild('address_book', ['route' => 'sylius_shop_account_address_book_index'])
            ->setLabel('sylius.menu.shop.account.address_book')
            ->setLabelAttribute('icon', 'book')
        ;
        $menu
            ->addChild('order_history', ['route' => 'sylius_shop_account_order_index'])
            ->setLabel('sylius.menu.shop.account.order_history')
            ->setLabelAttribute('icon', 'cart')
        ;
        $menu
            ->addChild('customers', ['route' => 'app_account_customers'])
            ->setLabel('app.menu.shop.account.customers')
            ->setLabelAttribute('icon', 'star')
        ;

        $this->eventDispatcher->dispatch(self::EVENT_NAME, new MenuBuilderEvent($this->factory, $menu));

        return $menu;
    }
}
