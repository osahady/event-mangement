<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Notifications\ReminderSentNotification;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $delay = 5;
    /**
     * Create a new job instance.
     */
    public function __construct(public ReminderSentNotification $n, public User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->user->notify($this->n);
    }
}