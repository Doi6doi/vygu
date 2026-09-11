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

   function defArg() { return Elem::NAME; }

   function shortcut( $x = Tools::GET ) { return $this->prop( self::SHORTCUT, $x ); }

}
