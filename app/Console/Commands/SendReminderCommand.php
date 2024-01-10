<?php

namespace App\Console\Commands;

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
                       ->whereBetween('start_time', [now(), now()->addDay()])
                       ->get();

        $eventsCount = $events->count();
        $label = Str::plural('event', $eventsCount);


        $events->each(fn ($event)
            => $event->attendees->each(fn ($attendee)
            => $this->notifyAttendee($attendee)));

        $this->info("{$eventsCount} {$label} found");
    }

     private function notifyAttendee($attendee)
    {
        // Simulate notification or any process delay
        // usleep(50000); // Example: 50ms delay
        sleep(2);
        // Output the notification at the top of the console
        // $this->info("Notifying user with id {$attendee->user->id}");
        $attendee->user->notify(new ReminderSentNotification($attendee->event));
    }
}