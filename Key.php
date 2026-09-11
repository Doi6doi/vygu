<?php

namespace vygu;

/// A keyboard key
class Key {

   /// Events
   const
      /// key is pressed
      PRESS = "press",
      /// key is released
      RELEASE = "release";

   /// Modifiers
   const
      /// Shift is held
      MSHIFT = 1,
      /// Ctrl is held
      MCTRL = 2,
      /// Alt is held
      MALT = 4;

   /// Special keys
   const
      ///
      F1 = 1001,
      ///
      F2 = 1002,
      ///
      F3 = 1003,
      ///
      F4 = 1004,
      ///
      F5 = 1005,
      ///
      F6 = 1006,
      ///
      F7 = 1007,
      ///
      F8 = 1008,
      ///
      F9 = 1009,
      ///
      F10 = 1010,
      ///
      F11 = 1011,
      ///
      F12 = 1012,

      ///
      ALT = 605,
      ///
      ALTGR = 606,
      ///
      CAPS = 611,
      ///
      NUM = 612,
      ///
      LCTRL = 603,
      ///
      LSHIFT = 601,
      ///
      META = 610,
      ///
      RCTRL = 604,
      ///
      RSHIFT = 602,

      ///
      LEFT = 703,
      ///
      UP = 701,
      ///
      RIGHT = 704,
      ///
      DOWN = 702,

      ///
      ESC = 801,
      ///
      HOME = 802,
      ///
      END = 803,
      ///
      PGUP = 804,
      ///
      PGDN = 805,
      ///
      INS = 806,
      ///
      DEL = 807,
      ///
      KPENTER = 808;

   /// Parse key from string
   static function parse( $x ) {
      $arr = [ "ctrl+"=>self::MCTRL, "alt+"=>self::MALT, "shift+"=>self::MSHIFT ];
      foreach ($arr as $k=>$v) {
         $kl = strlen($k);
         if ($k == substr($x,0,$kl)) {
            $ret = self::parse( substr($x,$kl));
            $ret->modif |= $v;
            return $ret;
         }
      }
      $ret = new Key();
      if ( 1 == Tools::ulen($x) ) {
         $ret->unicode = $x;
      } else {
         throw new EVygu("Cannot parse key: $x");
      }
      return $ret;
   }

   /// The key event (pressed, released)
   public $event;
   /// Unicode value od pressed key if exists
   public $unicode;
   /// Special key value
   public $special;
   /// Hardware scan code
   public $scan;
   /// Held modifiers (shift, ctrl, alt)
   public $modif;

}

