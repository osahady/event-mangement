<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelations;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{

    use CanLoadRelations;

    public function __construct()
    {
        // $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('throttle:api')->only(['store', 'destroy']);
        $this->authorizeResource(Attendee::class, 'attendee');
    }

    private array $allowedRelations = ['user', 'event', 'user.events', 'event.user'];
    public function index(Event $event)
    {
        $attendees =  $this->loadRelations($event->attendees()->latest())->paginate(10);
        return AttendeeResource::collection($attendees);

    }


    public function store(Request $request, Event $event)
    {
        $attendee = $event->attendees()->create([
            'user_id' => 1,
        ]);
        return new AttendeeResource($this->loadRelations($attendee));
    }


    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelations($attendee));
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id, Attendee $attendee)
    {
        $attendee->delete();
        return response(status: 204);
    }
}
