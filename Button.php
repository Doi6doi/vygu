<?php

namespace vygu;

/// gomb
class Button extends View {

   const
      BUTTON = "button";

   function defArg() { return self::TEXT; }

   function kind() { return self::BUTTON; }

   function text($x=Tools::GET) { return $this->prop(self::TEXT,$x); }

}
