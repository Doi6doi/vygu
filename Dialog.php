<?php

namespace vygu;

/// Dialog window
class Dialog extends Window {

   /// Dialog kinds
   const
      /// File open dialog
      OPEN = "open";

   /// Opens a file dialog window and waits for termination
   /// \param $args Dialog arguments
   /// \return Opened file name
   static function openFile( array $args = [] ) {
      return Vygu::ins()->dialog( self::OPEN, $args );
   }


}
