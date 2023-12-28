<ul class="nav navbar-nav">
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
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'index', 'action' => 'backup'));?>" target="_self">
					<i class="fa fa-database"></i> <span class="title">Backup dữ liệu</span><span class="arrow"></span>
				</a>
			</li>
		</ul>
	</li>
	<li class="parent">
		<a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
			<i class="fa fa-users"></i> <span class="title">Nhân sự</span><span class="arrow"></span>
		</a>
		<ul class="dropdown-menu">
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'user', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-user"></i> <span class="title">Nhân viên</span><span class="arrow"></span>
				</a>
			</li>
			<li class="divider"></li>
			<li>
				<a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'sale-branch', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-sitemap"></i> <span class="title">Cơ sở kinh doanh</span><span class="arrow"></span>
				</a>
			</li>
			<li>
				<a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'lists-group', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-sitemap"></i> <span class="title">Đội nhóm</span><span class="arrow"></span>
				</a>
			</li>
			<li class="divider"></li>
			<li>
				<a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'company-branch', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-sitemap"></i> <span class="title">Cơ sở làm việc</span><span class="arrow"></span>
				</a>
			</li>
			<li>
				<a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'company-department', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-sitemap"></i> <span class="title">Phòng ban</span><span class="arrow"></span>
				</a>
			</li>
			<li>
				<a href="<?php echo $this->url('routeAdminDocument/default', array('slug' => 'company-position', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-sitemap"></i> <span class="title">Vị trí/Chức vụ</span><span class="arrow"></span>
				</a>
			</li>
		</ul>
	</li>
	<li class="parent">
	    <a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">
			<i class="fa fa-money"></i> <span class="title">Sale</span><span class="arrow"></span>
		</a> 
		<ul class="dropdown-menu"> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contact', 'action' => 'search'));?>" target="_self">
					<i class="fa fa-search"></i> <span class="title">Tìm kiếm nhanh</span><span class="arrow"></span>
				</a>
			</li> 
			<li class="divider"></li> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contact', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-user"></i> <span class="title">Danh sách data</span><span class="arrow"></span>
				</a>
			</li> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'history', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-history"></i> <span class="title">Lịch sử chăm sóc</span><span class="arrow"></span>
				</a>
			</li> 
			<li class="divider"></li> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">đơn hàng</span><span class="arrow"></span>
				</a>
			</li> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'bill', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-dollar"></i> <span class="title">Hóa đơn</span><span class="arrow"></span>
				</a>
			</li>
			<li class="divider"></li>
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract-me', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">đơn hàng của tôi</span><span class="arrow"></span>
				</a>
			</li> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract-debt-old', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">đơn hàng công nợ cũ</span><span class="arrow"></span>
				</a>
			</li> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'contract-debt-new', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">đơn hàng công nợ mới</span><span class="arrow"></span>
				</a>
			</li>
			<!--  
			<li class="divider"></li>
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'bc', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-file-text-o"></i> <span class="title">Đăng ký Hội Đồng Anh</span><span class="arrow"></span>
				</a>
			</li>
			-->
			<li class="divider"></li> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'sales-target', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-folder-o"></i> <span class="title">Chỉ tiêu kinh doanh</span><span class="arrow"></span>
				</a>
			</li> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'product', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-folder-o"></i> <span class="title">Sản phẩm</span><span class="arrow"></span>
				</a>
			</li> 
		</ul>
	</li> 
<!--	<li class="parent">-->
<!--		<a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle">-->
<!--			<i class="fa fa-slideshare"></i> <span class="title">Đào tạo <span class="badge badge-important notification_pending hidden"></span></span><span class="arrow"></span>-->
<!--		</a> -->
<!--		<ul class="dropdown-menu"> -->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'edu-class', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-folder-o"></i> <span class="title">Lớp học</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<!-- -->
<!--			<li class="divider"></li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'event-demo', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-folder-o"></i> <span class="title">Lớp học demo</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			-->-->
<!--			<li class="divider"></li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'teacher', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-folder-o"></i> <span class="title">Quản lý giáo viên</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'coach', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-folder-o"></i> <span class="title">Quản lý trợ giảng</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<li class="divider"></li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdminDocument/default', array('slug' => 'edu-location', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-folder-o"></i> <span class="title">Địa điểm học</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdminDocument/default', array('slug' => 'edu-room', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-folder-o"></i> <span class="title">Phòng học</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li> -->
<!--			<li class="divider"></li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdmin/default', array('controller' => 'pending', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-bug"></i> <span class="title">Yêu cầu cần xử lý <span class="badge badge-important notification_pending hidden"></span></span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--		</ul>-->
<!--	</li>-->
	<!-- 
	<li class="parent">
		<a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle" aria-expanded="false">
			<i class="fa fa-dot-circle-o"></i> <span class="title">Chiến dịch</span><span class="arrow"></span>
		</a> 
		<ul class="dropdown-menu"> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'campaign', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-dot-circle-o"></i> <span class="title">Quản lý chiến dịch</span><span class="arrow"></span>
				</a>
			</li>
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'campaign-data', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-dot-circle-o"></i> <span class="title">Data chiến dịch</span><span class="arrow"></span>
				</a>
			</li>
		</ul>
	</li> 
	<li class="parent">
		<a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle" aria-expanded="false">
			<i class="fa fa-folder-o"></i> <span class="title">Công việc</span><span class="arrow"></span>
		</a> 
		<ul class="dropdown-menu"> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'task', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-folder-o"></i> <span class="title">Danh sách công việc</span><span class="arrow"></span>
				</a>
			</li>
			<li class="divider"></li>
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'task-project', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-folder-o"></i> <span class="title">Dự án</span><span class="arrow"></span>
				</a>
			</li>
		</ul>
	</li>
    -->
	<li>
		<a href="<?php echo $this->url('routeReport/default', array('controller' => 'revenue', 'action' => 'index'));?>" target="_self">
			<i class="fa fa-bar-chart"></i> <span class="title">Báo cáo</span><span class="arrow"></span>
		</a>
	</li> 
<!--	<li class="parent">-->
<!--		<a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle" aria-expanded="false">-->
<!--			<i class="fa fa-folder-o"></i> <span class="title">Chuyên mục</span><span class="arrow"></span>-->
<!--		</a> -->
<!--		<ul class="dropdown-menu"> -->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdminDocument/default', array('slug' => 'school', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-folder-o"></i> <span class="title">Trường học</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--			<li>-->
<!--				<a href="--><?php //echo $this->url('routeAdminDocument/default', array('slug' => 'major', 'action' => 'index'));?><!--" target="_self">-->
<!--					<i class="fa fa-folder-o"></i> <span class="title">Chuyên ngành học</span><span class="arrow"></span>-->
<!--				</a>-->
<!--			</li>-->
<!--		</ul>-->
<!--	</li> -->
	<li class="parent">
		<a href="javascript:;" target="_self" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle" aria-expanded="false">
			<i class="fa fa-cogs"></i> <span class="title">Cấu hình</span><span class="arrow"></span>
		</a> 
		<ul class="dropdown-menu"> 
			<li>
				<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'dynamic', 'action' => 'index'));?>" target="_self">
					<i class="fa fa-folder-o"></i> <span class="title">Cấu hình chuyên mục</span><span class="arrow"></span>
				</a>
			</li>
			<li>
				<a href="<?php echo $this->url('routeAdminNested/default', array('controller' => 'setting', 'action' => 'index', 'code' => 'System'));?>" target="_self">
					<i class="fa fa-cog"></i> <span class="title">Cấu hình hệ thống</span><span class="arrow"></span>
				</a>
			</li> 
			<li class="divider"></li> 
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
</ul>