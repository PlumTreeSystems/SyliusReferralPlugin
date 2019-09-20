<?php


namespace PTS\SyliusReferralPlugin\Controller;

use PTS\SyliusReferralPlugin\Entity\Customer;
use PTS\SyliusReferralPlugin\Service\CustomerManager;
use Sylius\Component\Core\Model\ShopUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AccountController extends Controller
{
    public function referralAction(Request $request)
    {
        $token = $this->get('security.token_storage')->getToken();
        $customerManager = $this->get('app.customer.manager');
        if ($token) {
            /** @var ShopUser $user */
            $user = $token->getUser();
            if ($user && $user instanceof ShopUser) {
                $page = $request->get('page');
                $enroller = $user->getCustomer();
                $routeName = $request->get('_route');
                $data = $customerManager->getPaginatedCustomers($enroller, $page, $routeName);
                return $this->render(
                    'Account\customers.html.twig',
                    [
                        'customers' => $data['customers'],
                        'pagination' => $data['pagination']
                    ]
                );
            }
        }
        throw new BadRequestHttpException('Unauthorized to view this resource');
    }

    public function redirectAction(Request $request)
    {
        $token = $request->get('redirectToken');
        $url = $request->get('redirectUrl');
        if ($token) {
            $user = $this->get('sylius.repository.shop_user')->findOneBy(['redirectToken' => $token]);
            if ($user) {
                $user->setRedirectToken('');
                $this->get('doctrine.orm.default_entity_manager')->flush($user);
                $this->get('sylius.security.user_login')->login($user, 'shop');
                $currentChannel = $this->get('sylius.context.channel')->getChannel();
                $this->get('app.manager.cart')->checkCart($user->getCustomer(), $currentChannel, $request->getSession());
            }
        }
        if ($url) {
            return $this->redirect($url);
        } else {
            return $this->redirectToRoute('sylius_shop_homepage');
        }
    }
}
