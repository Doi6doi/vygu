<?php

namespace vygu;

/// A pressable button [Group]
class Button extends Group {

   const
      /// Kind constant
      BUTTON = "button";

   function defArg() { return self::TEXT; }

   function kind() { return self::BUTTON; }

   /// Text property
   function text($x=Tools::GET) { return $this->prop(self::TEXT,$x); }

   function isProp($p) {
      switch ($p) {
         case self::STYLE:
            return true;
         case self::TEXT:
            return self::ISLANG;
         default:
            return parent::isProp($p);
      }
   }

}
