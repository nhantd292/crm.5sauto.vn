<?php
namespace ZendX\Functions;

class StringHelper {
    
    public function __construct() {
        
    }

    public function getSummary($dataSource, $leng = null){
        if($leng == null){
            $leng = 25;
        }
        $result = null;
        if(!empty($dataSource)) {
            $arr_source = explode(" ", $dataSource);
            if(count($arr_source) < $leng){
                $result = implode(" ", $arr_source);
            }
            else {
                $arr_summary = array_slice($arr_source, 0, $leng);
                $result = implode(" ", $arr_summary).'...';
            }
        }
		return $result;
    }
    public function badgeCount($count){
        if ($count)
        return '<span class="badge badge-success">'.$count.'</span>';
        return '<span class="badge badge-default">'.$count.'</span>';
    }

}