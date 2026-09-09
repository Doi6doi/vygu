<?php

namespace vygu;

/// billentyű
class Key {

   const
      PRESS = "press",
      RELEASE = "release";

   const
      MSHIFT = 1,
      MCTRL = 2,
      MALT = 4;

   const
      F1 = 1001,
      F2 = 1002,
      F3 = 1003,
      F4 = 1004,
      F5 = 1005,
      F6 = 1006,
      F7 = 1007,
      F8 = 1008,
      F9 = 1009,
      F10 = 1010,
      F11 = 1011,
      F12 = 1012,

      ALT = 605,
      ALTGR = 606,
      CAPS = 611,
      NUM = 612,
      LCTRL = 603,
      LSHIFT = 601,
      META = 610,
      RCTRL = 604,
      RSHIFT = 602,

      LEFT = 703,
      UP = 701,
      RIGHT = 704,
      DOWN = 702,

      ESC = 801,
      HOME = 802,
      END = 803,
      PGUP = 804,
      PGDN = 805,
      INS = 806,
      DEL = 807;
      
   public $event;
   public $unicode;
   public $special;
   public $scan;
   public $modif;

}

