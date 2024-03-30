<?php
$this->_userInfo = new \ZendX\System\UserInfo();
$curent_user = $this->_userInfo->getUserInfo();
$permission_ids = explode(',', $curent_user['permission_ids']);

$is_system = true;
$is_admin = true;
$is_marketing = true;
$is_sales = true;
$is_product = true;
$is_check_oder = true;
$is_accounting = true;

//    $is_system = false;
//    $is_admin = false;
//    $is_marketing = false;
//    $is_sales = false;
//    $is_product = false;
//    $is_check_oder = false;
//    $is_accounting = false;
//
//    if(in_array('system', $permission_ids)){
//        $is_system = true;
//    }
//    if(in_array('admin', $permission_ids)){
//        $is_admin = true;
//    }
//    if(in_array('marketing', $permission_ids)){
//        $is_marketing = true;
//    }
//    if(in_array('sales', $permission_ids)){
//        $is_sales = true;
//    }
//    if(in_array('product', $permission_ids)){
//        $is_product = true;
//    }
//    if(in_array('check_oder', $permission_ids)){
//        $is_check_oder = true;
//    }
//    if(in_array('accounting', $permission_ids)){
//        $is_accounting = true;
//    }
?>


<div class="page-sidebar navbar-collapse collapse">
    <ul class="page-sidebar-menu">
        <li class="sidebar-toggler-wrapper">
            <div class="sidebar-toggler hidden-phone"></div>
        </li>

        <?php if($is_system || $is_admin){?>
            <li class="start">
                <a href="<?php echo $this->url('routeReport/default', array('controller' => 'revenue', 'action' => 'index'));?>">
                    <i class="fa fa-home"></i>
                    <span class="title">Tổng quan</span><span class="selected"></span>
                </a>
            </li>
        <?php }?>


        <?php if($is_system || $is_admin || $is_marketing){?>
            <li>
                <a href="javascript:;">
                    <i class="fa fa-bar-chart"></i>
                    <span class="title">Báo cáo Marketing</span><span class="arrow"></span>
                </a>
                <ul class="sub-menu">
                    <!--				<li>-->
                    <!--					<a href="--><?php //echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'marketing', 'code' => 'overview'));?><!--">-->
                    <!--                        <i class="fa fa-dot-circle-o"></i>-->
                    <!--                        Báo cáo Marketing-->
                    <!--                    </a>-->
                    <!--				</li>-->
                    <li>
                        <a href="<?php echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'marketing', 'code' => 'overview12'));?>">
                            <i class="fa fa-dot-circle-o"></i>
                            Báo cáo MKT thành công
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'marketing', 'code' => 'overview13'));?>">
                            <i class="fa fa-dot-circle-o"></i>
                            Báo cáo MKT xuất hàng
                        </a>
                    </li>
                    <?php if($is_system || $is_admin){?>
                        <!--				<li>-->
                        <!--					<a href="--><?php //echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'marketing', 'code' => 'overview2'));?><!--">-->
                        <!--                        <i class="fa fa-dot-circle-o"></i>-->
                        <!--                        Báo cáo Marketing 2-->
                        <!--                    </a>-->
                        <!--				</li>-->
                        <!--				<li>-->
                        <!--					<a href="--><?php //echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'marketing', 'code' => 'overview22'));?><!--">-->
                        <!--                        <i class="fa fa-dot-circle-o"></i>-->
                        <!--                        Báo cáo Marketing 2 mới-->
                        <!--                    </a>-->
                        <!--				</li>-->
                    <?php }?>
<!--                    <li>-->
<!--                        <a href="--><?php //echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'marketing', 'code' => 'sources'));?><!--">-->
<!--                            <i class="fa fa-dot-circle-o"></i>-->
<!--                            Báo cáo kênh nguồn-->
<!--                        </a>-->
<!--                    </li>-->
                </ul>
            </li>
        <?php }?>

        <?php if($is_system || $is_admin || $is_sales){?>
            <li>
                <a href="javascript:;">
                    <i class="fa fa-bar-chart"></i>
                    <span class="title">Báo cáo Sales</span><span class="arrow"></span>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a href="<?php echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'sale', 'code' => 'sale3'));?>"><i class="fa fa-dot-circle-o"></i> Báo cáo doanh thu sale</a>
                    </li>
                    <li>
                        <a href="<?php echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'sale', 'code' => 'share'));?>"><i class="fa fa-dot-circle-o"></i> Báo cáo nhận số - Tỉ lệ chốt</a>
                    </li>
<!--                    <li>-->
<!--                        <a href="--><?php //echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'sale', 'code' => 'sale-store'));?><!--"><i class="fa fa-dot-circle-o"></i> Báo cáo doanh thu cửa hàng</a>-->
<!--                    </li>-->
<!--                    <li>-->
<!--                        <a href="--><?php //echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'sale', 'code' => 'sale2'));?><!--"><i class="fa fa-dot-circle-o"></i> Báo cáo sale chi tiết</a>-->
<!--                    </li>-->
                </ul>
            </li>
        <?php }?>

        <?php if($is_system || $is_admin || $is_check_oder){?>
            <li>
                <a href="javascript:;">
                    <i class="fa fa-bar-chart"></i>
                    <span class="title">Báo cáo giục đơn</span><span class="arrow"></span>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a href="<?php echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'check', 'code' => 'overview'));?>">
                            <i class="fa fa-dot-circle-o"></i>
                            Báo cáo giục đơn
                        </a>
                    </li>
                </ul>
            </li>
        <?php }?>

        <?php if($is_system || $is_admin || $is_accounting){?>
            <li>
                <a href="javascript:;">
                    <i class="fa fa-bar-chart"></i>
                    <span class="title">Báo cáo kế toán</span><span class="arrow"></span>
                </a>
                <ul class="sub-menu">
                    <li>
                        <a href="<?php echo $this->url('routeReport/default', array('controller' => 'index', 'action' => 'index', 'id' => 'acounting', 'code' => 'import'));?>">
                            <i class="fa fa-dot-circle-o"></i>
                            Báo cáo nhập hàng
                        </a>
                    </li>
                </ul>
            </li>
        <?php }?>
    </ul>
</div>