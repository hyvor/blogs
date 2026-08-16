<?php

namespace App\Service\Integration\HyvorPost;

use App\Entity\User;
use App\Service\Delivery\TemplateRenderer\TemplateRenderingEvent;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserUpdatedEvent;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class HyvorPostListener
{

    public function __construct(
        private HyvorPostService $hyvorPostService,
        private MessageBusInterface $bus,
        private InternalConfig $internalConfig
    ) {}

    /**
     * add the hyvor post embed code to the newsletter template
     */
    #[AsEventListener]
    public function onTemplateRendering(TemplateRenderingEvent $event)
    {
        $hp = $this->hyvorPostService->getHyvorPostOfBlog($event->blog);

        if (!$hp) {
            return;
        }

        $variables = $event->getVariables();

        $code = HyvorPostService::getEmbedCode($hp);
        $variables['_newsletter'] .= $code;

        $event->setVariables($variables);
    }

    /**
     * sync the user to hyvor post when a user is created
     */
    #[AsEventListener]
    public function onUserCreated(UserCreatedEvent $event): void
    {
        $this->dispatchSyncUser($event->user);
    }

    #[AsEventListener]
    public function onUserDeleted(UserDeletedEvent $event): void
    {
        $this->dispatchSyncUser($event->user, delete: true);
    }

    #[AsEventListener]
    public function onUserUpdate(UserUpdatedEvent $event): void
    {
        if ($event->user->getRole() === $event->userOld->getRole()) {
            return;
        }

        $currentRoleSynced = in_array($event->user->getRole(), HyvorPostService::SYNCED_ROLES, true);
        $oldRoleSynced = in_array($event->userOld->getRole(), HyvorPostService::SYNCED_ROLES, true);

        if ($currentRoleSynced && !$oldRoleSynced) {
            $this->dispatchSyncUser($event->user);
        } elseif (!$currentRoleSynced && $oldRoleSynced) {
            $this->dispatchSyncUser($event->user, delete: true);
        }
    }

    private function dispatchSyncUser(User $user, bool $delete = false): void
    {
        if (!$this->internalConfig->getDeployment()->isCloud()) {
            return;
        }

        // guest users
        if ($user->getHyvorUserId() === null) {
            return;
        }

        $hyvorPost = $this->hyvorPostService->getHyvorPostOfBlog($user->getBlog());

        if (!$hyvorPost) {
            return;
        }

        if (!in_array($user->getRole(), HyvorPostService::SYNCED_ROLES, true) && !$delete) {
            return;
        }

        $this->bus->dispatch(new SyncBlogUsersToNewsletterMessage(
            blogId: $user->getBlog()->getId(),
            hyvorUserId: $user->getHyvorUserId(),
            delete: $delete
        ));
    }

}
