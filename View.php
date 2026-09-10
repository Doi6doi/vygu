<?php

namespace vygu;

/// A widget, that can appear in the GUI
class View {

   /// Properties
   const
      /// Alignment ([Layout]:: `LEFT`, `RIGHT` or `CENTERX`)
      ALIGN = "align",
      /// Cursor ([Cursor]:: `DEFAULT`, `WAIT`)
      CURSOR = "cursor",
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

   // a konkrét implementáció
   public $impl;
   /// Parent [Group]
   public $parent;
   // extra információ
   public $data;
   // az eseménykezelők
   protected $handlers;

   /// Creates a new View.
   /// \param $args Property values
   function __construct($args=null) {
      $this->handlers = [];
      Vygu::ins()->viewCreate( $this );
      $this->initDefArg( $args );
      $this->initArgs( $args );
   }

   function __destruct() {
      $this->handlers = [];
      Vygu::ins()->viewDestroy($this);
   }

   /// Returns the kind constant (different for each View)
   function kind() { Tools::notImpl($this,__FUNCTION__); }

   /// Signals the engine to redraw the View
   function invalidate() {
      Vygu::ins()->viewInvalidate($this);
   }

   /// Sets an event handler
   /// \param $e Event kind (`KEY`, etc...)
   /// \param $x Callback (callable)
   /// \return `$this`
   function handler($e,$x=null) {
      $o = Tools::g($this->handlers,$e);
      if (null === $x) {
         return $o;
      } else {
         $v = Vygu::ins();
         $xh = $v->handlerCreate($this, $e, $x);
         $v->viewHandler( $this, $e, $o, $xh );
         $this->handlers[$e] = $xh;
         return $this;
      }
   }

   /// Simulate an event
   /// \param $e Event kind (`KEY`, etc...)
   /// \param $args Event arguments
   /// \return Return value of handler or null
   function handle($e,array $args=[]) {
      if ( ! $h = $this->handler($e))
         return null;
      Vygu::ins()->handlerCall( $h, $args );
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

   /// Gets or sets a property
   /// \param $p The property name
   /// \param $x The new value, or `Tools::GET` for query
   /// \return The current value if queried
   function prop($p,$x = Tools::GET ) {
      return Vygu::ins()->viewProperty($this,$p,$x);
   }

   /// Does `$p` property exists for this View
   function isProp($p) {
      switch ($p) {
         case self::CURSOR:
         case self::VISIBLE:
            return true;
         default:
            return false;
      }
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

   /// Does `$h` handler exist for this View
   function isHandler($h) {
      return false;
   }

   /// The default argument set
   /// if `$args` in `__create()` is not an array
   function defArg() { return null; }

   // default argumentum init
   function initDefArg( & $args ) {
      if ( ! is_array( $args )) {
         if ( null !== $args
               && $d = $this->defArg())
            $args = [$d=>$args];
            else $args = [];
      }
   }

   // minden argumentum init
   function initArgs( array $args ) {
      if ( ! $args ) return;
      foreach ($args as $k=>$v) {
         if ($this->isProp($k))
            $this->prop($k,$v);
         else if ($this->isCoord($k))
            $this->coord($k,$v);
         else if ($this->isHandler($k))
            $this->handler($k,$v);
      }
   }

}
