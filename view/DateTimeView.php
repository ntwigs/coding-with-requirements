<?php

namespace view;

class DateTimeView {

	public function show() {
		date_default_timezone_set('Europe/Stockholm');
		$day = date("l");
		$date = date("jS");
		$month = date("F");
		$year = date("Y");
		$time = date("h:i:s");

		$timeString = $day . ", the " . $date . " of " . $month . " " . $year . ", The time is " . $time;

		return '<p>' . $timeString . '</p>';
	}
}
