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
      if ( ! $this->handler( Group::LAYOUT ) )
         $this->handler( self::LAYOUT, [Layout::class,"def"] );
      $this->defSize($args);
      if ( Tools::g( $args, self::MAIN )) {
         $this->handler(self::CLOSING, function() { 
             Vygu::ins()->finish(); 
             return true;
         } );
         $this->visible(true);
      }
   }

   function defArg() { return self::TITLE; }

   function kind() { return self::WINDOW; }

   /// menü
   function menu($x=Tools::GET) { return $this->prop(self::MENU,$x); }

   /// ablakcím
   function title($x=Tools::GET) { return $this->prop(self::TITLE,$x); }

   function defSize( $args ) {
      $s = Screen::ins();
      if ( ! Tools::g( $args, Layout::WIDTH ))
         $this->coord( Layout::WIDTH, $s->coord( Layout::CONTWIDTH ) >> 1 );
      if ( ! Tools::g( $args, Layout::HEIGHT ))
         $this->coord( Layout::HEIGHT, $s->coord( Layout::CONTHEIGHT ) >> 1 );
      if ( ! Tools::ga( $args, [Layout::LEFT, Layout::RIGHT, Layout::CENTERX] ))
         $this->coord( Layout::CENTERX, $s->coord( Layout::CONTWIDTH ) >> 1 );
      if ( ! Tools::ga( $args, [Layout::TOP, Layout::BOTTOM, Layout::CENTERY] ))
         $this->coord( Layout::CENTERY, $s->coord( Layout::CONTHEIGHT ) >> 1 );
   }
      


}
