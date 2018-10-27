<?php

namespace App\Services;

use DateTime;

/*
 * CalFileParser
 *
 * Parser for iCal and vCal files. Reads event information and
 * outputs data into an Array or JSON
 *
 * @author Michael Mottola <mikemottola@gmail.com>
 * @license MIT
 * @version 1.0
 *
 */
class CalFileParser
{
    private $_base_path = './';
    private $_file_name = '';
    private $_output = 'array';
    private $DTfields = array('dtstart', 'dtend', 'dtstamp', 'created', 'last-modified');
    private $timezone = null;

    public function __construct()
    {
        $this->_default_output = $this->_output;
    }

    public function set_base_path($path)
    {
        if (isset($path)) {
            $this->_base_path = $path;
        }
    }

    public function set_file_name($filename)
    {
        if (!empty($filename)) {
            $this->_file_name = $filename;
        }
    }

    public function set_output($output)
    {
        if (!empty($output)) {
            $this->_output = $output;
        }
    }

    public function get_base_path()
    {
        return $this->_base_path;
    }

    public function get_file_name()
    {
        return $this->_file_name;
    }

    public function get_output()
    {
        return $this->_output;
    }

    /**
     * Read File
     *
     * @param string $file
     * @return string
     *
     * @example
     *  read_file('schedule.vcal')
     *  read_file('../2011-08/'schedule.vcal');
     *  read_file('http://michaelencode.com/example.vcal');
     */
    public function read_file($file = '')
    {
        if (empty($file)) {
            $file = $this->_file_name;
        }

        // check to see if file path is a url
        if (preg_match('/^(http|https):/', $file) === 1) {
            return $this->read_remote_file($file);
        }

        //empty base path if file starts with forward-slash
        if (substr($file, 0, 1) === '/') {
            $this->set_base_path('');
        }

        if (!empty($file) && file_exists($this->_base_path . $file)) {
            $file_contents = file_get_contents($this->_base_path . $file);
            return $file_contents;
        } else {
            return false;
        }
    }

    /**
     * Read Remote File
     * @param $file
     * @return bool|string
     */
    public function read_remote_file($file)
    {
        if (!empty($file)) {
            $data = file_get_contents($file);
            if ($data !== false) {
                return $data;
            }
        }
        return false;
    }

    /**
     * Parse
     * Parses iCal or vCal file and returns data of a type that is specified
     * @param string $file
     * @param string $output
     * @return mixed|string
     */
    public function parse($file = '', $output = '')
    {
        $file_contents = $this->read_file($file);

        if ($file_contents === false) {
            return 'Error: File Could not be read';
        }

        if (empty($output)) {
            $output = $this->_output;
        }

        if (empty($output)) {
            $output = $this->_default_output;
        }

        $events_arr = array();


        // fetch timezone to create datetime object
        if (preg_match('/X-WR-TIMEZONE:(.+)/i', $file_contents, $timezone) === 1) {
            $date = DateTime::createFromFormat('e', trim($timezone[1]));
            if ($date !== false) {
                $this->timezone = $date->getTimezone();
            }
        }

        //put contains between start and end of VEVENT into array called $events
        preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $file_contents, $events);

        if (!empty($events)) {
            foreach ($events[0] as $event_str) {

                //remove begin and end "tags"
                $event_str = trim(str_replace(array('BEGIN:VEVENT','END:VEVENT'), '', $event_str));

                //convert string of entire event into an array with elements containing string of 'key:value'
                $event_key_pairs = $this->convert_event_string_to_array($event_str);

                //convert array of 'key:value' strings to an array of key => values
                $events_arr[] = $this->convert_key_value_strings($event_key_pairs);
            }
        }

        $this->_output = $this->_default_output;

        return $this->output($events_arr, $output);
    }

    /**
     * Output
     * outputs data in the format specified
     *
     * @param $events_arr
     * @param string $output
     * @return mixed
     */
    private function output($events_arr, $output = 'array')
    {
        switch ($output) {
            case 'json':
                return json_encode($events_arr);
                break;
            default:
                return $events_arr;
                break;
        }
    }

    /**
     * Convert event string to array
     * accepts a string of calendar event data and produces array of 'key:value' strings
     * See convert_key_value_strings() to convert strings to
     * @param string $event_str
     * @return array
     */
    private function convert_event_string_to_array($event_str = '')
    {
        if (!empty($event_str)) {
            //replace new lines with a custom delimiter
            $event_str = preg_replace("/[\r\n]/", "%%", $event_str);

            if (strpos(substr($event_str, 2), '%%') == '0') { //if this code is executed, then file consisted of one line causing previous tactic to fail
                $tmp_piece = explode(':', $event_str);
                $num_pieces = count($tmp_piece);

                $event_str = '';
                foreach ($tmp_piece as $key => $item_str) {
                    if ($key != ($num_pieces -1)) {

                        //split at spaces
                        $tmp_pieces = preg_split('/\s/', $item_str);

                        //get the last whole word in the string [item]
                        $last_word = end($tmp_pieces);

                        //adds delimiter to front and back of item string, and also between each new key
                        $item_str = trim(str_replace(array($last_word,' %%' . $last_word), array('%%' . $last_word . ':', '%%' . $last_word), $item_str));
                    }

                    //build the event string back together, piece by piece
                    $event_str .= trim($item_str);
                }
            }

            //perform some house cleaning just in case
            $event_str = str_replace('%%%%', '%%', $event_str);

            if (substr($event_str, 0, 2) == '%%') {
                $event_str = substr($event_str, 2);
            }

            //break string into array elements at custom delimiter
            $return = explode('%%', $event_str);
        } else {
            $return = array();
        }

        return $return;
    }

    /**
     * Parse Key Value String
     * accepts an array of strings in the format of 'key:value' and returns an array of keys and values
     * @param array $event_key_pairs
     * @return array
     */
    private function convert_key_value_strings($event_key_pairs = array())
    {
        $event = array();

        if (!empty($event_key_pairs)) {
            foreach ($event_key_pairs as $line) {
                if (empty($line)) {
                    continue;
                }

                if ($line[0] == ' ') {
                    $event[$key] .= substr($line, 1);
                } else {
                    list($key, $value) = explode(':', $line, 2);
                    $key = strtolower(trim($key));

                    // autoconvert datetime fields to DateTime object
                    if (in_array($key, $this->DTfields)) {
                        $dt_str = str_replace(array('T', 'Z'), array('-', ''), $value);
                        $date = DateTime::createFromFormat('Ymd-His', $dt_str, $this->timezone);
                        if ($date !== false) {
                            $value = $date;
                        }
                    }
                    $event[$key] = $value;
                }
            }
        }

        // unescape every element if string.
        return array_map(function ($value) {
            return (is_string($value) ? stripcslashes($value) : $value);
        }, $event);
    }
}
