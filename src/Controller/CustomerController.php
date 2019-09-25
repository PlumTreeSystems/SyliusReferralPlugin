<?php


namespace PTS\SyliusReferralPlugin\Controller;

use FOS\RestBundle\View\View;
use Http\Client\Exception\HttpException;
use http\Exception\InvalidArgumentException;
use PTS\SyliusReferralPlugin\Event\EnrollerChangeEvent;
use PTS\SyliusReferralPlugin\Repository\CustomerRepository;
use PTS\SyliusReferralPlugin\Service\CustomerManager;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Core\Model\ShopUser;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends ResourceController
{
    public function createAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::CREATE);

        $newResource = $this->newResourceFactory->create($configuration, $this->factory);

        $form = $this->resourceFormFactory->create($configuration, $newResource);

        $refManager = $this->get('app.referral.manager');

        $refManager->checkReferralValidity();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $newResource = $form->getData();

            $referrer = $refManager->getReferrerFromSession();
            if ($referrer) {
                $newResource->setEnroller($referrer);
            }

            $refManager->removeReferral();

            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::CREATE, $configuration, $newResource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                if ($event->hasResponse()) {
                    return $event->getResponse();
                }

                return $this->redirectHandler->redirectToIndex($configuration, $newResource);
            }

            if ($configuration->hasStateMachine()) {
                $this->stateMachine->apply($configuration, $newResource);
            }

            $this->repository->add($newResource);
            $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::CREATE, $configuration, $newResource);

            if (!$configuration->isHtmlRequest()) {
                return $this->viewHandler->handle($configuration, View::create($newResource, Response::HTTP_CREATED));
            }

            $this->flashHelper->addSuccessFlash($configuration, ResourceActions::CREATE, $newResource);

            if ($postEvent->hasResponse()) {
                return $postEvent->getResponse();
            }

            return $this->redirectHandler->redirectToResource($configuration, $newResource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::CREATE, $configuration, $newResource);
        if ($initializeEvent->hasResponse()) {
            return $initializeEvent->getResponse();
        }

        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $newResource,
                $this->metadata->getName() => $newResource,
                'form' => $form->createView(),
            ])
            ->setTemplate($configuration->getTemplate(ResourceActions::CREATE . '.html'));

        return $this->viewHandler->handle($configuration, $view);
    }
    public function adminShowAction(Request $request, $id): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $enabledEnrollerEdit = $this->getParameter('app_edit_enroller_enabled');

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        $resource = $this->findOr404($configuration);

        $this->eventDispatcher->dispatch(ResourceActions::SHOW, $configuration, $resource);

        $customerManager = $this->get('app.customer.manager');

        $routeName = $request->get('_route');

        $data = $customerManager->getCustomerReferrals($request, $routeName, $id);

        $enroller = $customerManager->getEnroller($id);

        $view = View::create($resource);

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate(ResourceActions::SHOW . '.html'))
                ->setTemplateVar($this->metadata->getName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resource' => $resource,
                    'customers' => $data['customers'],
                    'pagination' => $data['pagination'],
                    'enroller' => $enroller,
                    'enabledEditEnroller' => $enabledEnrollerEdit,
                    $this->metadata->getName() => $resource,
                ])
            ;
        }

        return $this->viewHandler->handle($configuration, $view);
    }
    public function adminEditEnrollerAction(Request $request, $id): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        $resource = $this->findOr404($configuration);

        $translator = $this->get('translator');
        $session = $this->get('session');
        $enabled = $this->getParameter('app_edit_enroller_enabled');
        if (!$enabled) {
            $session->getFlashBag()->add(
                'error',
                $translator->trans('app.messages.error.editEnrollerDisabled')
            );
            return $this->redirectToRoute('sylius_admin_customer_show', ['id' => $id]);
        }

        if ($request->query->has('_enroller') && $request->query->has('_action')) {
            $enrollerId = $request->query->get('_enroller');
            if ($enrollerId !== $id && $request->query->get('_action') === 'selectEnroller') {
                /** @var CustomerManager $customerManager */
                $customerManager = $this->get('app.customer.manager');
                $customerManager->changeCustomerEnroller($id, $enrollerId);
                /** @var CustomerRepository $customerRepository */
                $customerRepository = $this->get('sylius.repository.customer');
                $enroller = $customerRepository->getCustomerById($enrollerId);
                $enrollerChangeEvent = new EnrollerChangeEvent($resource, $enroller);
                /** @var EventDispatcher $eventDispatcher */
                $eventDispatcher = $this->get('debug.event_dispatcher');
                $eventDispatcher->dispatch($enrollerChangeEvent, EnrollerChangeEvent::NAME);
                $session->getFlashBag()->add(
                    'success',
                    $translator->trans('app.messages.success.changedEnroller')
                );
                return $this->redirectToRoute('sylius_admin_customer_show', ['id' => $id]);
            }
            else {
                throw new InvalidArgumentException("Invalid request. Can't choose customer as his own enroller");
            }
        }

        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);

        $this->eventDispatcher->dispatch(ResourceActions::SHOW, $configuration, $resource);

        $view = View::create($resource);

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate(ResourceActions::SHOW . '.html'))
                ->setTemplateVar($this->metadata->getName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resource' => $resource,
                    'resources' => $resources,
                    $this->metadata->getName() => $resource,
                ])
            ;
        }

        return $this->viewHandler->handle($configuration, $view);
    }

}