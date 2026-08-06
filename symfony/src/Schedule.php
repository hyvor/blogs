<?php

namespace App;

use App\Service\Blog\Message\HardDeleteBlogsMessage;
use App\Service\Hosting\CustomDomain\Message\RegenerateExpiredTlsCertificatesMessage;
use App\Service\Theme\RepoSync\Message\RepoSyncMessage;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

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
            ->add(RecurringMessage::every('1 day', new RegenerateExpiredTlsCertificatesMessage()))
            ->add(RecurringMessage::every('1 day', new HardDeleteBlogsMessage()))

            // add your own tasks here
            // see https://symfony.com/doc/current/scheduler.html#attaching-recurring-messages-to-a-schedule
        ;
    }
}
