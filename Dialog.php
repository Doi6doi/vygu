<?php

namespace vygu;

/// Dialog window
class Dialog extends Window {

   /// Dialog kinds
   const
      /// Confirmation dialog
      CONFIRM = "confirm",
      /// File save dialog
      SAVE = "save",
      /// File open dialog
      OPEN = "open";

   /// Dialog arguments
   const
      /// appearing buttons
      BUTTONS = "buttons";

   /// Dialog buttons
   const
      /// Yes button
      YES = "dialog|Yes",
      /// No button
      NO = "dialog|No",
      /// Cancel button
      CANCEL = "dialog|Cancel";

   /// Opens a file open dialog window and waits for termination
   /// \param $args Dialog arguments
   /// \return Open file name
   static function openFile( array $args = [] ) {
      return Vygu::ins()->dialog( self::OPEN, $args );
   }

   /// Opens a file save dialog window and waits for termination
   /// \param $args Dialog arguments
   /// \return Save file name
   static function saveFile( array $args = [] ) {
      return Vygu::ins()->dialog( self::SAVE, $args );
   }

   /// Opens a Yes/No dialog window and waits for termination
   /// \param $args Dialog arguments
   /// \return Chosen index
   static function confirm( $text, $args = [] ) {
      $args[ self::BUTTONS ] = Lang::s( [self::YES, self::NO] );
      $args[ self::TEXT ] = $text;
      $ret = Vygu::ins()->dialog( self::CONFIRM, $args );
      switch ($ret) {
         case 0: return true;
         case 1: return false;
         default: return null;
      }
   }

   /// Opens a Yes/No/Cancel dialog window and waits for termination
   /// \param $args Dialog arguments
   /// \return Chosen index
   static function confirm3( $text, $args = [] ) {
      $args[ self::BUTTONS ] = Lang::s( [self::YES, self::NO, self::CANCEL] );
      $args[ self::TEXT ] = $text;
      $ret = Vygu::ins()->dialog( self::CONFIRM, $args );
      switch ($ret) {
         case 0: return true;
         case 1: return false;
         default: return null;
      }
   }

}
