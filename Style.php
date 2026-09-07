<?php

namespace vygu;

/// szövegstílus Rich-hez
class Style {

   const
      /// háttésszín
      BACK = "back",
      /// vastag betű
      BOLD = "bold",
      /// előtéár szín
      FORE = "fore",
      /// dőlt betűs
      ITALIC = "italic",
      /// áthúzott
      THROUGH = "through";

   protected $props;
   public $impl;

   function __construct($args=[]) {
      foreach ($args as $k=>$v)
         $this->prop($k,$v);
      Vygu::ins()->styleCreate($this);
   }

   function __destruct() {
      Vygu::ins()->styleDestroy($this);
   }

   function prop( $prop, $val = Tools::GET ) {
      if ( Tools::GET == $val )
         return Tools::g( $this->props, $prop );
         else $this->props[$prop] = $val;
   }

}
