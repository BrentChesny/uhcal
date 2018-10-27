<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Services\CalFileParser;
use Illuminate\Http\File;
use Illuminate\Http\Request;


class CoursesController extends Controller
{
    public function programs()
    {
		$contents = $this->fetchProgramsPage();
		$programs = $this->getPrograms($contents);

    	return response()->json($programs);
    }

    public function courses($year, $id)
    {
        $url = "http://collegeroosters.uhasselt.be/{$id}_{$year}.ics";
        $ical = new CalFileParser();
        $events = $ical->parse($url);

        $events = array_filter($events, function($event) {
            return array_key_exists('description', $event) && substr( $event['description'], 0, 3 ) !== ' ()';
        });

        $courses = array_values(array_unique(array_map(function($event) {
            $name = explode(";", $event['description'])[0];

            if (strpos($name, '-')) {
                $pos = strpos($name, '-');
                $part = substr($name, $pos-1, 3);
                if ($part == ' - ' && trim(substr($name, 0, $pos)) == strtoupper(trim(substr($name, 0, $pos)))) {
                    $name = trim(substr($name, 0, $pos));
                }
            }

            return $name;
        }, $events)));

        return response()->json($courses);
    }

    private function fetchProgramsPage()
    {
        return mb_convert_encoding(file_get_contents('http://collegeroosters.uhasselt.be/OverzichtTopFrame.html'), 'ISO-8859-1', 'UTF-8');
    }

    private function getPrograms($content)
    {
        // Fetch id's
		    preg_match_all("/opt.value=\"Frame_(.*)__(.*).html\";/m", $content, $ids);
        $years = $ids[1];
        $ids = $ids[2];

        // Fetch program names
        preg_match_all("/opt.text=\"(.*)\";/m", $content, $names);
        $names = $names[1];
        array_shift($names);

        $programs = [];
        for ($i = 0; $i < count($ids); $i++) {
            $programs[] = [
                'id' => $ids[$i],
                'year' => $years[$i],
                'name' => $names[$i],
            ];
        }

        $programs = array_values(array_filter($programs, function($program) {
            return $program['name'] != "";
        }));

		return $programs;
    }
}
