<?php
namespace ZendX\Functions;

class Number {
    
    public function __construct() {
        
    }

    public function formatToData($dataSource){
        $result     = null;
        if($dataSource != '') {
            $result = preg_replace('/\D/', '', $dataSource);
            if(substr(trim($dataSource), 0, 1) == '-') {
                $result = '-'. preg_replace('/\D/', '', $dataSource);
            }
        }
		return (int)$result;
	}

	public function formatToNumber($dataSource){
	    $result = preg_replace('/\D/', '', $dataSource);
	    if(substr(trim($dataSource), 0, 1) == '-') {
	        $result = '-'. preg_replace('/\D/', '', $dataSource);
	    }
	    if(empty($result)) {
	        $result = 0;
	    }
	    return (int)$result;
	}

	public function formatToPhone($dataSource){
	    if(substr(trim($dataSource), 0, 1) != '0') {
	        $dataSource = '0'. $dataSource;
	    }
	    $result = preg_replace('/\D/', '', $dataSource);
	    return $result;
	}

	public function colorRevenue($dataSource){
	    $result = '#000';
	    if($dataSource >= 120) {
	        $result = '#9C27B0';
	    } elseif ($dataSource >= 100) {
	        $result = '#ff0000';
	    } elseif ($dataSource >= 70) {
	        $result = '#ff9900';
	    }
	    return $result;
	}
	
	public function dauSo($phone, $dauSo) {
	    $arr11 = array(
	        '0120' => '070',
	        '0121' => '079',
	        '0122' => '077',
	        '0126' => '076',
	        '0128' => '078',
	        '0123' => '083',
	        '0124' => '084',
	        '0125' => '085',
	        '0127' => '081',
	        '0129' => '082',
	        '0162' => '032',
	        '0163' => '033',
	        '0164' => '034',
	        '0165' => '035',
	        '0166' => '036',
	        '0167' => '037',
	        '0168' => '038',
	        '0169' => '039',
	        '0186' => '056',
	        '0188' => '058',
	        '0199' => '059',
	    );
	    $arr10 = array_flip($arr11);
	     
	    if(substr($phone, 0, 1) != 0 || substr($phone, 0, 1) != '0') {
	        $phone = '0'. $phone;
	    }
	
	    $phone10 = $phone;
	    $phone11 = $phone;
	
	    $dauso10 = substr($phone, 0, 3);
	    $soduoi10 = substr($phone, 3);
	    $dauso11 = substr($phone, 0, 4);
	    $soduoi11 = substr($phone, 4);
	
	    if(!empty($arr11[$dauso11])) {
	        $phone10 = $arr11[$dauso11] . $soduoi11;
	    } elseif (!empty($arr10[$dauso10])) {
	        $phone11 = $arr10[$dauso10] . $soduoi10;
	    }
	     
	    $return = $phone10;
	    if($dauSo == 11) {
	        $return = $phone11;
	    }
	    return $return;
	}
	
	public function convertString($dataSource) {
	    $number = $dataSource;
	    $hyphen = ' ';
	    $conjunction = '  ';
	    $separator = ' ';
	    $negative = 'âm ';
	    $decimal = ' phẩy ';
	    $dictionary = array(
	        0 => 'Không',
	        1 => 'Một',
	        2 => 'Hai',
	        3 => 'Ba',
	        4 => 'Bốn',
	        5 => 'Năm',
	        6 => 'Sáu',
	        7 => 'Bảy',
	        8 => 'Tám',
	        9 => 'Chín',
	        10 => 'Mười',
	        11 => 'Mười một',
	        12 => 'Mười hai',
	        13 => 'Mười ba',
	        14 => 'Mười bốn',
	        15 => 'Mười năm',
	        16 => 'Mười sáu',
	        17 => 'Mười bảy',
	        18 => 'Mười tám',
	        19 => 'Mười chín',
	        20 => 'Hai mươi',
	        30 => 'Ba mươi',
	        40 => 'Bốn mươi',
	        50 => 'Năm mươi',
	        60 => 'Sáu mươi',
	        70 => 'Bảy mươi',
	        80 => 'Tám mươi',
	        90 => 'Chín mươi',
	        100 => 'trăm',
	        1000 => 'nghìn',
	        1000000 => 'triệu',
	        1000000000 => 'tỷ',
	        1000000000000 => 'nghìn tỷ',
	        1000000000000000 => 'ngàn triệu triệu',
	        1000000000000000000 => 'tỷ tỷ'
	    );
	    
	    if( !is_numeric( $number ) )
	    {
	        return false;
	    }
	    
	    if( ($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX )
	    {
	        // overflow
	        trigger_error( '$this->convertString only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING );
	        return false;
	    }
	    
	    if( $number < 0 )
	    {
	        return $negative . $this->convertString( abs( $number ) );
	    }
	    
	    $string = $fraction = null;
	    
	    if( strpos( $number, '.' ) !== false )
	    {
	        list( $number, $fraction ) = explode( '.', $number );
	    }
	    
	    switch (true)
	    {
	        case $number < 21:
	            $string = $dictionary[$number];
	            break;
	        case $number < 100:
	            $tens = ((int)($number / 10)) * 10;
	            $units = $number % 10;
	            $string = $dictionary[$tens];
	            if( $units )
	            {
	                $string .= $hyphen . $dictionary[$units];
	            }
	            break;
	        case $number < 1000:
	            $hundreds = $number / 100;
	            $remainder = $number % 100;
	            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
	            if( $remainder )
	            {
	                $string .= $conjunction . $this->convertString( $remainder );
	            }
	            break;
	        default:
	            $baseUnit = pow( 1000, floor( log( $number, 1000 ) ) );
	            $numBaseUnits = (int)($number / $baseUnit);
	            $remainder = $number % $baseUnit;
	            $string = $this->convertString( $numBaseUnits ) . ' ' . $dictionary[$baseUnit];
	            if( $remainder )
	            {
	                $string .= $remainder < 100 ? $conjunction : $separator;
	                $string .= $this->convertString( $remainder );
	            }
	            break;
	    }
	    
	    if( null !== $fraction && is_numeric( $fraction ) )
	    {
	        $string .= $decimal;
	        $words = array( );
	        foreach( str_split((string) $fraction) as $number )
	        {
	            $words[] = $dictionary[$number];
	        }
	        $string .= implode( ' ', $words );
	    }
	    
	    return $string;
	}
}