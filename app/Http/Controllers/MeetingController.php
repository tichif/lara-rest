<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Meeting;

class MeetingController extends Controller
{

    public function __construct(){

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $meetings = Meeting::all();
        foreach($meetings as $meeting){
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/'.$meeting->id,
                'method' => 'GET'
            ];
        }

        $response = [
            'msg' => 'All meetings',
            'meeting' => $meetings
        ];

        return response()->json($response, 200);
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie',
            'user_id' => 'required',
        ]);
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        $meeting = new Meeting([
            'title' => $title,
            'description' => $description,
            'time' => Carbon::createFromFormat('YmdHie',$time)
        ]);
        if($meeting->save()){
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/'.$meeting->id,
                'method' => 'GET'
            ];
            $response = [
                'msg' => 'Meeting Created',
                'meeting' => $meeting
            ];
    
            return response()->json($response, 201);
        }

        $response = [
            'msg' => 'Something went wrong',
        ];

        return response()->json($response, 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $meeting = Meeting::with('users')->where('id',$id)->firstOrFail();
        $meeting->view_meeting = [
            'href' => 'api/v1/meeting/',
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting Information',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request,[
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie',
            'user_id' => 'required',
        ]);
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        $meeting = Meeting::with('users')->findOrFail($id);

        if(!$meeting->users()->where('users.id',$user_id)->first()){
            return response()->json(['msg' => 'User is not registered for this meeting. Update failed'], 401);
        }

        $meeting->title = $title;
        $meeting->description = $description;
        $meeting->time = Carbon::createFromFormat('YmdHie',$time);

        if(!$meeting->update()){
            return response()->json(['msg' => 'Error during updating'], 404);
        }
        $meeting->view_meeting = [
            'href' => 'api/v1/meeting/'.$meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting updated',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        $users = $meeting->users;
        $meeting->users->detach();
        if(!$meeting->delete()){
            foreach($users as $user){
                $meeting->users->attach($user);
            }
            return response()->json(['msg'=> 'Deletion failed'],404);
        }

        $response = [
            'msg' => 'Meeting deleted',
            'create' => [
                'href' => '/api/v1/meeting',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];

        return response()->json($response,200);

    }
}
