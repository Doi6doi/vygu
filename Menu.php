<?php

namespace vygu;

/// A menu bar, popup menu, or submenu
class Menu {

   public $props;
   public $impl;
   /// Menu items
   public $items;

   /// Create new menu
   /// \param $args property values (default: [Action]::NAME)
   function __construct( $args=[] ) {
      Vygu::ins()->menuCreate( $this );
      if ( null !== $args && ! is_array($args))
         $args = [Action::NAME=>$args];
      $this->props = $args;
   }

   /// Add a menu item
   /// \param $i [Menu] or [Action] to add
   function add( $i ) {
      Vygu::ins()->menuAdd( $this, $i );
      $this->items [] = $i;
      return $i;
   }

}
