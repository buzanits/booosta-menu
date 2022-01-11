<?php
namespace booosta\menu;

\booosta\Framework::add_module_trait('webapp', 'menu\webapp');

trait webapp
{
  public $menu = [];
  public $menutemplatefile_admin = 'tpl/mainmenu_admin.tpl.php';
  public $menutemplatefile_user = 'tpl/mainmenu_user.tpl.php';
  protected $openmenu;
  public $activemenuitem;

  protected function init_menu($menudefinitionfile, $menutemplatefile, $index = 0)
  {
    #\booosta\Framework::debug("in init_menu: $menudefinitionfile, $menutemplatefile");
    if(is_readable($menudefinitionfile) && is_readable($menutemplatefile))
      $this->menu[$index] = $this->makeInstance('Menu', $menudefinitionfile, $menutemplatefile);
  }

  protected function get_menu_html($index = 0) 
  { 
    if(!is_object($this->menu[$index])) return '';
    return $this->menu[$index]->get_html();
  }

  protected function autorun_menu()
  {
    #\booosta\Framework::debug("in autorun_menu");
    $template_module = $this->config('template_module');

    if($this->user_class == 'adminuser') $postfix = '_admin'; else $postfix = '_' . $this->user_class;

    if($df = $this->config("menutemplatefile$postfix")) $tplfile = $df;
    elseif(($df = $this->{"menutemplatefile$postfix"}) && is_readable($df)) $tplfile = $df;
    elseif(($df = $this->config('menutemplatefile')) && is_readable($df)) $tplfile = $df;
    elseif(is_readable("tpl/menutemplatefile$postfix.php")) $tplfile = "tpl/menutemplatefile$postfix.php";
    elseif(is_readable('tpl/menutemplatefile.php')) $tplfile = 'tpl/menutemplatefile.php';
    elseif($template_module && is_readable("vendor/booosta/$template_module/src/menutemplatefile$postfix.php")) 
      $tplfile = "vendor/booosta/$template_module/src/menutemplatefile$postfix.php";
    elseif($template_module && is_readable("vendor/booosta/$template_module/src/menutemplatefile.php")) 
      $tplfile = "vendor/booosta/$template_module/src/menutemplatefile.php";
    elseif(is_readable("vendor/booosta/menu/menutemplatefile$postfix.php")) $tplfile = "vendor/booosta/menu/menutemplatefile$postfix.php";
    elseif(is_readable("vendor/booosta/menu/menutemplatefile.php")) $tplfile = "vendor/booosta/menu/menutemplatefile.php";
    #else \booosta\Framework::debug("ERROR: no menudetemplatefile: ($this->base_dir) $postfix - " . getcwd());
    #\booosta\Framework::debug("{$this->base_dir}vendor/booosta/$template_module/src/menutemplatefile$postfix.php");
    #\booosta\debug("tplfile: $tplfile");

    if($df = $this->config("menudefinitionfile$postfix")) $this->init_menu($df, $tplfile);
    elseif($df = $this->{"menudefinitionfile$postfix"}) $this->init_menu($df, $tplfile);
    elseif($df = $this->config('menudefinitionfile')) $this->init_menu($df, $tplfile);
    elseif(is_readable("incl/menudefinitionfile$postfix.php")) $this->init_menu("incl/menudefinitionfile$postfix.php", $tplfile);
    elseif(is_readable('incl/menudefinitionfile.php')) $this->init_menu('incl/menudefinitionfile.php', $tplfile);
    elseif(is_readable("vendor/booosta/menu/menudefinitionfile$postfix.php")) $this->init_menu("vendor/booosta/menu/menudefinitionfile$postfix.php", $tplfile);
    elseif(is_readable('vendor/booosta/menu/menudefinitionfile.php')) $this->init_menu('vendor/booosta/menu/menudefinitionfile.php', $tplfile);
    #else \booosta\Framework::debug("ERROR: no menudefinitionfile");
  }

  protected function webappinit_menu()
  {
    if($menu = $this->VAR['openmenu']) $_SESSION['open_menu'] = [$menu];
    elseif($menu = $this->openmenu) $_SESSION['open_menu'] = [$menu];


  }

  protected function preparse_menu()
  {
    foreach($this->menu as $index=>$menu):
      if(is_object($menu)):
        if(is_array($this->moduleinfo['menu']['add_items']))
          foreach($this->moduleinfo['menu']['add_items'] as $path=>$items)
            foreach($items as $item)
              $menu->add_submenu($item, $path);

        $tag = 'BOOSTAMENU';
        if($index !== 0) $tag = strtoupper("MENU$index");
        #\booosta\debug("index: $index");
        $this->extra_templates[$tag] = $this->get_menu_html($index);

        if($this->extra_templates[$tag] == '') $this->extra_templates[$tag] = ' ';  // avoid shoing MENUXXX when no menu present
      endif;
    endforeach;
  }

  protected function add_submenu($submenu, $path = null, $index = 0)
  {
    if(is_object($this->menu[$index])) $this->menu[$index]->add_submenu($submenu, $path);
  }

  protected function disable_submenu($path, $index = 0)
  {
    if(is_object($this->menu[$index])) $this->menu[$index]->enable_submenu(false, $path);
  }

  protected function set_menu_anchor($name, $menu_class = '.sidebar')
  {
    $anchor = md5($name);
    $_SESSION['modules']['menu']['anchor'] = $anchor;
    $this->add_jquery_ready("$('$menu_class').scrollTop($('#$anchor').offset().top);");
  }
}
