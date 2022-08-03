<?php

namespace Tests\Unit\Helpers;

use Illuminate\Console\Scheduling\ManagesFrequencies;
use Illuminate\Console\Scheduling\Schedule;

class SchedulerFake
{
    /**
     * @param  class-string  $jobClass
     * @return void
     */
    public static function assertJobScheduled(string $jobClass, string|callable $frequency = null)
    {
        $schedule = app()->make(Schedule::class);
        $jobs = collect($schedule->events());

        $job = $jobs->firstWhere('description', $jobClass);
        expect($job)->not->toBeNull();

        if ($frequency !== null) {
            $cronExpression = is_callable($frequency) ?
                $frequency(new Expression())->expression :
                $frequency;

            expect($job->expression)->toBe($cronExpression);
        }
    }
}

class Expression
{
    public string $expression = '* * * * *';

    use ManagesFrequencies;
}
