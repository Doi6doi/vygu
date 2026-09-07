<?php

namespace vygu;

/// formázható beviteli memo
class Rich extends Memo {

   const
      RICH = "rich";

   const
      TEXTVIEW = "textview";

   function kind() { return self::RICH; }

   /// aktuális stílus
   function style($x=Tools::GET) { return $this->prop(self::STYLE,$x); }

}
