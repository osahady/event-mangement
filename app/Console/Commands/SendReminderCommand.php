<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\Event;
use App\Notifications\ReminderSentNotification;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class SendReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends reminders to users who have events coming up';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::with('attendees.user')
                       ->withCount('attendees')
                       ->whereBetween('start_time', [now(), now()->addDay()])
                       ->get();

        $eventsCount = $events->count();
        $label = Str::plural('event', $eventsCount);


        // $events->each(fn ($event)
        //     => $event->attendees->each(fn ($attendee)
        //     => $this->notifyAttendee($attendee)));

        $events->each(function($event){
            $label2 = Str::plural('attendee', $event->attendees_count);
            $this->info("Event {$event->name} has {$event->attendees_count} {$label2}");
           $event->attendees->each(fn ($attendee)
            => $this->notifyAttendee($attendee));
        });

        $this->info("{$eventsCount} {$label} found");
    }

     private function notifyAttendee($attendee)
    {
        SendEmailJob::dispatch(
            new ReminderSentNotification($attendee->event),
            $attendee->user
        );
    }
}