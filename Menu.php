<?php

namespace vygu;

/// A menu bar, popup menu, or submenu
class Menu extends Elem {

   const
      MENU = "menu";

   /// Menu items
   public $items;

   function kind() { return self::MENU; }

   function defArg() { return self::NAME; }

   /// `NAME` property
   function name( $x = Tools::GET ) { return $this->prop( self::NAME, $x ); }

   /// Add a menu item
   /// \param $i [Menu] or [Action] to add
   function add( $i ) {
      Vygu::ins()->menuAdd( $this, $i );
      $this->items [] = $i;
      return $i;
   }

}
