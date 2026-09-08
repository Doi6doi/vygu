<?php

namespace vygu;

/// más view-kat tartalmazó view
class Group extends View {

   const
      GROUP = "group";

   public $items;

   function kind() { return self::GROUP; }

   function __construct($args=null) {
      parent::__construct($args);
      $this->items = [];
   }


   function __destruct() {
      $this->clear();
      parent::__destruct();
   }

   /// tartalamzott view-k száma
   function count() {
      return count($this->items);
   }

   /// view hozzáadása
   function add( View $v ) {
      $v->parent($this);
      return $v;
   }

   /// view kivétele
   function drop( $at ) {
      if ( $at < 0 || $this->count() <= $at )
         return;
      $this->items[$at]->parent(null);
   }

   /// minden elem kivétele
   function clear() {
      while ($n = $this->count() )
         $this->drop( $n-1 );
   }

}
