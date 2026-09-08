<?php

namespace vygu;

/// desktop terület
class Screen {

   protected static $ins;
   
   static function ins() {
      if ( ! self::$ins )
         self::$ins = new Screen();
      return self::$ins;
   }

   function coord( $c ) {
      return Vygu::ins()->screenCoord( $c );
   }

}
