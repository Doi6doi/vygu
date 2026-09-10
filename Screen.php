<?php

namespace vygu;

/// Class for the desktop scrren
class Screen {

   protected static $ins;

   /// Singleton instance
   static function ins() {
      if ( ! self::$ins )
         self::$ins = new Screen();
      return self::$ins;
   }

   public $data;

   function __construct() {
      Vygu::ins()->screenCreate( $this );
   }

   /// Get a screen coordinate
   /// \param $c One of the [Layout] coordinates \
   /// (`HEIGHT`, `WIDTH`, `CONTHEIGHT`, `CONTWIDTH`)
   function coord( $c ) {
      return Vygu::ins()->screenCoord( $this, $c );
   }

}
