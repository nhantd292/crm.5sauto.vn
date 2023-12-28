<?php
namespace ZendX\View\Helper\Url;
use Zend\View\Helper\AbstractHelper;

class LinkAdminSort extends AbstractHelper {
	
	public function __invoke($name, $column, $ssFilter, $options = null) {
        $order = ($ssFilter['order'] == 'ASC') ? 'DESC' : 'ASC';
    
        $class = $options['class'] . ' sorting';
        $width = $options['width'];
        if($ssFilter['order_by'] == $column) {
            $class = $options['class'] . ' sorting sorting_' . strtolower($ssFilter['order']);
        }

        return sprintf('<th class="%s" width="%s"><a href="javascript:;" onclick="javascript:sortList(\'%s\', \'%s\');">%s</a></th>', trim($class),$width, $column, $order, $name);
    }
}