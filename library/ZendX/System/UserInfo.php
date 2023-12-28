<?php
namespace ZendX\System;

use Zend\Session\Container;

class UserInfo {
	
	public function __construct(){
		$ssInfo	= new Container(APP_KEY . '_user');
	}
	
	public function storeInfo($data){
		$ssInfo	= new Container(APP_KEY . '_user');
		$ssInfo->setExpirationSeconds(720000);
		$ssInfo->user		      = $data['user'];
		$ssInfo->permission	      = $data['permission'];
		$ssInfo->permission_list  = $data['permission_list'];
	}
	
	public function storeUserInfo($dataUser){
	    $ssInfo		  = new Container(APP_KEY . '_user');
	    $ssInfo->user = $dataUser;
	}
	
	public function destroyInfo(){
		$ssInfo	= new Container(APP_KEY . '_user');
		$ssInfo->getManager()->getStorage()->clear(APP_KEY . '_user');
	}
	
	public function getUserInfo($element = null){
		$ssInfo		= new Container(APP_KEY . '_user');
		$userInfo	= $ssInfo->user;
		
		$result	= ($element == null) ? $userInfo : $userInfo[$element];
		return $result;
	}
	
	public function setUserInfo($key, $value){
		$ssInfo		= new Container(APP_KEY . '_user');
		$ssInfo->user[$key] = $value;
	}
	
	public function getPermissionInfo($element = null){
		$ssInfo			= new Container(APP_KEY . '_user');
		$permissionInfo	= $ssInfo->permission;
	
		$result	= ($element == null) ? $permissionInfo : $permissionInfo[$element];
		return $result;
	}

    public function getGroupInfo($element = null){
        $ssInfo		= new Container(APP_KEY . '_user');
        $groupInfo	= $ssInfo->group;
        $result	= ($element == null) ? $groupInfo : $groupInfo->$element;
        return $result;
    }

    public function getPermission($element = null){
        $ssInfo			= new Container(APP_KEY . '_user');
        $permissionInfo	= $ssInfo->permission;

        $result	= ($element == null) ? $permissionInfo : $permissionInfo[$element];
        return $result;
	}
	
    public function getPermissionOfUser($element = null){
        $ssInfo			= new Container(APP_KEY . '_user');
        $permissionInfo	= $ssInfo->user;

        $result	= ($element == null) ? $permissionInfo : $permissionInfo[$element];
        return $result;
    }
	
	public function getPermissionListInfo($element = null){
	    $ssInfo		= new Container(APP_KEY . '_user');
	    $permissionListInfo	= $ssInfo->permission_list;
	
	    $result	= ($element == null) ? $permissionListInfo : $permissionListInfo[$element];
	    return $result;
	}
}