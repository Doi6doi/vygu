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

   public $data;

   function __construct() {
      Vygu::ins()->screenCreate( $this );
   }

   function coord( $c ) {
      return Vygu::ins()->screenCoord( $this, $c );
   }

}
