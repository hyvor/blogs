<?php

namespace App\Service\Integration\HyvorTalk;

use App\Entity\User;
use App\Service\Delivery\TemplateRenderer\TemplateRenderingEvent;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserUpdatedEvent;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class HyvorTalkListener
{

    public function __construct(
        private HyvorTalkService $hyvorTalkService,
        private MessageBusInterface $bus,
        private InternalConfig $internalConfig
    ) {}

    /**
     * add the hyvor talk embed code to the comments template
     */
    #[AsEventListener]
    public function onTemplateRendering(TemplateRenderingEvent $event)
    {
        $ht = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($event->blog);

        if (!$ht) {
            return;
        }

        $variables = $event->getVariables();

        $code = HyvorTalkService::getEmbedCode($ht);
        $variables['_comments'] .= $code;

        $event->setVariables($variables);
    }

    /**
     * sync the user to hyvor talk when a user is created
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

        $currentRoleSynced = in_array($event->user->getRole(), HyvorTalkService::SYNCED_ROLES, true);
        $oldRoleSynced = in_array($event->userOld->getRole(), HyvorTalkService::SYNCED_ROLES, true);

        if ($currentRoleSynced && !$oldRoleSynced) {
            $this->dispatchSyncUser($event->user);
        } elseif (!$currentRoleSynced && $oldRoleSynced) {
            $this->dispatchSyncUser($event->user, delete: true);
        } elseif ($currentRoleSynced && $oldRoleSynced) {
            // still synced, but the mapped Hyvor Talk role (admin/mod) may have changed
            $this->dispatchSyncUser($event->user);
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

        $hyvorTalk = $this->hyvorTalkService->getHyvorTalkWebsiteOfBlog($user->getBlog());

        if (!$hyvorTalk) {
            return;
        }

        $role = HyvorTalkService::mapUserRole($user->getRole());

        if ($role === null && !$delete) {
            return;
        }

        $this->bus->dispatch(new SyncBlogUsersToWebsiteMessage(
            blogId: $user->getBlog()->getId(),
            hyvorUserId: $user->getHyvorUserId(),
            role: $role,
            delete: $delete
        ));
    }

}
