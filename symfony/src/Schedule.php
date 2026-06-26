<?php

namespace App;

use App\Service\Theme\RepoSync\Message\RepoSyncMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Lock\LockFactory;

#[AsSchedule]
class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private LockFactory $lockFactory,
    ) {
    }

    public function getSchedule(): SymfonySchedule
    {
        return new SymfonySchedule()
            ->lock($this->lockFactory->createLock('default-schedule')) // only run on one server

            ->add(RecurringMessage::cron('0 0 * * *', new RepoSyncMessage()))

            // add your own tasks here
            // see https://symfony.com/doc/current/scheduler.html#attaching-recurring-messages-to-a-schedule
        ;
    }
}
