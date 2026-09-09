<?php

namespace vygu;

/// más view-kat tartalmazó view
class Group extends View {

   const
      GROUP = "group";
      
   const
      LAYOUT = "layout";

   public $items;

   function kind() { return self::GROUP; }

   function __construct($args=null) {
      parent::__construct($args);
      $this->items = [];
   }


   /// tartalamzott view-k száma
   function count() {
      return count($this->items);
   }

   /// view hozzáadása
   
   function add( View $v ) {
      $v->parent($this);
      $this->handle( self::LAYOUT );
      return $v;
   }

   /// view kivétele
   function drop( $at ) {
      if ( $at < 0 || $this->count() <= $at )
         return;
      $this->items[$at]->parent(null);
      $this->handle( self::LAYOUT );
   }

   function isHandler($h) {
      switch ($h) {
         case self::LAYOUT: return true;
         default: return parent::isHandler($h);
      }
   }

   function handle($e,array $args=[]) {
      switch ($e) {
         case self::LAYOUT: $args=[$this]; break;
      }
      return parent::handle($e,$args);
   }

   /// minden elem kivétele
   function clear() {
      while ($n = $this->count() )
         $this->drop( $n-1 );
   }

}
