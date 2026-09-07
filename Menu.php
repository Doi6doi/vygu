<?php

namespace vygu;

class Menu {

   public $props;
   public $impl;
   public $items;

   function __construct( $args=[] ) {
      Vygu::ins()->menuCreate( $this );
      if ( null !== $args && ! is_array($args))
         $args = [Action::NAME=>$args];
      $this->props = $args;
   }

   /// view hozzáadása
   function add( $i ) {
      Vygu::ins()->menuAdd( $this, $i );
      $this->items [] = $i;
      return $i;
   }

}
