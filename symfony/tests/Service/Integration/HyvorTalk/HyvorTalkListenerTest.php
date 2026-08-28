<?php

namespace App\Tests\Service\Integration\HyvorTalk;

use App\Entity\Enum\UserRole;
use App\Service\Delivery\PathMatcher;
use App\Service\Integration\HyvorTalk\HyvorTalkListener;
use App\Service\Integration\HyvorTalk\SyncBlogUsersToWebsiteMessage;
use App\Service\User\Event\UserCreatedEvent;
use App\Service\User\Event\UserDeletedEvent;
use App\Service\User\Event\UserUpdatedEvent;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\InterHyvorTalkWebsiteFactory;
use App\Tests\Factory\ThemeFileFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HyvorTalkListener::class)]
class HyvorTalkListenerTest extends KernelTestCase
{

    // 1. Comments code

    public function test_adds_code_when_ht_integration_enabled(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createIndexTwig($blog, '{{ _comments }}');
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 394]);

        $pathMatcher = $this->getService(PathMatcher::class);
        $response = $pathMatcher->match($blog, '/');

        $this->assertIsString($response->content);
        $this->assertStringContainsString(htmlspecialchars('hyvor-talk-comments'), $response->content);
        $this->assertStringContainsString('website-id=&quot;394&quot;', $response->content);
    }

    public function test_when_ht_integration_disabled(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createIndexTwig($blog, '{{ _comments }}');
        $pathMatcher = $this->getService(PathMatcher::class);
        $response = $pathMatcher->match($blog, '/');

        $this->assertSame('', $response->content);
    }

    // 2. User create

    public function test_no_sync_no_integration(): void
    {
        $blog = BlogFactory::createOne();
        $user = UserFactory::createOne(['blog' => $blog]);
        $this->getEd()->dispatch(new UserCreatedEvent($user));

        $transport = $this->transport('async');
        $transport->queue()->assertEmpty();
    }

    public function test_no_sync_when_user_role_not_synced(): void
    {
        $blog = BlogFactory::createOne();
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 394]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::CONTRIBUTOR]);
        $this->getEd()->dispatch(new UserCreatedEvent($user));

        $transport = $this->transport('async');
        $transport->queue()->assertEmpty();
    }

    public function test_sync_as_mod_when_editor(): void
    {
        $blog = BlogFactory::createOne();
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 394]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR]);
        $this->getEd()->dispatch(new UserCreatedEvent($user));

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);

        $message = $messages[0];
        $this->assertInstanceOf(SyncBlogUsersToWebsiteMessage::class, $message);
        $this->assertSame($blog->getId(), $message->blogId);
        $this->assertSame($user->getHyvorUserId(), $message->hyvorUserId);
        $this->assertSame('mod', $message->role);
        $this->assertFalse($message->delete);
    }

    public function test_sync_as_admin_when_admin(): void
    {
        $blog = BlogFactory::createOne();
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 394]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);
        $this->getEd()->dispatch(new UserCreatedEvent($user));

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);

        $message = $messages[0];
        $this->assertInstanceOf(SyncBlogUsersToWebsiteMessage::class, $message);
        $this->assertSame('admin', $message->role);
        $this->assertFalse($message->delete);
    }

    // 3. User delete

    public function test_sync_on_user_delete(): void
    {
        $blog = BlogFactory::createOne();
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 394]);
        $user = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR]);
        $this->getEd()->dispatch(new UserDeletedEvent($user));

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);

        $message = $messages[0];
        $this->assertInstanceOf(SyncBlogUsersToWebsiteMessage::class, $message);
        $this->assertSame($blog->getId(), $message->blogId);
        $this->assertSame($user->getHyvorUserId(), $message->hyvorUserId);
        $this->assertTrue($message->delete);
    }

    // 4. User update

    public function test_sync_on_user_update_role_change(): void
    {
        $blog = BlogFactory::createOne();
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 394]);
        $userOld = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::CONTRIBUTOR]);
        $user = clone $userOld;
        $user->setRole(UserRole::EDITOR);
        $this->getEd()->dispatch(new UserUpdatedEvent($user, $userOld));

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);

        $message = $messages[0];
        $this->assertInstanceOf(SyncBlogUsersToWebsiteMessage::class, $message);
        $this->assertSame($blog->getId(), $message->blogId);
        $this->assertSame($user->getHyvorUserId(), $message->hyvorUserId);
        $this->assertSame('mod', $message->role);
        $this->assertFalse($message->delete);
    }

    public function test_deletes_on_role_downgrade(): void
    {
        $blog = BlogFactory::createOne();
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 394]);
        $userOld = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR]);
        $user = clone $userOld;
        $user->setRole(UserRole::CONTRIBUTOR);
        $this->getEd()->dispatch(new UserUpdatedEvent($user, $userOld));

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);

        $message = $messages[0];
        $this->assertInstanceOf(SyncBlogUsersToWebsiteMessage::class, $message);
        $this->assertSame($blog->getId(), $message->blogId);
        $this->assertSame($user->getHyvorUserId(), $message->hyvorUserId);
        $this->assertTrue($message->delete);
    }

    public function test_resyncs_with_new_role_on_role_change_within_synced_roles(): void
    {
        $blog = BlogFactory::createOne();
        InterHyvorTalkWebsiteFactory::createOne(['blog' => $blog, 'website_id' => 394]);
        $userOld = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::EDITOR]);
        $user = clone $userOld;
        $user->setRole(UserRole::ADMIN);
        $this->getEd()->dispatch(new UserUpdatedEvent($user, $userOld));

        $transport = $this->transport('async');
        $messages = $transport->queue()->messages();
        $this->assertCount(1, $messages);

        $message = $messages[0];
        $this->assertInstanceOf(SyncBlogUsersToWebsiteMessage::class, $message);
        $this->assertSame('admin', $message->role);
        $this->assertFalse($message->delete);
    }

}
