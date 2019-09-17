<?php

namespace PTS\SyliusReferralPlugin\Service;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository;
use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductRepository;
use Sylius\Bundle\ResourceBundle\Storage\SessionStorage;
use Sylius\Component\Core\Model\Product;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ReferralManager
{
    static private $expirationDays = 7 * (24*60*60); // 7 days (h*m*s)

    /** @var SessionStorage */
    private $session;

    /** @var CustomerRepository */
    private $customerRepo;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var ProductRepository */
    private $productRepo;

    private $channelPaths;

    /** @var ChannelUrlManager */
    private $channelUrlManager;

    /**
     * ReferralManager constructor.
     * @param SessionStorage $session
     * @param CustomerRepository $customerRepo
     * @param TokenStorage $tokenStorage
     * @param UrlGeneratorInterface $router
     * @param ProductRepository $productRepo
     * @param $channelPaths
     * @param $channelUrlManager
     */
    public function __construct(SessionStorage $session,
                                CustomerRepository $customerRepo,
                                TokenStorage $tokenStorage,
                                UrlGeneratorInterface $router,
                                ProductRepository $productRepo,
                                $channelPaths,
                                $channelUrlManager
    ) {
        $this->session = $session;
        $this->customerRepo = $customerRepo;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->productRepo = $productRepo;
        $this->channelPaths = $channelPaths;
        $this->channelUrlManager = $channelUrlManager;
    }


    public function getReferrerFromSession()
    {
        if($this->session->has('referrer')) {
            $saved = json_decode($this->session->get('referrer'), true);
            return $this->customerRepo->findOneBy(['id' => $saved['id']]);
        }
        return null;
    }

    public function setReferral($id)
    {
        $data = [
            'timestamp' => time(),
            'id' => $id
        ];
        if ($this->session->has('referrer')) {
            $saved = json_decode($this->session->get('referrer'), true);
            if ($saved['id'] == $id) {
                return;
            }
        }
        $this->session->set('referrer', json_encode($data));
    }

    public function checkReferralValidity()
    {
        if ($this->session->has('referrer')) {
            $referrer = json_decode($this->session->get('referrer'), true);
            $setTime = $referrer['timestamp'];
            if (time() - $setTime > $this::$expirationDays) {
                $this->removeReferral();
            }
        }
    }

    public function removeReferral()
    {
        if ($this->session->has('referrer')) {
            $this->session->remove('referrer');
        }
    }

    public function generateProductLink($product, $customer, $locale = null, $basePath = '')
    {
        $distributorId = $customer->getid();
        $routeParams = [
            'refId' => $distributorId,
            'slug' => $product->getSlug()
        ];
        if ($locale) {
            $routeParams['_locale'] = $locale;
        }
        $url = $this->router->generate(
            'sylius_shop_product_show',
            $routeParams,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $requestUri = $this->router->generate(
            'sylius_shop_product_show',
            $routeParams,
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
        return $this->trimChannelName($url, $requestUri, $basePath);
    }

    public function referralProductLinkGenerator($id)
    {
        $customer = $this->tokenStorage->getToken()->getUser()->getCustomer();
        /** @var Product $product */
        $product = $this->productRepo->find($id);
        return $this->generateProductLink($product, $customer);
    }

    public function generateRootLink($customer, $locale = null, $basePath = '')
    {

        $distributorId = $customer->getid();
        $routeParams = [
            'refId' => $distributorId
        ];
        if ($locale) {
            $routeParams['_locale'] = $locale;
        }
        $url = $this->router->generate(
            'sylius_shop_homepage',
            $routeParams,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $requestUri = $this->router->generate(
            'sylius_shop_homepage',
            $routeParams,
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
        return $this->trimChannelName($url, $requestUri, $basePath);
    }

    public function referralRootLinkGenerator()
    {
        $customer = $this->tokenStorage->getToken()->getUser()->getCustomer();
        return $this->generateRootLink($customer);
    }

    private function trimChannelName($url, $requestUri, $basePath)
    {
        $urlParts = [];
        $splitUrl = explode('://', $url);
        $scheme = $splitUrl[0];
        $urlToBeSplit = $splitUrl[1];
        $urlParts['scheme'] = $scheme;
        $urlParts['host'] = substr($urlToBeSplit,0, strpos($urlToBeSplit,'/'));
        if ($basePath && $basePath !== '') {
            $urlParts['basePath'] = $basePath;
        }
        $urlParts['requestUri'] = $requestUri;
        return $this->channelUrlManager->formUrl($urlParts);
    }
}
