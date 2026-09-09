<?php

namespace vygu;

/// gomb
class Button extends Group {

   const
      BUTTON = "button";

   function defArg() { return self::TEXT; }

   function kind() { return self::BUTTON; }

   function text($x=Tools::GET) { return $this->prop(self::TEXT,$x); }

   function isProp($p) {
      switch ($p) {
         case self::STYLE:
         case self::TEXT:
            return true;
         default:
            return parent::isProp($p);
      }
   }


}
