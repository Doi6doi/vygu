<?php

namespace vygu;

/// vygu Top level window [Group]
class Window extends Group {

   const
      /// Kind constant
      WINDOW = "window";

   /// Properties
   const
      /// Title of teh window (string)
      TITLE = "title",
      /// Top menu ([Menu])
      MENU = "menu",
      /// It is a main window (bool), shows up automatically
      /// and exits application if closed
      MAIN = "main";

   /// Handlers
   const
      /// Called when window is about to close
      /// Return true if closing is allowed
      CLOSING = "closing";

   function __construct($args) {
      parent::__construct($args);
      $this->defSize($args);
      if ( ! $this->handler( Group::LAYOUT ) )
         $this->handler( self::LAYOUT, [Layout::class,"def"] );
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

   /// `MENU` property
   function menu($x=Tools::GET) { return $this->prop(self::MENU,$x); }

   /// `TITLE` property
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

   function isProp($p) {
      switch ($p) {
         case self::TITLE: return self::ISLANG;
         default: return parent::isProp($p);
      }
   }


}
