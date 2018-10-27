<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Services\CalFileParser;

class Calendar extends Model
{
    use Uuids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public function courses()
    {
        return $this->hasMany('App\Course');
    }

    public function ical()
    {
        $vCalendar = new \Eluceo\iCal\Component\Calendar('www.example.com');

        $courses = $this->courses()->get();

        foreach ($courses as $course) {
            $events = $this->getEvents($course['program'], $course['year']);

            foreach ($events as $event) {
                if (
                  array_key_exists('description', $event) &&
                  strpos($event['description'], $course['name']) !== false
                ) {
                    $vEvent = new \Eluceo\iCal\Component\Event();

                    $parts = explode(';', $event['description']);
                    if (count($parts) >= 3) {
                        $location = trim($parts[2]);

                        $vEvent->setDtStart($event['dtstart'])
                            ->setDtEnd($event['dtend'])
                            ->setSummary($course['name'])
                            ->setLocation($location)
                            ->setDescription($event['description'])
                            ->setIsPrivate(false);
                    } else {
                        $vEvent->setDtStart($event['dtstart'])
                            ->setDtEnd($event['dtend'])
                            ->setSummary($event['summary'])
                            ->setDescription($event['description'])
                            ->setIsPrivate(false);
                    }

                    $vCalendar->addComponent($vEvent);
                }
            }
        }

        return $vCalendar;
    }

    private function getEvents($program, $year)
    {
        $url = "http://collegeroosters.uhasselt.be/{$program}_{$year}.ics";
        $ical = new CalFileParser();
        return $ical->parse($url);
    }
}
