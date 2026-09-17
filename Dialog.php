<?php

namespace vygu;

/// Dialog window
class Dialog {

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

   // dialog texts
   const
      CONFIRMTITLE = "dialog|Confirmation";

   // config window parts
   const
      FWINDOW = "window",
      FTEXT = "text",
      FBAR = "bar",
      FGUARD = "guard";

   const
      DEFGAP = 4;
   protected static $ins;

   // instance
   static function ins() {
      if ( ! self::$ins )
         self::$ins = new Dialog();
      return self::$ins;
   }

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

   /// Opens a dialog box with a text and a bar with  custom buttons
   /// \param $args Dialog arguments
   /// \return Chosen index
   static function confirm( $text, $args = [] ) {
      $c = self::ins()->confirmWindow();
      $c[self::FTEXT]->text( $text );
      $b = $c[self::FBAR];
      $g = $c[self::FGUARD];
      $g->over = null;
      $b->clear();
      if ( ! $bts = Tools::g( $args, self::BUTTONS ))
         $bts = ["dialog|OK"];
      for ($i=0; $i<count($bts); ++$i) {
         ($bb = new Button())
            ->text( $bts[$i] )
            ->handler( Action::FIRE, function() use ($g,$bts,$i) { 
               $g->data = $bts[$i]; $g->over = true; 
            })->parent($b);
      }
      ($w = $c[self::FWINDOW])->visible(true);
      $dz = $w->coords( [Layout::DEFWIDTH, Layout::DEFHEIGHT] );
      $sz = Screen::ins()->coords([Layout::CONTWIDTH,Layout::CONTHEIGHT]);
      $w->coords( [Layout::WIDTH, Layout::HEIGHT,Layout::CENTERX,Layout::CENTERY], 
         [$dz[0],$dz[1],$sz[0]>>1,$sz[1]>>1] );
      Vygu::ins()->run( $g );
      $w->visible(false);
      return $g->data;
   }

   /// Opens a Yes/No dialog window and waits for termination
   /// \param $args Dialog arguments
   /// \return Chosen index
   static function confirm2( $text, $args = [] ) {
      $args[ self::BUTTONS ] = [self::YES,self::NO];
      return self::confirm( $text, $args );
   }

   /// Opens a Yes/No/Cancel dialog window and waits for termination
   /// \param $args Dialog arguments
   /// \return Chosen index
   static function confirm3( $text, $args = [] ) {
      $args[ self::BUTTONS ] = [self::YES, self::NO, self::CANCEL];
      return self::confirm( $text, $args );
   }

   // konfig ablak 
   protected $confWnd;

   // confirm ablak
   function confirmWindow() {
      if ( ! $ret = $this->confWnd ) {
         $ret = [];
         $ret[ self::FWINDOW ] = ($w = new Window( self::CONFIRMTITLE ))
            ->handler( Group::LAYOUT, function() use ($w) {
               Layout::fill( $w, [Layout::DIR=>Layout::TOP, Layout::GAP=>self::DEFGAP] );
            })->handler( View::MEASURE, [$this,"confirmWindowMeasure"]);
         $s = Screen::ins();
         $ret[ self::FTEXT ] = $w->add( new Label() );
         $ret[ self::FBAR ] = ($b = new Group())
            ->handler( Group::LAYOUT, function($g) {
               Layout::grid( $g, [Layout::ROWS=>1, Layout::GAP=>self::DEFGAP]); 
            })->handler( View::MEASURE, [$this,"confirmBarMeasure"] )
            ->parent( $w );
         $ret[ self::FGUARD ] = new Guard();
         $this->confWnd = $ret;
      }
      return $ret;
   }
 
   // confirm ablak mérete
   function confirmWindowMeasure( $w, $c ) {
      $ret = Layout::sticked( $w, $c, 
         [Layout::DIR=>Layout::BOTTOM, Layout::GAP=>self::DEFGAP] );
      switch  ($c) {
         case Layout::DEFWIDTH:
            [$ww,$cw] = $w->coords( [Layout::WIDTH, Layout::CONTWIDTH] );
            return $ret + $ww - $cw;
         case Layout::DEFHEIGHT:
            [$wh,$ch] = $w->coords( [Layout::HEIGHT, Layout::CONTHEIGHT] );
            return $ret + $wh - $ch;
      }
   }
   
   // confirm léc mérete
   function confirmBarMeasure( $g, $c ) {
      $ga =  self::DEFGAP;
      switch ($c) {
         case Layout::DEFWIDTH:
            return $g->count() * (Layout::max($g, $c) + $ga) + $ga;
         case Layout::DEFHEIGHT:
            return Layout::max( $g, $c ) + 2*$ga;
      }
   }

}
