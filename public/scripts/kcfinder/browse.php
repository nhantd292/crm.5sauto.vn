<?php

/** This file is part of KCFinder project
  *
  *      @desc Browser calling script
  *   @package KCFinder
  *   @version 3.12
  *    @author Pavel Tzonkov <sunhater@sunhater.com>
  * @copyright 2010-2014 KCFinder Project
  *   @license http://opensource.org/licenses/GPL-3.0 GPLv3
  *   @license http://opensource.org/licenses/LGPL-3.0 LGPLv3
  *      @link http://kcfinder.sunhater.com
  */

include_once '../../../public/define.php';
include_once PATH_LIBRARY . '/Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory(array(
    'Zend\Loader\StandardAutoloader' => array(
        'autoregister_zf' => true,
        'namespaces'	  => array(
            'ZendX'	      => PATH_LIBRARY . '/ZendX',
        ),
    )
));

// Set config value
$user = new ZendX\System\UserInfo();
$userInfo = $user->getUserInfo();

$disabled = $userInfo['id'] ? false : true;
$uploadURL = $userInfo['uploadConfig']['uploadURL'] ? '/'. $userInfo['uploadConfig']['uploadURL'] : '/default';

define('DISABLED', $disabled);
define('UPLOAD_URL', $uploadURL);

require PATH_SCRIPTS .'/kcfinder/core/bootstrap.php';
$browser = "kcfinder\\browser"; // To execute core/bootstrap.php on older
$browser = new $browser();      // PHP versions (even PHP 4)
$browser->action();
