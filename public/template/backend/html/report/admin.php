<div class="page-sidebar navbar-collapse collapse">
	<ul class="page-sidebar-menu">
		<li class="sidebar-toggler-wrapper">
			<div class="sidebar-toggler hidden-phone"></div>
		</li>
		<li class="start">
			<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index'));?>">
				<i class="fa fa-home"></i>
				<span class="title">Tổng quan</span><span class="selected"></span>
			</a>
		</li>
		<li class="">
			<a href="javascript:;">
				<i class="fa fa-bar-chart"></i>
				<span class="title">Doanh thu</span><span class="arrow"></span>
			</a>
			<ul class="sub-menu">
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'revenue-branch'));?>"><i class="fa fa-dot-circle-o"></i> Cơ sở</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'group'));?>"><i class="fa fa-dot-circle-o"></i> Đội nhóm</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'revenue-user'));?>"><i class="fa fa-dot-circle-o"></i> Nhân viên</a>
				</li>
			</ul>
		</li>
		<li class="">
			<a href="javascript:;">
				<i class="fa fa-bar-chart"></i>
				<span class="title">Thống kê đơn hàng</span><span class="arrow"></span>
			</a>
			<ul class="sub-menu">
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contract-sex'));?>"><i class="fa fa-dot-circle-o"></i> Giới tính</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contract-school'));?>"><i class="fa fa-dot-circle-o"></i> Trường học</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contract-location'));?>"><i class="fa fa-dot-circle-o"></i> Vị trí địa lý</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contract-source-channel'));?>"><i class="fa fa-dot-circle-o"></i> Kênh nguồn</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contract-age'));?>"><i class="fa fa-dot-circle-o"></i> Độ tuổi</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contract-subject'));?>"><i class="fa fa-dot-circle-o"></i> Đối tượng</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contract-class'));?>"><i class="fa fa-dot-circle-o"></i> Khóa học</a>
				</li>
			</ul>
		</li>
		<li class="">
			<a href="javascript:;">
				<i class="fa fa-bar-chart"></i>
				<span class="title">Thống kê liên hệ</span><span class="arrow"></span>
			</a>
			<ul class="sub-menu">
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contact-sex'));?>"><i class="fa fa-dot-circle-o"></i> Giới tính</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contact-school'));?>"><i class="fa fa-dot-circle-o"></i> Trường học</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contact-location'));?>"><i class="fa fa-dot-circle-o"></i> Vị trí địa lý</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contact-source-channel'));?>"><i class="fa fa-dot-circle-o"></i> Kênh nguồn</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contact-age'));?>"><i class="fa fa-dot-circle-o"></i> Độ tuổi</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'contact-subject'));?>"><i class="fa fa-dot-circle-o"></i> Đối tượng</a>
				</li>
			</ul>
		</li>
		<li class="">
			<a href="javascript:;">
				<i class="fa fa-bar-chart"></i>
				<span class="title">Thống kê sự kiện</span><span class="arrow"></span>
			</a>
			<ul class="sub-menu">
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'demo'));?>"><i class="fa fa-dot-circle-o"></i> Demo</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'test'));?>"><i class="fa fa-dot-circle-o"></i> Test</a>
				</li>
			</ul>
		</li>
		<li class="">
			<a href="javascript:;">
				<i class="fa fa-bar-chart"></i>
				<span class="title">Thống kê theo tháng</span><span class="arrow"></span>
			</a>
			<ul class="sub-menu">
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'class-open'));?>"><i class="fa fa-dot-circle-o"></i> Lớp học theo tháng</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'matter'));?>"><i class="fa fa-dot-circle-o"></i> Vật phẩm theo tháng</a>
				</li>
			</ul>
		</li>
		<li class="">
			<a href="javascript:;">
				<i class="fa fa-bar-chart"></i>
				<span class="title">Thống kê Marketing</span><span class="arrow"></span>
			</a>
			<ul class="sub-menu">
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'video-of-week'));?>"><i class="fa fa-dot-circle-o"></i> Video trong ngày</a>
				</li>
				<li>
					<a href="<?php echo $this->url('routeAdmin/default', array('controller' => 'report', 'action' => 'index', 'id' => 'video-top-view'));?>"><i class="fa fa-dot-circle-o"></i> Top 20 video xem nhiều</a>
				</li>
			</ul>
		</li>
	</ul>
</div>