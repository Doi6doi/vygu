<?php

namespace vygu;

/// megjeleníthető widget
class View {

   /// propertyk
   const
      CURSOR = "cursor",
      LAYOUT = "layout",
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

}
