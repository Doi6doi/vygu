<?php

namespace vygu;

/// Base class for Views, Actions, Menus, etc..
class Elem {

   const
      /// Kind constant
      ELEM = "elem",
      /// Property is localized
      ISLANG = "!lang";

   /// Actions
   const
      /// default action (like button press)
      FIRE = "fire";

   /// Properties
   const
      /// identifier of Elem
      ID = "id",
      /// name of Elem
      NAME = "name";

   // a konkrét implementáció
   public $impl;
   // extra információ
   public $data;
   // az eseménykezelők
   protected $handlers;

   function __construct($args=null) {
      $this->handlers = [];
      Vygu::ins()->elemCreate($this);
      $this->initDefArg( $args );
      $this->initArgs( $args );
   }

   function __destruct() {
      $this->handlers = [];
      Vygu::ins()->elemDestroy($this);
   }
   
   /// Returns the kind constant (different for each Elem)
   function kind() { Tools::notImpl($this,__FUNCTION__); }

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
         $v->elemHandler( $this, $e, $o, $xh );
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

   /// Gets or sets a property
   /// \param $p The property name
   /// \param $x The new value, or `Tools::GET` for query
   /// \return The current value if queried,otherwise `$this`
   function prop($p,$x = Tools::GET ) {
      if ( null !== $x && Tools::GET != $x 
         && self::ISLANG == $this->isProp($p)
      )
         $x = Lang::s($x);
      return Vygu::ins()->elemProperty($this,$p,$x);
   }

   /// Does `$p` property exists for this Elem
   function isProp($p) {
      return false;
   }

   /// Does `$h` handler exist for this Elem
   function isHandler($h) {
      return false;
   }
   
   /// The default argument set
   /// if `$args` in `__create()` is not an array
   function defArg() { return null; }

   // default argumentum init
   function initDefArg( & $args ) {
      if ( ! is_array( $args )) {
         if ( null === $args ) {
            $args = [];
         } else {
            if ( ! $d = $this->defArg() )
               throw new EVygu("No default argument for ".$this->kind());
            $args = [$d=>$args];
         }
      }
   }

   // minden argumentum init
   function initArgs( array $args ) {
      if ( ! $args ) return;
      foreach ($args as $k=>$v) {
         if ($this->isProp($k))
            $this->prop($k,$v);
         else if ($this->isHandler($k))
            $this->handler($k,$v);
      }
   }



}
