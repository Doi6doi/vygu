<?php

namespace vygu;

/// A [View] which can contain other Views
class Group extends View {

   const
      /// Kind constant
      GROUP = "group";

   /// Handlers
   const
      /// Layout handler when group size is changed
      LAYOUT = "layout";

   /// Contained views
   public $items;

   function kind() { return self::GROUP; }

   /// Creates a new Group
   /// \param $args Property values
   function __construct($args=null) {
      parent::__construct($args);
      $this->items = [];
   }

   /// Number of contained Views
   function count() {
      return count($this->items);
   }

   /// Add a [View] to the Group
   function add( View $v ) {
      $v->parent($this);
      $this->handle( self::LAYOUT );
      return $v;
   }

   /// Remove the `$at`th View from the Group
   function drop( $at ) {
      if ( $at < 0 || $this->count() <= $at )
         return;
      $this->items[$at]->parent(null);
      $this->handle( self::LAYOUT );
   }

   /// Remove all Views from the Group
   function clear() {
      while ($n = $this->count() )
         $this->drop( $n-1 );
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


}
