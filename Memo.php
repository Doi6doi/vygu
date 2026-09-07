<?php

namespace vygu;

/// Beviteli memo
class Memo extends View {

   const
      MEMO = "memo";

   const
      TEXTVIEW = "textview";

   function kind() { return self::MEMO; }

   function text($x=Tools::GET) { return $this->prop(self::TEXT,$x); }

   /// szöveg hozzáadása egy helyen
   function insert( $at, $x ) {
      return Vygu::ins()->viewInsert($this, $at, $x);
   }

}
