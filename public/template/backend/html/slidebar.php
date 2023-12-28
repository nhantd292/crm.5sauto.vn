<?php
    /* $menus = new \ZendX\Functions\Menu($this->arrParams['menu']);
    $arrMenu = array();
    foreach ($menus->getByLevel(1) AS $menu) {
        $arrMenu[] = array('class' => '', 'name' => $menu['name'], 'icon' => 'fa-link', 'link' => $this->url('routeAdminNested/index', array('controller' => 'menu', 'code' => $menu['code'])));
    } */
    $arrMenu[] = array('class' => '', 'name' => 'Tất cả menu', 'icon' => 'fa-link', 'link' => $this->url('routeAdminNested/index', array('controller' => 'menu', 'code' => 'Menu')));
    
    $arrMenus = array(
        array('class' => '', 'name' => 'Truy cập nhanh', 'icon' => 'fa-home', 'link' => 'javascript:;', 
            'children' => array(
                array('class' => '', 'name' => 'Dashboard', 'icon' => 'fa fa-dashboard', 'link' => $this->url('routeAdmin')),
                array('class' => '', 'name' => 'Bàn làm việc', 'icon' => 'fa-calendar', 'link' => 'javascript:;'),
                array('class' => '', 'name' => 'Thống kê', 'icon' => 'fa-bar-chart-o', 'link' => 'javascript:;'),
                array('class' => '', 'name' => 'Xem trang', 'icon' => 'fa-home', 'link' => '/'),
            )
        ),
        array('class' => '', 'name' => 'Nhân sự', 'icon' => 'fa-users', 'link' => 'javascript:;',
            'children' => array(
                array('class' => '', 'name' => 'Thành viên', 'icon' => 'fa-user', 'link' => $this->url('routeAdmin/default', array('controller' => 'user', 'action' => 'index'))),
                array('class' => '', 'name' => 'Nhóm vai trò', 'icon' => 'fa-users', 'link' => $this->url('routeAdmin/default', array('controller' => 'permission', 'action' => 'index'))),
                array('class' => '', 'name' => 'Quyền truy cập', 'icon' => 'fa-universal-access', 'link' => $this->url('routeAdmin/default', array('controller' => 'permission-list', 'action' => 'index'))),
            )
        ),
        array('class' => '', 'name' => 'Menu', 'icon' => 'fa-link', 'link' => 'javascript:;',
            'children' => $arrMenu
        ),
        array('class' => '', 'name' => 'Cấu hình', 'icon' => 'fa-cogs', 'link' => 'javascript:;', 
            'children' => array(
                array('class' => '', 'name' => 'Cấu hình chung', 'icon' => 'fa-cog', 'link' => $this->url('routeAdminNested/index', array('controller' => 'setting', 'code' => 'General'))),
            )
        ),
    );
    
    $xhtmlMenu = '';
    foreach ($arrMenus AS $menu) {
        if(!empty($menu['children'])) {
            $xhtmlMenuChild = '';
            foreach ($menu['children'] AS $menuChild) {
                $xhtmlMenuChild .= sprintf('<li class="%s"> <a href="%s"><i class="fa %s"></i> %s</a> </li>', $menuChild['class'], $menuChild['link'], $menuChild['icon'], $menuChild['name']);
            }
            $xhtmlMenu .= sprintf('<li class="%s"> <a href="%s"> <i class="fa %s"></i> <span class="title">%s</span> <span class="arrow"></span> </a> %s </li>', $menu['class'], $menu['link'], $menu['icon'], $menu['name'], '<ul class="sub-menu">' . $xhtmlMenuChild . '</ul>');
        } else {
            $xhtmlMenu .= sprintf('<li class="%s"> <a href="%s"> <i class="fa %s"></i> <span class="title">%s</span> </a> </li>', $menu['class'], $menu['link'], $menu['icon'], $menu['name']);
        }
    }
?>

<!-- BEGIN SIDEBAR -->
<div class="page-sidebar-wrapper">
	<div class="page-sidebar navbar-collapse collapse">
		<ul class="page-sidebar-menu">
			<li class="sidebar-toggler-wrapper">
				<div class="sidebar-toggler hidden-phone"></div>
			</li>
			<?php echo $xhtmlMenu;?>
		</ul>
	</div>
</div>
<!-- END SIDEBAR -->