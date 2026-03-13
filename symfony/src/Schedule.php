<?php

namespace App;

use App\Service\TlsCertificate\Message\GeneratePendingTlsCertificatesMessage;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule]
class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private LockFactory $lockFactory,
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): SymfonySchedule
    {
        return new SymfonySchedule()
            ->add(RecurringMessage::every('5 minutes', new GeneratePendingTlsCertificatesMessage()))

            ->lock($this->lockFactory->createLock('schedule'))
            ->stateful($this->cache) // ensure missed tasks are executed
            ->processOnlyLastMissedRun(true) // ensure only last missed task is run

            // add your own tasks here
            // see https://symfony.com/doc/current/scheduler.html#attaching-recurring-messages-to-a-schedule
        ;
    }
}
