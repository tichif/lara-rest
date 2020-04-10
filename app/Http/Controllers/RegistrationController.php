<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Meeting;

class RegistrationController extends Controller
{
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'meeting_id' => 'required',
            'user_id' => 'required',
        ]);

        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');

        $meeting = Meeting::findOrFail($meeting_id);
        $user = User::findOrFail($user_id);

        $message = [
            'msg' => 'User already registered for this meeting',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => '/api/v1/meeting/registration/'.$meeting->id,
                'method' => 'DELETE'
            ]
        ];

        if($meeting->users()->where('user_id', $user->id)->first()){
            return response()->json($message,404);
        }

        $meeting->users()->attach($user_id);
        $response = [
            'msg' => 'User registered for this meeting',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => '/api/v1/meeting/registration/'.$meeting->id,
                'method' => 'DELETE'
            ]
            ];       

        return response()->json($response, 201);
    }

    
    
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        $meeting->users()->detach();

        $response = [
            'msg' => 'User unregistered for this meeting',
            'meting' => $meeting,
            'user' => 'TBD',
            'register' => [
                'href' => '/api/v1/meeting/registration/'.$meeting->id,
                'method' => 'POST',
                'params' => 'user_id, meeting_id'
            ]
        ];

        return response()->json($response,200);
    }

   
}
