<?php

namespace vygu;

/// A widget, that can appear in the GUI
class View extends Elem {

   /// Properties
   const
      /// Alignment ([Layout]:: `LEFT`, `RIGHT` or `CENTERX`)
      ALIGN = "align",
      /// Cursor ([Cursor]:: `DEFAULT`, `WAIT`)
      CURSOR = "cursor",
      /// Position
      POSITION = "position",
      /// Selection length
      SELLENGTH = "selLength",
      /// [Style] describing colors and font properties
      STYLE = "style",
      /// Text of a View (string)
      TEXT = "text",
      /// Is View visible (bool)
      VISIBLE = "visible";

   /// Handlers
   const
      /// Key press/release handler (`h(Key $k)`)
      KEY = "key";

   /// Parent [Group]
   public $parent;

   /// Signals the engine to redraw the View
   function invalidate() {
      Vygu::ins()->viewInvalidate($this);
   }

   /// Get or set parent [Group]
   function parent( $x = Tools::GET ) {
      $old = $this->parent;
      if (Tools::GET === $x) return $old;
      Vygu::ins()->viewParent( $this, $x );
      if ($old)
         Tools::arrayRemove( $old->items, $this );
      $this->parent = $x;
      if ($x)
         $x->items[] = $this;
   }

   /// `View::VISIBLE` property
   function visible( $x = Tools::GET ) { return $this->prop(self::VISIBLE,$x); }

   /// `View::Cursor` property
   function cursor( $x = Tools::GET ) { return $this->prop(self::CURSOR,$x); }

   /// Focuses the View
   function focus() {
      return Vygu::ins()->viewFocus($this);
   }

   /// Gets or sets a coordinate
   /// \param $c The [Layout] coordinate
   /// \param $x The new value, or `Tools::GET` for query
   /// \return The current value if queried
   function coord( $c, $x=Tools::GET ) {
      $tmp = true;
      return Vygu::ins()->viewCoord($this,$c,$x,$tmp);
   }

   /// Gets or sets more coordinates
   /// \param $cs The [Layout] coordinates
   /// \param $xs The values, or `Tools::GET` for query
   /// \return An array containing the values
   function coords( array $cs, $xs=Tools::GET) {
      return Vygu::ins()->viewCoords($this,$cs,$xs);
   }

   /// Does `$c` coordinate exist for this View
   function isCoord($c) {
      switch ($c) {
         case Layout::BOTTOM:
         case Layout::CENTERX:
         case Layout::CENTERY:
         case Layout::HEIGHT:
         case Layout::LEFT:
         case Layout::RIGHT:
         case Layout::TOP:
         case Layout::WIDTH:
            return true;
         default:
            return false;
      }
   }

   function isProp($p) {
      return false;
      switch ($p) {
         case self::CURSOR:
         case self::VISIBLE:
            return true;
         default:
            return false;
      }
   }

   function initArgs( array $args ) {
      parent::initArgs( $args );
      if ( ! $args ) return;
      foreach ($args as $k=>$v) {
         if ($this->isCoord($k))
            $this->coord($k,$v);
      }
   }

}
