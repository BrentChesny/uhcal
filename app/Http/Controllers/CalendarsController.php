<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Calendar;

class CalendarsController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $courses = $request->json()->all();

        if (count($courses) == 0)
            return response()->json(['error' => 'No courses selected!', 'code' => 422], 422);

        $calendar = Calendar::create();

        foreach ($courses as $course) {
            $calendar->courses()->create([
                'name' => $course['name'],
                'program' => $course['program'],
                'year' => $course['year']
            ]);
        }

        return response()->json([
            'id' => $calendar['id'],
            'url' => \App::make('url')->to('/calendars/' . $calendar['id'] . '.ics')
        ]);
    }

    public function show($id) {
        $calendar = Calendar::findOrFail($id);

        return response($calendar->ical()->render())
            ->withHeaders([
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $id . '.ics"'
            ]);
    }

    public function count() {
        return response()->json([
            'count' => Calendar::all()->count()
        ]);
    }
}
