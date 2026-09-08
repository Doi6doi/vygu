<?php

namespace vygu;

class Label extends View {

   const
      LABEL = "label";

   function defArg() { return self::TEXT; }

   function kind() { return self::LABEL; }

   function text($x=Tools::GET) { return Vygu::ins()->viewProperty($this,self::TEXT,$x); }

}
