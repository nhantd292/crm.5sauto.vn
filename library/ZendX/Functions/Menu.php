<?php
namespace ZendX\Functions;

class Menu {

    protected $_menu;
    
    public function __construct($menu) {
        $this->_menu = $menu;
    }
    
    public function getByCode($code, $field = null, $options = null) {
        $result = array();
        
        $left = 0;
        $right = 0;
        foreach ($this->_menu AS $menu) {
            if($left != 0) {
                if($menu['left'] >= $left && $menu['right'] <= $right) {
                    $result[$menu['id']] = $menu;
                } else {
                    break;
                }
            }
            
            if($menu['code'] == $code) {
                $result[$menu['id']] = $menu;
                $left = $menu['left'];
                $right = $menu['right'];
                
                if($right - $left == 1) {
                    break;
                }
            }
        }
        
        if($field != null) {
            $result = current($result);
            $result = $result[$field];
        }
        
        return $result;
    }
    
    public function getByLevel($level, $options = null) {
        $result = array();

        if(!empty($this->_menu)) {
            foreach ($this->_menu AS $menu) {
                if($menu['level'] <= $level) {
                    $result[$menu['id']] = $menu;
                }
            }
        }
    
        return $result;
    }
    
    public function createMenu($code, $options = null) {
        $arrMenu = $this->getByCode($code);
        
        $xhtml = '';
        if(!empty($arrMenu)) {
            $xhtml .= '<ul class="'. $options['class'] .'">';
            $parentRight = array();
            $maxLevel = 1;
            $minLevel = 1;
            $i = 0;
            
            foreach ($arrMenu AS $menu) {
                
                if($i > 0) {
                    $title  = $menu['title'];
                    $name   = $menu['name'];
                    $target = $menu['target'];
                    $icon   = $menu['icon'] ? $menu['icon'] : 'link';
                    $link   = $menu['link'];
                    
                    if($menu['level'] > $maxLevel) {
                        $maxLevel = $menu['level'];
                    }
                    
                    if($menu['right'] - $menu['left'] == 1) {
                        if(strtolower($link) == 'divider') {
                            $xhtml .= sprintf('<li class="divider"></li>');
                        } else {
                            $xhtml .= sprintf('<li><a href="%s" target="%s" title="%s"><i class="fa fa-%s"></i> <span class="title">%s</span><span class="arrow"></span></a></li>', $link, $target, $title, $icon, $name);
                        }
                        
                        for ($j = $minLevel; $j < $maxLevel; $j++) {
                            if(in_array($menu['right'] + $j, $parentRight)) {
                                $xhtml .= '</ul></li>';
                            }
                        }
                    } else {
                        $parentRight[] = $menu['right'];
                        $xhtml .= sprintf('<li class="parent"><a href="%s" target="%s" title="%s" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" class="dropdown-toggle"><i class="fa fa-%s"></i> <span class="title">%s</span><span class="arrow"></span></a><ul class="dropdown-menu">', $link, $target, $title, $icon, $name);
                    }
                }
                $i++;
            }
            $xhtml .= '</ul>';
        }
    
        return $xhtml;
    }
}