<?php
namespace booosta\menu;
\booosta\Framework::init_module('menu');

class Menu extends \booosta\base\Module
{
  use moduletrait_menu;

  public $menu, $menu_tpl, $menuicons, $always_open;

  public function __construct($menudefinition_file = null, $menutemplate_file = null)
  {
    #print "$menudefinition_file, $menutemplate_file\n";
    parent::__construct();

    if(is_readable($menudefinition_file)) include $menudefinition_file;
    elseif(is_readable($this->config('menudefinition_file'))) include $this->config('menudefinition_file');
    elseif(is_readable('incl/menu.php')) include 'incl/menu.php';
    elseif(is_readable('../incl/menu.php')) include '../incl/menu.php';

    if(is_readable($menutemplate_file)) include $menutemplate_file;
    elseif(is_readable($this->config('menutemplate_file'))) include $this->config('menutemplate_file');
    elseif(is_readable('incl/menu_template.php')) include 'incl/menu_template.php';
    elseif(is_readable('../incl/menu_template.php')) include '../incl/menu_template.php';

    if(is_array($menu)) $this->menu = $this->build_menu($menu);
    if(is_array($menu_tpl)) $this->menu_tpl = $menu_tpl; else $this->menu_tpl = [];
    if(is_array($menuicons)) $this->menuicons = $menuicons;
    
    if(is_array($menu_always_open)) $this->always_open = $menu_always_open;
  }

  protected function build_menu($menu, $mcaption = '')
  {
    if(!is_array($menu)) return new Menuitem($mcaption, $menu);  // $menu is the link in this case

    $menuitems = [];

    foreach($menu as $caption=>$item):
      if(is_array($item)):
        $menuitems[$caption] = $this->build_menu($item, $caption);
      else:
        $menuitems[$caption] = new Menuitem($caption, $item);
      endif;
    endforeach;

    $obj = new Menuitem($mcaption);
    $obj->set_submenus($menuitems);

    return $obj;
  }

  public function add_submenu($submenu, $path = null)
  {
    if(!is_array($submenu)) return false;

    $menu = $this->build_menu(current($submenu), key($submenu));
    $this->menu->add($menu, $path);
  }

  public function enable_submenu($flag, $path = null)
  {
    $this->menu->enable($flag, $path);
  }

  public function get_html()
  {
    if(!is_object($this->menu)) return '';
    
    #if($_SESSION['open_menu'] == $submenu->caption && isset($this->menu_tpl['submenu_prefix_open'])) $submenu_prefix = $this->menu_tpl['submenu_prefix_open'];
    #elseif(isset($this->menu_tpl['submenu_prefix'])) $submenu_prefix = $this->menu_tpl['submenu_prefix'];

    #if($_SESSION['open_menu'] == $submenu->caption && isset($this->menu_tpl['submenu_postfix_open'])) $submenu_postfix = $this->menu_tpl['submenu_postfix_open'];
    #elseif(isset($this->menu_tpl['submenu_postfix'])) $submenu_postfix = $this->menu_tpl['submenu_postfix'];

    if(isset($this->menu_tpl['menu_prefix'])) $menu_prefix = $this->menu_tpl['menu_prefix'];
    if(isset($this->menu_tpl['menu_postfix'])) $menu_postfix = $this->menu_tpl['menu_postfix'];

    if($this->menuicons[$this->menu->caption] == "" ) $this->menuicons[$this->menu->caption] = $this->config('menu_default_icon');
    if($this->menuicons[$this->menu->caption] == "no-icon") $this->menuicons[$this->menu->caption] = '';

    $html = $menu_prefix;
    foreach($this->menu->submenus as $submenu) $html .= $this->get_submenu_html($submenu);
    $html .= $menu_postfix;

    return $html;
  }

  protected function get_submenu_html($submenu)
  {
    if($submenu->disabled) return '';
    #\booosta\debug("get_submenu_html"); \booosta\debug($submenu);

    $open_menu = $_SESSION['open_menu'];
    if(is_string($open_menu) && $open_menu != '') $open_menu = [$open_menu];
    elseif(!is_array($open_menu)) $open_menu = [];

    if(is_object($this->topobj)) $active_menu_item = $this->topobj->activemenuitem;

    if(is_array($this->always_open)) $open_menu = array_merge($open_menu, $this->always_open);
    #\booosta\debug('open_menu'); \booosta\debug($open_menu);

    if(in_array($submenu->caption, $open_menu) && isset($this->menu_tpl['submenu_prefix_open'])) $submenu_prefix = $this->menu_tpl['submenu_prefix_open'];
    elseif(isset($this->menu_tpl['submenu_prefix'])) $submenu_prefix = $this->menu_tpl['submenu_prefix'];

    if(in_array($submenu->caption, $open_menu) && isset($this->menu_tpl['submenu_postfix_open'])) $submenu_postfix = $this->menu_tpl['submenu_postfix_open'];
    elseif(isset($this->menu_tpl['submenu_postfix'])) $submenu_postfix = $this->menu_tpl['submenu_postfix'];
    
    if($active_menu_item == $submenu->caption) $menulink_key = 'menulink_open'; else $menulink_key = 'menulink';
    if(isset($this->menu_tpl[$menulink_key])) $menulink = $this->menu_tpl[$menulink_key];

    if(isset($this->menu_tpl['menucaption'])) $menucaption = $this->menu_tpl['menucaption'];
 
    #\booosta\debug("caption: " . $submenu->caption);
    if($this->menuicons[$submenu->caption] == "" ) $this->menuicons[$submenu->caption] = $this->config('menu_default_icon');
    if($this->menuicons[$submenu->caption] == "no-icon") $this->menuicons[$submenu->caption] = '';

    if(sizeof($submenu->submenus) == 0):
      if($submenu->link === null):
        $tmp1 = $menucaption;
        $tmp1 = str_replace('{id}', md5($submenu->caption), $tmp1);
        $tmp1 = str_replace('{icon}', $this->menuicons[$submenu->caption], $tmp1);
        $tmp1 = str_replace('{extra}', '', $tmp1);
        $html = str_replace('{caption}', $this->t($submenu->caption), $tmp1);
      else: 
        $tmp1 = $menulink;
        $tmp1 = str_replace('{id}', md5($submenu->caption), $tmp1);
        $tmp1 = str_replace('{icon}', $this->menuicons[$submenu->caption], $tmp1);

        if(substr($submenu->link, 0, 4) == 'http' && $this->config('extlink_newwindow')) $extra = '|target::_new'; else $extra = '';
        $tmp1 = str_replace('{extra}', $extra, $tmp1);
        $tmp1 = str_replace('{caption}', $this->t($submenu->caption), $tmp1);
        $html = str_replace('{link}', $submenu->link, $tmp1);
      endif;
    else:
      $tmp1 = $submenu_prefix;
      $tmp1 = str_replace('{id}', md5($submenu->caption), $tmp1);
      $tmp1 = str_replace('{icon}', $this->menuicons[$submenu->caption], $tmp1);
      $tmp1 = str_replace('{extra}', '', $tmp1);
      $html = str_replace('{caption}', $this->t($submenu->caption), $tmp1);
      foreach($submenu->submenus as $subsubmenu) $html .= $this->get_submenu_html($subsubmenu);
      $html .= $submenu_postfix;
    endif;

    #\booosta\debug("HTML of $submenu->caption"); \booosta\debug($html);
    return $html;
  }
}


class Menuitem
{
  public $caption;
  public $link;
  public $disabled = false;
  public $submenus = [];

  public function __construct($caption, $link = null)
  {
    $this->link = $link;
    $this->caption = $caption;
  }

  public function set_submenus($items) { $this->submenus = $items; }

  public function add($items, $path = null)
  {
    if($path === null):
      $this->submenus[$items->caption] = $items;
    else:
      $subitem = &$this->get_submenu_ref($path);
      if(is_object($subitem)) $subitem->submenus[$items->caption] = $items;
    endif;
  }

  public function enable($flag, $path)
  {
    if($path === null):
      $this->disabled = !$flag;
    else:
      $subitem = &$this->get_submenu_ref($path);
      if(is_object($subitem)) $subitem->disabled = !$flag;
    endif;
  }

  protected function &get_submenu_ref($path)
  {
    if(is_string($path)) $path = explode(',', $path);

    $current = &$this;
    foreach($path as $element):
      if(isset($current->submenus[$element]) && is_object($current->submenus[$element])) $current = &$current->submenus[$element];
      else return false;
    endforeach;
      
    #print_r($current);
    if(is_object($current)) $result = &$current; else $result = null;
    return $result;
  }
}
