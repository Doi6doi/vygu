<?php

namespace vygu;

/// Window level action
/// and action constants
class Action extends Elem {

   const
      /// Kind constant
      ACTION = "action";
      
   /// Properties
   const
      /// Keyboard shortcut ([Key])
      SHORTCUT = "shortcut";   

   function kind() { return self::ACTION; }

   function defArg() { return self::NAME; }

   /// `NAME` property
   function name( $x = Tools::GET ) { return $this->prop( self::NAME, $x); }

   /// `SHORTCUT` property ([Key])
   function shortcut( $x = Tools::GET ) { return $this->prop( self::SHORTCUT, $x ); }

   /// fire the action
   function fire() {
      $this->handle( self::FIRE );
   }

   function isProp($p) {
      switch ($p) {
         case self::NAME:
            return self::ISLANG;
         case self::SHORTCUT:
            return true;
         default:
            return parent::isProp($p);
      }
   }

}
