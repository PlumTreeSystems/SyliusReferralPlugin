<?php


namespace PTS\SyliusReferralPlugin\Controller;

use PTS\SyliusReferralPlugin\Entity\Customer;
use Sylius\Component\Core\Model\ShopUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AccountController extends Controller
{
    public function referralAction(Request $request)
    {
        $token = $this->get('security.token_storage')->getToken();
        if ($token) {
            /** @var ShopUser $user */
            $user = $token->getUser();
            if ($user && $user instanceof ShopUser) {
                $page = $request->get('page');
                $enroller = $user->getCustomer();
                $data = $this->getPaginatedCustomers($enroller, $page);
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

    private function getPaginatedCustomers($enroller, $page)
    {
        if (!$page || $page < 0) {
            $page = 1;
        }
        $limit = 7;
        $customers = $this->getDoctrine()
            ->getRepository(Customer::class)
            ->getEnrolled($enroller, $limit, floor($page));
        $maxPages = ceil($customers->count()/$limit);
        $thisPage = (int) $page;
        if ($maxPages < $thisPage) {
            $thisPage = $maxPages;
        }

        return [
            'customers' => iterator_to_array($customers->getIterator()),
            'pagination' => [
                'maxPages' => $maxPages,
                'thisPage' => $thisPage,
                'route' => 'app_account_customers'
            ]
        ];
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
