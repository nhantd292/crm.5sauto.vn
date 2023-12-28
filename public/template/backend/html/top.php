<?php
    // Thông tin user
    $userInfo = new \ZendX\System\UserInfo();
    $userInfo = $userInfo->getUserInfo();
    
    $name = $userInfo['name'];
    $avatar = URL_FILES . '/users/thumb/user-male.png';
    if(!empty($userInfo['avatar'])) {
        $avatar = URL_FILES . '/users/thumb/' . $userInfo['avatar'];
    }
?>

<div class="header navbar navbar-inverse">
	<div class="header-inner">
	
		<div class="hor-menu hidden-sm hidden-xs">
		    <?php include_once $this->arrParams['template']['pathHtml'] . '/menu/default.php';?>
		</div>
		
		<a href="javascript:;" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"><img src="<?php echo $this->arrParams['template']['urlImg'] . '/menu-toggler.png';?>" alt=""/></a>
		
		<ul class="nav navbar-nav pull-right">
<!--            <li class="header_notification_bar"  id="load_notifycation">-->
<!--                <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true">-->
<!--                    <i class="fa fa-bell"></i>-->
<!--                </a>-->
<!--            </li>-->

		    <li class="header_notification_bar hidden" id="notification_history_return">
				<a href="<?php echo $this->url('routeAdmin/type', array('controller' => 'contact', 'action' => 'filter', 'type' => 'history-return'));?>" title="Danh sách liên hệ cần chăm sóc lại ngày hôm nay">
    				<i class="fa fa-history"></i>
    				<span class="badge">0</span>
				</a>
			</li>

		    <li class="header_notification_bar" id="notification_contract_false">
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'notifi-user', 'action' => 'index'));?>" title="Thông báo đơn hàng bán sai">
    				<i class="fa fa-exclamation-circle"></i>
				</a>
			</li>

			<li class="dropdown user">
				<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true">
				    <img src="<?php echo $avatar;?>">
    				<span class="username"><?php echo $name;?></span>
    				<i class="fa fa-angle-down"></i>
				</a>
				<ul class="dropdown-menu">
					<li><a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'user', 'action' => 'change-password'));?>"><i class="fa fa-lock"></i> Đổi mật khẩu</a> </li>
					<li class="divider"></li>
					<li><a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'user', 'action' => 'logout'));?>"><i class="fa fa-key"></i> Đăng xuất</a> </li>
				</ul>
			</li>
		</ul>
	</div>
</div>
<div class="clearfix"></div>