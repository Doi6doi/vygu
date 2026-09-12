<?php

namespace vygu;

/// Multi line input memo [View]
class Memo extends Edit {

   const
      /// Kind constant
      MEMO = "memo"; 

   function kind() { return self::MEMO; }

}
