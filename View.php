<?php

namespace vygu;

/// megjeleníthető widget
class View {

   /// propertyk
   const
      CURSOR = "cursor",
      STYLE = "style",
      TEXT = "text",
      VISIBLE = "visible";

   /// események
   const
      KEYPRESS = "keyPress";

   /// a konkrét implementáció
   public $impl;
   /// szülő view
   public $parent;
   /// plusz adat
   public $data;
   /// az eseménykezelők
   protected $handlers;

   function __construct($args=null) {
      $this->handlers = [];
      Vygu::ins()->viewCreate( $this );
      $this->initDefArg( $args );
      $this->initArgs( $args );
   }

   function __destruct() {
      Vygu::ins()->viewDestroy($this);
   }

   function kind() { Tools::notImpl($this,__FUNCTION__); }

   /// eseménykezelő
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

   /// szülő view
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

   /// láthatóság
   function visible( $x = Tools::GET ) { return $this->prop(self::VISIBLE,$x); }

   /// kurzor
   function cursor( $x = Tools::GET ) { return $this->prop(self::CURSOR,$x); }

   /// fókusz kérése
   function focus() { 
      return Vygu::ins()->viewFocus($this); 
   }

   /// egy koordináta
   function coord( $c, $x=Tools::GET ) {
      $tmp = true;
      return Vygu::ins()->viewCoord($this,$c,$x,$tmp);
   }
   
   /// több koordináta
   function coords( array $cs, $xs=Tools::GET) {
      return Vygu::ins()->viewCoords($this,$cs,$xs);
   }

   /// egy jellemző
   function prop($p,$x = Tools::GET ) {
      return Vygu::ins()->viewProperty($this,$p,$x);
   }

   /// van-e ilyen jellemző
   function isProp($p) {
      switch ($p) {
         case self::CURSOR:
         case self::TEXT:
         case self::VISIBLE:
            return true;
         default:
            return false;
      }
   }
   
   /// koordináta-e
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
   
   /// handler-e
   function isHandler($h) {
      return false;
   }

   /// a default argumentum
   function defArg() { return null; }

   /// default argumentum init
   function initDefArg( & $args ) {
      if ( ! is_array( $args )) {
         if ( null !== $args
               && $d = $this->defArg())
            $args = [$d=>$args];
            else $args = [];
      }
   }

   /// minden argumentum init
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
