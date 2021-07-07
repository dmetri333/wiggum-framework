<?php
namespace wiggum\commons\util;

use \DateTime;

/**
 * @deprecated
 * 
 *
 */
class Date {

	/**
	 * returns the number of days have past since the date provided
	 *
	 * @param string $date
	 * @return int
	 */
	public static function dayCount($date) {
		return (int)((time() - strtotime($date))/86400);
	}
	
	/**
	 * 
	 * @param string $start
	 * @param string $end
	 * @return int
	 */
	public static function dayRange($start, $end) {
		$datetime1 = new DateTime($start);
		$datetime2 = new DateTime($end);
		$interval = $datetime1->diff($datetime2);
		return $interval->days;
		//return (int)((strtotime($end) - strtotime($start))/86400);
	}
	
	/**
	 * 
	 * @param int $time
	 */
	public static function relativeDate($time) {
		$today = strtotime(date('M j, Y'));
		$reldays = ($time - $today)/86400;

		if ($reldays >= 0 && $reldays < 1) {
			return 'Today';
		} else if ($reldays >= 1 && $reldays < 2) {
			return 'Tomorrow';
		} else if ($reldays >= -1 && $reldays < 0) {
			return 'Yesterday';
		}
	
		if (abs($reldays) < 7) {
			if ($reldays > 0) {
				$reldays = floor($reldays);
				return 'In ' . $reldays . ' day' . ($reldays != 1 ? 's' : '');
			} else {
				$reldays = abs(floor($reldays));
				return $reldays . ' day' . ($reldays != 1 ? 's' : '') . ' ago';
			}
		}
	
		if (abs($reldays) < 182) {
			return date('M j', $time ? $time : time());
		} else {
			return date('M j, Y', $time ? $time : time());
		}
	}

}
?>