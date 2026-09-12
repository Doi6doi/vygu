<?php

namespace vygu;

/// Clipboard handling
class Clipboard {

   protected static $ins;

   /// Singleton instance
   public static function ins() {
      if (! self::$ins)
         self::$ins = new Clipboard();
      return self::$ins;
   }
   
   public $data;

   function value( $x = Tools::GET ) {
      return Vygu::ins()->clipboardValue( $this, $x );
   }

}
