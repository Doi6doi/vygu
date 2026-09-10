<?php

namespace vygu;

/// Multi line input memo [View]
class Memo extends View {

   const
      /// Kind constant
      MEMO = "memo";

   function kind() { return self::MEMO; }

   /// [View]::TEXT property
   function text($x=Tools::GET) { return $this->prop(self::TEXT,$x); }

   /// Inserts text at a point
   /// \param $at Insertion at this point (false: start, true:finish, int:index)
   /// \param $x The text to insert
   function insert( $at, $x ) {
      return Vygu::ins()->viewInsert($this, $at, $x);
   }

}
