<?php

namespace App;

use App\Service\Ai\Agent\Message\DeleteOldAiConversationsMessage;
use App\Service\Blog\Message\HardDeleteBlogsMessage;
use App\Service\Hosting\CustomDomain\Message\RegenerateExpiringTlsCertificatesMessage;
use App\Service\LinkAnalysis\Message\DispatchAllLinkAnalysisChecksMessage;
use App\Service\Post\Document\ClearStepsMessage;
use App\Service\Post\Message\PublishScheduledPostVariantsMessage;
use App\Service\Theme\RepoSync\Message\RepoSyncMessage;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/**
 * @codeCoverageIgnore
 */
#[AsSchedule]
class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private LockFactory $lockFactory,
    ) {}

    public function getSchedule(): SymfonySchedule
    {
        return new SymfonySchedule()
            ->lock($this->lockFactory->createLock('default-schedule')) // only run on one server

            ->add(RecurringMessage::cron('0 0 * * *', new RepoSyncMessage()))
            ->add(RecurringMessage::cron('0 0 * * *', new RegenerateExpiringTlsCertificatesMessage()))
            ->add(RecurringMessage::cron('0 0 * * *', new HardDeleteBlogsMessage()))
            ->add(RecurringMessage::cron('0 0 * * *', new DeleteOldAiConversationsMessage()))
            ->add(RecurringMessage::cron('0 0 * * *', new DispatchAllLinkAnalysisChecksMessage()))
            ->add(RecurringMessage::cron('0 0 * * *', new ClearStepsMessage()))
            ->add(RecurringMessage::every('1 minute', new PublishScheduledPostVariantsMessage()))

            // add your own tasks here
            // see https://symfony.com/doc/current/scheduler.html#attaching-recurring-messages-to-a-schedule
        ;
    }
}
