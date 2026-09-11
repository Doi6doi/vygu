<?php

namespace vygu;

/// Useful static functions
class Tools {

   const
      /// Value used, when a property is not changed, just read.
      GET = "%0\x00\x01\x04",
      /// UTF16LE encoding
      U16L = "UTF-16LE",
      /// UTF-8 encoding
      UTF = "UTF-8";

   /// Warning-less `$arr[ $fld ] ` or `null`
   static function g($arr,$fld) {
      if ( is_array($arr) && array_key_exists($fld,$arr))
         return $arr[$fld];
      return null;
   }

   /// Returns `$arr[ $fld ] ` or throws [EVygu] if not exists
   static function gg($arr,$fld) {
      if ( is_array($arr) && array_key_exists($fld,$arr))
         return $arr[$fld];
      throw new EVygu("Unknown field: $fld");
   }

   /// Returns the first existing `$arr[ $f ]` from `$flds` or null
   static function ga($arr,array $flds) {
      if ( ! is_array($arr)) return null;
      foreach ($flds as $f) {
         if (array_key_exists($f,$arr))
            return $arr[$f];
      }
      return null;
   }

   /// Returns `$arr[ $fld ]`, or if not exists, `$def`
   static function gd($arr,$fld,$def) {
      if ( is_array($arr) && array_key_exists($fld,$arr))
         return $arr[$fld];
      return $def;
   }

   /// Removes first occurence `$x` from `$arr` (strict)
   static function arrayRemove( array & $arr, $x ) {
      if ( false !== $i = array_search( $x, $arr, true ))
         array_splice( $arr, $i, 1 );
   }

   /// Some bits of an integer
   /// \param $x The integer
   /// \param $at First returned bit index
   /// \param $n Number of returned bits
   /// \return The queried bits as integer
   static function bits($x,$at,$n) {
      return $x >> $at & ((1<<$n)-1);
   }

   /// An integer with some bits changed
   /// \param $x The input integer
   /// \param $at First changed bit index
   /// \param $n Number of changed bits
   /// \param $v Changed bit values
   /// \return A new integer with bits changed
   static function withBits($x,$at,$n,$v) {
      $mask = ((1<<$n)-1) << $at;
      return ($x & ~ $mask) | (($v << $at) & $mask);
   }

   /// Integer division
   static function div($a,$b) {
      return intdiv($a,$b);
   }

   /// Integer division rounding up
   static function divu($a,$b) {
      return intdiv($a+$b-1,$b);
   }

   /// An utf-8 character from Unicode value
   static function utf($uni) {
      return mb_chr($uni,self::UTF);
   }

   /// Char length of an utf8 string
   static function ulen($s) {
      return mb_strlen($s,self::UTF);
   }

   /// Lowercase utf8 string
   static function ulower($s) {
      return mb_strtolower($s,self::UTF);
   }

   /// Throws an [EVygu] saying `$obj` not implements `$meth`
   static function notImpl( $obj, $meth ) {
      throw new EVygu( "Not implemented: ".get_class($obj).".$meth" );
   }

   /// Number of bits in system processor (32,64)
   static function sysBits() {
      return 8*PHP_INT_SIZE;
   }

   /// Loads a file or throws [EVygu]
   static function loadFile($fname) {
      $ret = file_get_contents($fname);
      if ( false === $ret )
         throw new EVygu("Cannot load file: $fname");
      return $ret;
   }

   /// Shows debug values for all arguments on `STDERR`
   static function debug() {
      fwrite( STDERR, self::str(func_get_args())."\n" );
      fflush( STDERR );
   }

   /// Converts any value to string
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

   /// Is `$x` an FFI NULL value
   static function cIsNull($x) {
      return $x instanceof FFI\CData
         && FFI\CType::TYPE_POINTER == \FFI::typeof($x)
         && \FFI::isNull($x);
   }

   /// Converts FFI value to string
   static function cstr( \FFI\CData $x ) {
      if (self::cIsNull($x))
         return "NULL";
      $t = \FFI::typeof($x)->getName();
      switch ($t) {
         case "char*": return \FFI::string($x);
         default: return "?$t";
      }
   }

   /// Is `$x` an associative array
   static function isAssoc($x) {
      if ( ! is_array($x)) return false;
      end($x);
      return key($x) !== count($x)-1;
   }

   /// Type (or class name) of `$x`
   static function type($x) {
      if (is_object($x))
         return get_class($x);
      if (null === $x)
         return "null";
      return gettype($x);
   }

}
