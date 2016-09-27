<?php
namespace wiggum\commons\util;

use \wiggum\commons\logging\Logger;

class CSV {
	
	private $labels;
	
	/**
	 * 
	 */
	public function __construct() {
	}
	
	/**
	 * 
	 * @param array $labels
	 */
	public function setLabels(array $labels) {
		$this->labels = $labels;
	}
	
	/**
	 * create and add contacts to database from a csv file
	 * the expected format of the csv file is:
	 * firstName, lastName, jobTitle, company, workPhone, fax, mobilePhone, email, streetAddress, state, city, postal, country, groups
	 * 0		, 1       , 2       , 3      , 4        , 5  , 6          , 7    , 8            , 9    , 10  , 11    , 12     , 13
	 * 	
	 * @param string $csv - path to csv file
	 * @return array
	 */
	public function parse($csv) {
		
		$handle = fopen($csv, 'r');
		$output = array();
		$labels = array();
		
		if($handle) {
			$count = 0;
			
			while(($data = fgetcsv($handle)) !== false) {
				
				//skip first line
				if($count == 0 ) {
					$labels = isset($this->labels) ? $this->labels : $data;
					$count++;
					continue;
				}
				
				$row = array_combine($labels, $data);
				if ($row) {
					array_push($output, $row);
				}
				
				$count++;
				
			}
			Logger::debug("parsed {$count} lines", 'CSV');
		} else {
			Logger::debug("could not get file handle", 'CSV');
		}
		fclose($handle);		
		
		return $output;
	}

}
?>