<?php

namespace vygu;

/// hasznos cuccok
class Tools {

   const
      GET = "%0\x00\x01\x02\x04",
      U16L = "UTF-16LE",
      UTF = "UTF-8";

   /// array_get
   static function g($arr,$fld) {
      if ( is_array($arr) && array_key_exists($fld,$arr))
         return $arr[$fld];
      return null;
   }

   /// array_get + default
   static function gd($arr,$fld,$def) {
      if ( is_array($arr) && array_key_exists($fld,$arr))
         return $arr[$fld];
      return $def;
   }

   /// egész osztás
   static function div($a,$b) {
      return intdiv($a,$b);
   }

   /// egész osztás felkerekítéssel
   static function divu($a,$b) {
      return intdiv($a+$b-1,$b);
   }

   /// utf-8 karakter
   static function utf($uni) {
      return mb_chr($uni,self::UTF);
   }

   /// nem implementált függvény
   static function notImpl( $obj, $meth ) {
      throw new EVygu( "Not implemented: ".get_class($obj).".$meth" );
   }

   /// rendszer bitek száma
   static function sysBits() {
      return 8*PHP_INT_SIZE;
   }

   /// fájl betöltése
   static function loadFile($fname) {
      $ret = file_get_contents($fname);
      if ( false === $ret )
         throw new EVygu("Cannot load file: $fname");
      return $ret;
   }

   /// debug üzenet
   static function debug() {
      fwrite( STDERR, self::str(func_get_args())."\n" );
      fflush( STDERR );
   }

   /// akármi szöveggé
   static function str($x) {
      switch ($t = self::type($x)) {
         case "array":
            $ret = [];
            $a = self::isAssoc($x);
            foreach ($x as $k=>$v)
               $ret [] = ($a ? "$k:":"").self::str($v);
            return "[".implode(",",$ret)."]";
         break;
         case "FFI\CData": return self::cstr($x);
         case "boolean": return $x ? "true" : "false";
         case "null": return "null";
         default:
            if (is_object($x)) {
               if (method_exists($x,"__toString"))
                  return "$x";
               return "?$t";
            }
            return "$x";
      }
   }

   /// c null-e
   static function cIsNull($x) {
      return $x instanceof FFI\CData
         && FFI\CType::TYPE_POINTER == \FFI::typeof($x)
         && \FFI::isNull($x);
   }

   /// cdata
   static function cstr( \FFI\CData $x ) {
      if (self::cIsNull($x))
         return "NULL";
      $t = \FFI::typeof($x)->getName();
      switch ($t) {
         case "char*": return \FFI::string($x);
         default: return "?$t";
      }
   }

   /// asszoc tömb
   static function isAssoc($x) {
      if ( ! is_array($x)) return false;
      end($x);
      return key($x) !== count($x)-1;
   }

   /// elem típusa
   static function type($x) {
      if (is_object($x))
         return get_class($x);
      if (null === $x)
         return "null";
      return gettype($x);
   }

}
