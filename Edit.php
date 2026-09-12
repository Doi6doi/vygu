<?php

namespace vygu;

/// Single ine edit [View]
class Edit extends View {

   const
      /// Kind constant
      EDIT = "edit";

   function kind() { return self::EDIT; }

   /// `TEXT` property
   function text($x=Tools::GET) { return $this->prop(self::TEXT,$x); }

   /// `POSITION` property
   function position($x = Tools::GET) { return $this->prop(self::POSITION,$x); }

   /// `SELLENGTH` property
   function selLength($x = Tools::GET) { return $this->prop(self::SELLENGTH,$x); }

   /// A part in text
   function part( $start, $len, $x = Tools::GET ) {
      return Vygu::ins()->textPart( $this, $start, $len, $x );
   }
   
   /// Selected part as string
   function selPart($x=Tools::GET) { 
      return $this->part( $this->position(), $this->selLength(), $x );
   }

   /// Inserts text at a point
   /// \param $at Insertion at this point (false: start, true:finish, int:index)
   /// \param $x The text to insert
   function insert( $at, $x ) {
      return $this->part( $at, 0, $x );
   }

   function isProp($p) {
      switch ($p) {
         case self::TEXT:
         case self::POSITION:
         case self::SELSTART:
            return true;
         default:
            return parent::isProp($p);
      }
   }

}
