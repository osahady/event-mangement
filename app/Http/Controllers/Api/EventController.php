<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelations;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventController extends Controller
{
    use CanLoadRelations;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    private array $allowedRelations = ['user', 'attendees', 'attendees.user', 'attendees.user.events'];
    public function index()
    {
        $query = $this->loadRelations(Event::query());

        $events = $query->paginate(10);
        return EventResource::collection($events);
    }

    public function store(Request $request)
    {
       try {
         $event = Event::create([
            'user_id' => $request->user()->id,
            ...$request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            ]),
        ]);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => new EventResource($this->loadRelations($event)),
        ], 201);
       }catch (ValidationException $e){
              return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
              ], 422);
       }
       catch (\Exception $e) {
           return response()->json([
               'message' => 'Unable to create event',
               'error' => $e->getMessage(),
           ], 500);
       }

    }


    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return new EventResource($this->loadRelations($event));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {

        try {
            if($event->user_id !== $request->user()->id){
                return response()->json([
                    'message' => 'You are not authorized to update this event',
                ], 403);
            }
            $event->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ]));
        return response()->json([
            'message' => 'Event updated successfully',
            'event' => new EventResource($this->loadRelations($event)),

        ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors(),
            ], 422);
        }catch (Exception $e){
            return response()->json([
                'message' => 'Unable to update event',
                'error' => $e->getMessage(),
            ]);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try{

            $event = Event::findOrFail($id);
        }catch (ModelNotFoundException $e){
            return response()->json([
                'message' => 'Exception Handling: Event not found',
            ], 404);
        }

        if($event->user_id !== request()->user()->id){
                return response()->json([
                    'message' => 'You are not authorized to delete this event',
                ], 403);
            }

        if($event->delete()){
            return response()->json(status: 204);
        }else{
            return response()->json([
                'message' => 'Unable to delete event',
            ], 500);
        }



   }
}
