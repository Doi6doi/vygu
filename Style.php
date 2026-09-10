<?php

namespace vygu;

/// Text style
class Style {

   /// Properties
   const
      /// Background color
      BACK = "back",
      /// Bold text
      BOLD = "bold",
      /// Foreground color
      FORE = "fore",
      /// Italic text
      ITALIC = "italic",
      /// Strike-through text
      THROUGH = "through";

   protected $props;
   public $impl;

   /// Create a new Style
   /// \param $args Property values
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
