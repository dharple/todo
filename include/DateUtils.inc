<?php

class DateUtils {

	function getNow() {
		return $this->getDate();
	}

	function getDate($date = 'now', $format = 'Y-m-d H:i:s') {
		$then = strtotime($date);
		$ret = date($format, $then);
		return $ret;
	}

	function getWeekStart($date = 'now', $format = 'Y-m-d 00:00:00') {
		$then = strtotime($date);
		$dow = date('w', $then);
		if($dow > 0)
			$weekstart = strtotime('last sunday', $then);
		else
			$weekstart = $then;
		$ret = date($format, $weekstart);
		return $ret;
	}

	function getWeekEnd($date = 'now', $format = 'Y-m-d 23:59:59') {
		$then = strtotime($date);
		$dow = date('w', $then);
		if($dow < 6)
			$weekend = strtotime('saturday', $then);
		else
			$weekend = $then;
		$ret = date($format, $weekend);
		return $ret;
	}

	function getMonthStart($date = 'now', $format = 'Y-m-d 00:00:00') {
		$then = strtotime($date);
		$monthstart = strtotime(date('Y-m-1 00:00:00', $then));
		$ret = date($format, $monthstart);
		return $ret;
	}

	function getMonthEnd($date = 'now', $format = 'Y-m-d 23:59:59') {
		$then = strtotime($date);
		$monthend = strtotime(date('Y-m-t 00:00:00', $then));
		$ret = date($format, $monthend);
		return $ret;
	}

}

?>
