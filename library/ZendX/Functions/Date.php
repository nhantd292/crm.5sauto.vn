<?php
namespace ZendX\Functions;

class Date {
    
    public function __construct() {
        
    }

    public function formatToData($dataSource, $format = 'Y-m-d H:i:s'){
        $result = null;
        if(!empty($dataSource)) {
            $dataSource = explode(' ', $dataSource);
    
            if(strpos($dataSource[0], '/') > 0) {
                $dateValue  = explode('/', $dataSource[0]);
            } else {
                $dateValue = explode('-', $dataSource[0]);
            }
            
            if((int)$dateValue[0] > 1000) {
                $result = str_replace('Y', $dateValue[0], $format);
                $result = str_replace('m', $dateValue[1], $result);
                $result = str_replace('d', $dateValue[2], $result);
            } else {
                $result = str_replace('Y', $dateValue[2], $format);
                $result = str_replace('m', $dateValue[1], $result);
                $result = str_replace('d', $dateValue[0], $result);
            }
            
            $timeValue  = array('00', '00', '00');
            if(!empty($dataSource[1])) {
                $time   = explode(':', $dataSource[1]);
                $timeValue[0]  = $time[0] ? $time[0] : '00';
                $timeValue[1]  = $time[1] ? $time[1] : '00';
                $timeValue[2]  = $time[2] ? $time[2] : '00';
            }
            
            $result = str_replace('H', $timeValue[0], $result);
            $result = str_replace('i', $timeValue[1], $result);
            $result = str_replace('s', $timeValue[2], $result);
        }
		return $result;
	}

    public function formatToSearch($dataSource, $format = 'Y-m-d'){
        $result = null;
        if(!empty($dataSource)) {
            $dataSource = explode(' ', $dataSource);

            if(strpos($dataSource[0], '/') > 0) {
                $dateValue  = explode('/', $dataSource[0]);
            } else {
                $dateValue = explode('-', $dataSource[0]);
            }

            if((int)$dateValue[0] > 1000) {
                $result = str_replace('Y', $dateValue[0], $format);
                $result = str_replace('m', $dateValue[1], $result);
                $result = str_replace('d', $dateValue[2], $result);
            } else {
                $result = str_replace('Y', $dateValue[2], $format);
                $result = str_replace('m', $dateValue[1], $result);
                $result = str_replace('d', $dateValue[0], $result);
            }

            $timeValue  = array('00', '00', '00');
            if(!empty($dataSource[1])) {
                $time   = explode(':', $dataSource[1]);
                $timeValue[0]  = $time[0] ? $time[0] : '00';
                $timeValue[1]  = $time[1] ? $time[1] : '00';
                $timeValue[2]  = $time[2] ? $time[2] : '00';
            }

            $result = str_replace('H', $timeValue[0], $result);
            $result = str_replace('i', $timeValue[1], $result);
            $result = str_replace('s', $timeValue[2], $result);
        }
		return $result;
	}

    public function formatToView($dataSource, $format = 'd/m/Y'){
        $result = null;
        if(!empty($dataSource)) {
            $dataSource = explode(' ', $dataSource);
            
            if(strpos($dataSource[0], '/') > 0) {
                $dateValue  = explode('/', $dataSource[0]);
            } else {
                $dateValue = explode('-', $dataSource[0]);
            }
            
            if((int)$dateValue[0] > 1000) {
                $result = str_replace('Y', $dateValue[0], $format);
                $result = str_replace('m', $dateValue[1], $result);
                $result = str_replace('d', $dateValue[2], $result);
            } else {
                $result = str_replace('Y', $dateValue[2], $format);
                $result = str_replace('m', $dateValue[1], $result);
                $result = str_replace('d', $dateValue[0], $result);
            }
            
            $timeValue  = array('00', '00', '00');
            if(!empty($dataSource[1])) {
                $time   = explode(':', $dataSource[1]);
                $timeValue[0]  = $time[0] ? $time[0] : '00';
                $timeValue[1]  = $time[1] ? $time[1] : '00';
                $timeValue[2]  = $time[2] ? $time[2] : '00';
            }
            
            $result = str_replace('H', $timeValue[0], $result);
            $result = str_replace('i', $timeValue[1], $result);
            $result = str_replace('s', $timeValue[2], $result);
        }
		return $result;
	}
	
	public function diff($date_start, $date_end = null, $type = 'day') {
	    $date_start = strtotime($this->formatToData($date_start, 'Y-m-d H:i:s'));
	    if($date_end == null) {
	        $date_end = date('d/m/Y H:i:s');
	    }
	    $date_end = strtotime($this->formatToData($date_end, 'Y-m-d H:i:s'));
	    
	    $diff = $date_end - $date_start;
	    
	    if($type == 'hour') {
	        $diff = $diff/60/60;
	    } else {
	        $diff = floor($diff/60/60/24);
	    }
	    
	    return $diff;
	}
	
	public function sub($date_point, $number, $format = 'Y-m-d H:i:s') {
	    $date_point = date_create($this->formatToData($date_point, 'Y-m-d H:i:s'));
        date_sub($date_point, date_interval_create_from_date_string($number ." days"));
	    
	    return date_format($date_point, $format);
	}
	
	public function sub_month($date_point, $number, $format = 'Y-m-d') {
	    $date_point = date_create($this->formatToData($date_point, 'Y-m-d'));
        date_sub($date_point, date_interval_create_from_date_string($number ." months"));
	    
	    return date_format($date_point, $format);
	}
	
	public function add($date_point, $number, $format = 'Y-m-d H:i:s') {
	    $date_point = date_create($this->formatToData($date_point, 'Y-m-d H:i:s'));
	    date_add($date_point, date_interval_create_from_date_string($number ." days"));
	     
	    return date_format($date_point, $format);
	}

    function check_date_format_to_data($date_string){
        list($dd,$mm,$yyyy) = explode('/', $date_string);
        if (!checkdate($mm,$dd,$yyyy)) {
            return false;
        }
        return true;
    }

    function date_to_string($date){
        if(empty($date)) {
            return "No date provided";
        }

        $periods         = array("giây", "phút", "giờ", "ngày", "tuần", "tháng", "năm", "thế kỷ");
        $lengths         = array("60","60","24","7","4.35","12","10");

        $now             = time();
        $unix_date         = strtotime($date);

        // check validity of date
        if(empty($unix_date)) {
            return "Bad date";
        }

        // is it future date or past date
        if($now > $unix_date) {
            $difference     = $now - $unix_date;
            $tense         = "trước";

        } else {
            $difference     = $unix_date - $now;
            $tense         = "bây giờ";
        }

        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);


        return "$difference $periods[$j] {$tense}";
    }
}