<?php

namespace vygu;

/// A simple static Label [View]
class Label extends View {

   const
      /// Kind name
      LABEL = "label";

   function defArg() { return self::TEXT; }

   function kind() { return self::LABEL; }

   /// [View]::TEXT property
   function text($x=Tools::GET) { return $this->prop(self::TEXT,$x); }

   function isProp($p) {
      switch ($p) {
         case self::ALIGN:
         case self::STYLE:
            return true;
         case self::TEXT:
            return self::ISLANG;
         default:
            return parent::isProp($p);
      }
   }

}
