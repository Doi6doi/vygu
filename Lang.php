<?php

namespace vygu;

/// Localization and translation class
class Lang {

   /// Language sources
   const
      /// LANG environment variable
      ENV = "!env",
      /// System language
      SYS = "!sys",
      /// No translation
      NONE = "!none";
 
   protected static $ins;

   /// Singleton
   /// \param $lang The required language. Can be ISO639, BCP47, \
   ///    or ENV, SYS, NONE constant
   static function ins( $lang = null ) {
      if (! self::$ins)
         self::$ins = new Lang( $lang );
      return self::$ins;
   }

   /// Convert string to localized (translated) form
   static function s($x) {
      return self::ins()->ss( $x );
   }
   
   protected $trans;
   
   /// Converts string to localized form
   function ss( $x ) {
      if (is_array($x)) {
         $ret = [];
         foreach ($x as $i)
            $ret [] = $this->ss($i);
         return $ret;
      }
      if (null !== $ret = Tools::g($this->trans,$x))
         return $ret;
      if (false !== $i = strpos( $x, "|" ))
         return substr($x,$i+1);
      return $x;
   }
   
   
}
