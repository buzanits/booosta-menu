<?php
$menu_tpl = ['menu_prefix'   => '<ul class="nav navbar-nav navbar-right">',
             'menu_postfix' => '</ul>',
             'submenu_prefix' => '<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{caption}<span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">',
             'submenu_prefix_open' => '<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">{caption}<span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">',
             'submenu_postfix' => '</ul></li>',
             'menulink' => '<li>{LINK|{caption}|{link}}</li>',
             'menucaption' => '<li><a>{caption}</a></li>',
            ];