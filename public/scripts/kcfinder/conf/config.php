<?php

$config = array(
    // GENERAL SETTINGS
    'disabled' => DISABLED,
    'denyZipDownload' => false,
    'denyUpdateCheck' => true, // true: không cho phép check update version
    'denyExtensionRename' => true, // true: không cho phép đổi phần mở rộng của file
    
    'uploadURL' => "../../files/upload" . UPLOAD_URL,
    'uploadDir' => "",
    
    'theme' => "me",

    'types' => array(

        // (F)CKEditor types
        'files'   =>  "",
        'flash'   =>  "swf",
        'images'  =>  "*img",

        // TinyMCE types
        'file'    =>  "",
        'media'   =>  "swf flv avi mpg mpeg qt mov wmv asf rm",
        'image'   =>  "*img",
    ),


    // IMAGE SETTINGS
    'imageDriversPriority' => "imagick gmagick gd",
    'jpegQuality' => 100,
    'thumbsDir' => "thumbs",
    'mediumDir' => "medium",

    'maxImageWidth' => 0,
    'maxImageHeight' => 0,

    'thumbWidth' => 200,
    'thumbHeight' => 150,
    
    'mediumWidth' => 600,
    'mediumHeight' => 450,
    
    'resizeType' => 'fit', //fit: Co đều theo tỷ lệ ảnh, crop: Co ảnh và cắt bằng đúng kích thước khai bào thumb 

    'watermark' => "",
    
    'maxFilesize' => 1024,

    // PERMISSION SETTINGS
    'dirPerms' => 0755,
    'filePerms' => 0644,

    'access' => array(

        'files' => array(
            'upload' => true,
            'delete' => true,
            'copy'   => true,
            'move'   => true,
            'rename' => true
        ),

        'dirs' => array(
            'create' => true,
            'delete' => true,
            'rename' => true
        )
    ),

    'deniedExts' => "js exe com msi bat cgi pl php phps phtml php3 php4 php5 php6 py pyc pyo pcgi pcgi3 pcgi4 pcgi5 pchi6",


    // MISC SETTINGS
    'filenameChangeChars' => array(
        'à'=>'a','ả'=>'a','ã'=>'a','á'=>'a','ạ'=>'a','ă'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ắ'=>'a','ặ'=>'a','â'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ấ'=>'a','ậ'=>'a',
        'À'=>'a','Ả'=>'a','Ã'=>'a','Á'=>'a','Ạ'=>'a','Ă'=>'a','Ằ'=>'a','Ắ'=>'a','Ẵ'=>'a','Ẳ'=>'a','Ặ'=>'a','Â'=>'a','Ầ'=>'a','Ẩ'=>'a','Ẫ'=>'a','Ấ'=>'a','Ậ'=>'a',
        'đ'=>"d",'Đ'=>"d",
        'è'=>'e','ẻ'=>'e','ẽ'=>'e','é'=>'e','ẹ'=>'e','ê'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ế'=>'e','ệ'=>'e',
        'È'=>'e','Ẻ'=>'e','Ẽ'=>'e','É'=>'e','Ẹ'=>'e','Ê'=>'e','Ề'=>'e','Ể'=>'e','Ễ'=>'e','Ế'=>'e','Ệ'=>'e',
        'ì'=>'i','ỉ'=>'i','ĩ'=>'i','í'=>'i','ị'=>'i',
        'Ì'=>'i','Ỉ'=>'i','Ĩ'=>'i','Í'=>'i','Ị'=>'i',
        'ò'=>'o','ỏ'=>'o','õ'=>'o','ó'=>'o','ọ'=>'o','ô'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ố'=>'o','ộ'=>'o','ơ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ớ'=>'o','ợ'=>'o',
        'Ò'=>'o','Ỏ'=>'o','Õ'=>'o','Ó'=>'o','Ọ'=>'o','Ô'=>'o','Ồ'=>'o','Ổ'=>'o','Ỗ'=>'o','Ố'=>'o','Ộ'=>'o','Ơ'=>'o','Ờ'=>'o','Ở'=>'o','Ỡ'=>'o','Ớ'=>'o','Ợ'=>'o',
        'ù'=>'u','ủ'=>'u','ũ'=>'u','ú'=>'u','ụ'=>'u','ư'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ứ'=>'u','ự'=>'u',
        'Ù'=>'u','Ủ'=>'u','Ũ'=>'u','Ú'=>'u','Ụ'=>'u','Ư'=>'u','Ừ'=>'u','Ử'=>'u','Ữ'=>'u','Ứ'=>'u','Ự'=>'u',
        'ỳ'=>'y','ỷ'=>'y','ỹ'=>'y','ý'=>'y',
        'Ỳ'=>'y','Ỷ'=>'y','Ỹ'=>'y','Ý'=>'y',
        ' ' => "-",'  ' => "-",'   ' => "-",'     ' => "-",
        '!'=>"-",'@'=>"-",'$'=>"-",'%'=>"-",'^'=>"-",'*'=>"-",'('=>"-",')'=>"-",'+'=>"-",'='=>"-",'<'=>"-",'>'=>"-",'?'=>"-",'/'=>"-",','=>"-",
        ':'=>"-",'\''=>"-",'\"'=>"-",'&'=>"-",'#'=>"-",'['=>"-",']'=>"-",'\\'=>"-","~"=>"-","_"=>"-","{"=>"-","}"=>"-","`"=>"-",
        ';' => "-"
    ),

    'dirnameChangeChars' => array(
        'à'=>'a','ả'=>'a','ã'=>'a','á'=>'a','ạ'=>'a','ă'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ắ'=>'a','ặ'=>'a','â'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ấ'=>'a','ậ'=>'a',
        'À'=>'a','Ả'=>'a','Ã'=>'a','Á'=>'a','Ạ'=>'a','Ă'=>'a','Ằ'=>'a','Ắ'=>'a','Ẵ'=>'a','Ẳ'=>'a','Ặ'=>'a','Â'=>'a','Ầ'=>'a','Ẩ'=>'a','Ẫ'=>'a','Ấ'=>'a','Ậ'=>'a',
        'đ'=>"d",'Đ'=>"d",
        'è'=>'e','ẻ'=>'e','ẽ'=>'e','é'=>'e','ẹ'=>'e','ê'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ế'=>'e','ệ'=>'e',
        'È'=>'e','Ẻ'=>'e','Ẽ'=>'e','É'=>'e','Ẹ'=>'e','Ê'=>'e','Ề'=>'e','Ể'=>'e','Ễ'=>'e','Ế'=>'e','Ệ'=>'e',
        'ì'=>'i','ỉ'=>'i','ĩ'=>'i','í'=>'i','ị'=>'i',
        'Ì'=>'i','Ỉ'=>'i','Ĩ'=>'i','Í'=>'i','Ị'=>'i',
        'ò'=>'o','ỏ'=>'o','õ'=>'o','ó'=>'o','ọ'=>'o','ô'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ố'=>'o','ộ'=>'o','ơ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ớ'=>'o','ợ'=>'o',
        'Ò'=>'o','Ỏ'=>'o','Õ'=>'o','Ó'=>'o','Ọ'=>'o','Ô'=>'o','Ồ'=>'o','Ổ'=>'o','Ỗ'=>'o','Ố'=>'o','Ộ'=>'o','Ơ'=>'o','Ờ'=>'o','Ở'=>'o','Ỡ'=>'o','Ớ'=>'o','Ợ'=>'o',
        'ù'=>'u','ủ'=>'u','ũ'=>'u','ú'=>'u','ụ'=>'u','ư'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ứ'=>'u','ự'=>'u',
        'Ù'=>'u','Ủ'=>'u','Ũ'=>'u','Ú'=>'u','Ụ'=>'u','Ư'=>'u','Ừ'=>'u','Ử'=>'u','Ữ'=>'u','Ứ'=>'u','Ự'=>'u',
        'ỳ'=>'y','ỷ'=>'y','ỹ'=>'y','ý'=>'y',
        'Ỳ'=>'y','Ỷ'=>'y','Ỹ'=>'y','Ý'=>'y',
        ' ' => "-",'  ' => "-",'   ' => "-",'     ' => "-",
        '!'=>"-",'@'=>"-",'$'=>"-",'%'=>"-",'^'=>"-",'*'=>"-",'('=>"-",')'=>"-",'+'=>"-",'='=>"-",'<'=>"-",'>'=>"-",'?'=>"-",'/'=>"-",','=>"-",'.'=>"-",
        ':'=>"-",'\''=>"-",'\"'=>"-",'&'=>"-",'#'=>"-",'['=>"-",']'=>"-",'\\'=>"-","~"=>"-","_"=>"-","{"=>"-","}"=>"-","`"=>"-",
        ';' => "-"
    ),

    'mime_magic' => "",

    'cookieDomain' => "",
    'cookiePath' => "",
    'cookiePrefix' => 'xxx_',
    
    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION SETTINGS
    '_sessionVar' => &$_SESSION['KCFINDER'],
    '_check4htaccess' => true,
    '_normalizeFilenames' => true,
    '_dropUploadMaxFilesize' => 2048,
    //'_tinyMCEPath' => "/tiny_mce",
    //'_cssMinCmd' => "java -jar /path/to/yuicompressor.jar --type css {file}",
    //'_jsMinCmd' => "java -jar /path/to/yuicompressor.jar --type js {file}",
);

return $config;