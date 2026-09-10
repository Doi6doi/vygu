<?php

namespace vygu;

/// Multi line [Memo] with formattable parts
class Rich extends Memo {

   const
      /// Kind constant
      RICH = "rich";

   function kind() { return self::RICH; }

   /// [View]::STYLE property
   function style($x=Tools::GET) { return $this->prop(self::STYLE,$x); }

}
