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

    $is_system = false;
//    $is_admin = false;
//    $is_marketing = false;
//    $is_sales = false;
//    $is_product = false;
//    $is_check_oder = false;
//    $is_accounting = false;
//
    if(in_array('system', $permission_ids)){
        $is_system = true;
    }

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
<ul class="nav navbar-nav">

    <!--Hệ thống-->
	<li class="parent">
        <a href="<?php echo $this->url('routeHome');?>" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
	       	<i class="fa fa-home"></i> <span class="title">Hệ thống</span><span class="arrow"></span>
        </a>
        <ul class="dropdown-menu">

        	<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'index', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-calendar"></i> <span class="title">Bàn làm việc</span><span class="arrow"></span>
				</a>
			</li>

        	<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'logs', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-clock-o"></i> <span class="title">Lịch sử hệ thống</span><span class="arrow"></span>
				</a>
			</li>

			<li class="divider"></li>
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'api', 'action' => 'delete-cache'));?>" target="_self">
					<i class="fa fa-battery-full"></i> <span class="title">Xóa cache</span><span class="arrow"></span>
				</a>
			</li>
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'index', 'action' => 'backup'));?><!--" target="_self">-->
<!--					<i class="fa fa-database"></i> <span class="title">Backup dữ liệu</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
		</ul>
	</li>

    <!--Nhân sự-->
	<li class="parent">
		<a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
			<i class="fa fa-users"></i> <span class="title">Nhân sự</span><span class="arrow"></span>
		</a>
		<ul class="dropdown-menu">
            <li class="dropdown-submenu">
                <a href="javascript:;" target="_self">
                    <i class="fa fa-folder-o"></i> <span class="title">Sơ đồ tổ chức</span><span class="arrow"></span>
                </a>
                <ul class="dropdown-menu" style="display: none;">
                    <li>
                        <a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'sale-branch', 'action' => 'index'));?>" target="_self">
                            <i class="fa fa-sitemap"></i> <span class="title">Cơ sở</span><span class="arrow"></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'company-department', 'action' => 'index'));?>" target="_self">
                            <i class="fa fa-sitemap"></i> <span class="title">Phòng ban</span><span class="arrow"></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'lists-group', 'action' => 'index'));?>" target="_self">
                            <i class="fa fa-sitemap"></i> <span class="title">Đội nhóm</span><span class="arrow"></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'company-position', 'action' => 'index'));?>" target="_self">
                            <i class="fa fa-sitemap"></i> <span class="title">Vị trí/Chức vụ</span><span class="arrow"></span>
                        </a>
                    </li>
                </ul>
            </li>

            <li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'user', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-user"></i> <span class="title">Nhân viên</span><span class="arrow"></span>
				</a>
			</li>

<!--            <li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'check-in', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-calendar-check-o"></i> <span class="title">Báo cáo chấm công</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->

            <li class="divider"></li>

            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'dynamic', 'action' => 'index'));?>" target="_self">
                    <i class="fa fa-folder-o"></i> <span class="title">Cấu hình chuyên mục</span><span class="arrow"></span>
                </a>
            </li>

            <li class="dropdown-submenu">
                <a href="javascript:;" target="_self">
                    <i class="fa fa-folder-o"></i> <span class="title">Zalo</span><span class="arrow"></span>
                </a>
                <ul class="dropdown-menu" style="display: none;">
                    <li>
                        <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'zalo-notify-config', 'action' => 'index'));?>" target="_self">
                            <i class="fa fa-cog"></i> <span class="title">Cấu hình thông báo</span><span class="arrow"></span>
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'zalo-notify-result', 'action' => 'index'));?>" target="_self">
                            <i class="fa fa-cog"></i> <span class="title">Kết quả gửi thông báo</span><span class="arrow"></span>
                        </a>
                    </li>
                </ul>
            </li>

            <?php if($curent_user['id'] = '1111111111111111111111'){?>
            <li>
                <a href="<?php echo $this->url('routeAdminNested/default', array('controller' => 'setting', 'action' => 'index', 'code' => 'System'));?>" target="_self">
                    <i class="fa fa-cog"></i> <span class="title">Cấu hình hệ thống</span><span class="arrow"></span>
                </a>
            </li>
            <?php }?>
            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'permission', 'action' => 'index'));?>" target="_self">
                    <i class="fa fa-users"></i> <span class="title">Nhóm quyền truy cập</span><span class="arrow"></span>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'permission-list', 'action' => 'index'));?>" target="_self">
                    <i class="fa fa-universal-access"></i> <span class="title">Danh sách quyền truy cập</span><span class="arrow"></span>
                </a>
            </li>
        </ul>
	</li>

    <!--Marketing-->
    <li class="parent">
        <a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
            <i class="fa fa-database"></i> <span class="title">Marketing</span><span class="arrow"></span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'form-data', 'action' => 'index'));?>" target="_self">
                    <i class="fa fa-database"></i> <span class="title">Data Marketing</span><span class="arrow"></span>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contact-data', 'action' => 'index'));?>" target="_self">
                    <i class="fa fa-share-alt"></i> <span class="title">Chia data marketing</span><span class="arrow"></span>
                </a>
            </li>
            <li class="dropdown-submenu">
                <a href="javascript:;" target="_self">
                    <i class="fa fa-link"></i> <span class="title">Link Tracking</span><span class="arrow"></span>
                </a>
                <ul class="dropdown-menu" style="display: none;">
                    <li>
                        <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'link-checking', 'action' => 'index'));?>" target="_self">
                            <i class="fa fa-list"></i> <span class="title">Danh sách</span><span class="arrow"></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'link-checking', 'action' => 'introduce'));?>" target="blank">
                            <i class="fa fa-key"></i> <span class="title">Hướng dẫn cài đặt</span><span class="arrow"></span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="dropdown-submenu">
                <a href="javascript:;" target="_self">
                    <i class="fa fa-folder-o"></i> <span class="title">Báo cáo</span><span class="arrow"></span>
                </a>
                <ul class="dropdown-menu" style="display: none;">
<!--                    <li>-->
<!--                        <a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'marketing-report', 'action' => 'index'));?><!--" target="_self">-->
<!--                            <i class="fa fa-bar-chart"></i> <span class="title">Báo cáo chi phí ADS</span><span class="arrow"></span>-->
<!--                        </a>-->
<!--                    </li>-->
                    <li>
                        <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'marketing-ads', 'action' => 'index'));?>" target="_self">
                            <i class="fa fa-bar-chart"></i> <span class="title">Báo cáo ADS theo ngày</span><span class="arrow"></span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>

    <!--Kinh doanh-->
	<li class="parent">
	    <a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
			<i class="fa fa-money"></i> <span class="title">Sales</span><span class="arrow"></span>
		</a> 
		<ul class="dropdown-menu"> 
<!--            <li>-->
<!--                <a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'contact', 'action' => 'search'));?><!--" target="_self">-->
<!--                    <i class="fa fa-search"></i> <span class="title">Tìm kiếm nhanh</span><span class="arrow"></span>-->
<!--                </a>-->
<!--            </li>-->
<!--            <li class="divider"></li>-->
<!--            <li>-->
<!--                <a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'target', 'action' => 'index'));?><!--" target="_self">-->
<!--                    <i class="fa fa-file-text-o"></i> <span class="title">Chi tiêu sales</span><span class="arrow"></span>-->
<!--                </a>-->
<!--            </li>-->
<!---->
<!--            <li class="divider"></li>-->
<!--            <li>-->
<!--                <a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'contact-data', 'action' => 'index'));?><!--" target="_self">-->
<!--                    <i class="fa fa-share-alt"></i> <span class="title">Chia data marketing</span><span class="arrow"></span>-->
<!--                </a>-->
<!--            </li>-->
<!--            <li>-->
<!--                <a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'data-config', 'action' => 'index'));?><!--" target="_self">-->
<!--                    <i class="fa fa-user"></i> <span class="title">Cấu hình chia data Marketing</span><span class="arrow"></span>-->
<!--                </a>-->
<!--            </li>-->
<!--            <li class="divider"></li>-->

            <li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contact', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-list"></i> <span class="title">Danh sách liên hệ</span><span class="arrow"></span>
				</a>
			</li>

<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'history', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-history"></i> <span class="title">Lịch sử chăm sóc</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li> -->
<!--			<li class="divider"></li>-->

			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">Danh sách đơn hàng</span><span class="arrow"></span>
				</a>
			</li>

			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract-detail', 'action' => 'products'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">Báo cáo sản phẩm</span><span class="arrow"></span>
				</a>
			</li>

<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'warehouse'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Danh sách hàng có sẵn</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!---->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'warehouse-hidden'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Danh sách hàng đã ẩn</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!---->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'lost'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Đơn hàng không có khách hàng</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
		</ul>
	</li>

	<!--Sản xuất-->
<!--	<li class="parent">-->
<!--	    <a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">-->
<!--			<i class="fa fa-cogs"></i> <span class="title">Sản xuất</span><span class="arrow"></span>-->
<!--		</a>-->
<!--		<ul class="dropdown-menu">-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'production', 'action' => 'new'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Đơn hàng mới</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'production', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Đơn hàng</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--		</ul>-->
<!--	</li>-->
<?php if($is_system){?>
	<li class="parent">
	    <a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
			<i class="fa fa-cogs"></i> <span class="title">Kho Kiotviet</span><span class="arrow"></span>
		</a>
		<ul class="dropdown-menu">
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'kov-branches', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">Kho hàng</span><span class="arrow"></span>
				</a>
			</li>
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'kov-products', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">Sản phẩm</span><span class="arrow"></span>
				</a>
			</li>
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'kov-discounts', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Khuyến mãi</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'product-return', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Kho hàng hoàn</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
		</ul>
	</li>
<?php }?>

	<!--Giục đơn-->
	<li class="parent">
	    <a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
			<i class="fa fa-phone-square"></i> <span class="title">Giục đơn</span><span class="arrow"></span>
		</a>
		<ul class="dropdown-menu">
            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'index-shipping'));?>" target="_self">
                    <i class="fa fa-file-text-o"></i> <span class="title">Danh sách đơn hàng</span><span class="arrow"></span>
                </a>
            </li>
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'check', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Đơn hàng Tỉnh</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!---->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'check', 'action' => 'internal'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Đơn hàng Hà Nội</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
		</ul>
	</li>

	<!--Kế toán -->
	<li class="parent">
	    <a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
			<i class="fa fa-usd"></i> <span class="title">Kế toán</span><span class="arrow"></span>
		</a>
		<ul class="dropdown-menu">
            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'index-new'));?>" target="_self">
                    <i class="fa fa-file-text-o"></i> <span class="title">Danh sách đơn hàng mới</span><span class="arrow"></span>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'index-accounting'));?>" target="_self">
                    <i class="fa fa-file-text-o"></i> <span class="title">Danh sách đơn hàng</span><span class="arrow"></span>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'del'));?>" target="_self">
                    <i class="fa fa-file-text-o"></i> <span class="title">Danh sách đã xóa</span><span class="arrow"></span>
                </a>
            </li>
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'contract-owed', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Công nợ</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'contract-owed', 'action' => 'warehouseCancel'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Đơn hàng Hủy Không lưu kho</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'contract-owed', 'action' => 'warehouseSold'));?><!--" target="_self">-->
<!--					<i class="fa fa-file-text-o"></i> <span class="title">Hàng có sẵn đã bán hết</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--            <li class="divider"></li>-->
		</ul>
	</li>

    <!--Báo cáo-->
	<li>
		<a href="<?php echo $this->url('routeReport/default', array('controller' => 'revenue', 'action' => 'index'));?>" target="_self">
			<i class="fa fa-bar-chart"></i> <span class="title">Báo cáo</span><span class="arrow"></span>
		</a>
	</li>

</ul>