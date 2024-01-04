<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Event::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       try {
         $event = Event::create([
            'user_id' => 1,
            ...$request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            ]),
        ]);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event,
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
        return $event;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        try {
            $event->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ]));
        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event,

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

        if($event->delete()){
            return response()->json(status: 204);
        }else{
            return response()->json([
                'message' => 'Unable to delete event',
            ], 500);
        }


    }
}