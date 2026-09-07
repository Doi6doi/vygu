<?php

namespace vygu;

/// vygu ablak
class Window extends Group {

   const
      WINDOW = "window";

   const
      /// ablak fejléc
      TITLE = "title",
      /// felső menü
      MENU = "menu",
      /// főablak, rögötn látszik, és bezáráskor kilép
      MAIN = "main";

   const
      /// bezárás engedélyezés
      CLOSING = "closing";

   function __construct($args) {
      parent::__construct($args);
      if ( null !== $args && ! is_array($args))
         $args = [ self::TITLE=>$args ];
      if ( $t = Tools::g( $args, self::TITLE ))
         $this->title($t);
      if ( Tools::g( $args, self::MAIN )) {
         $this->handler( self::CLOSING, function() { Vygu::$over = true; } );
         $this->visible(true);
      }
   }

   function kind() { return self::WINDOW; }

   /// menü
   function menu($x=Tools::GET) { return $this->prop(self::MENU,$x); }

   /// ablakcím
   function title($x=Tools::GET) { return $this->prop(self::TITLE,$x); }


}
