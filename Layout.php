<?php

namespace vygu;

/// Class for different [Group] layouts
/// And coordinate constants.
/// Coordinates are usually in pixels, and
/// are relative to the parent's client area
class Layout {

   /// Coordinates
   const
      /// Distance between baseline and top
      ASCENT = "ascent",
      /// Bottom side Y coordinate (excluded)
      BOTTOM = "bottom",
      /// Center X coordinate
      CENTERX = "centerX",
      /// Center Y coordinate
      CENTERY = "centerY",
      /// Width of a [Groups]'s client area
      CONTWIDTH = "contWidth",
      /// Height of a [Groups]'s client area
      CONTHEIGHT = "contHeight",
      /// Default (preferred) width
      DEFWIDTH = "defWidth",
      /// Default (preferred) height
      DEFHEIGHT = "defHeight",
      /// Distance between baseline and bottom
      DESCENT = "descent",
      /// Height of View
      HEIGHT = "height",
      /// Left side X coordinate
      LEFT = "left",
      /// Minimal possible width
      MINWIDTH = "minWidth",
      /// Minimal possible height
      MINHEIGHT = "minHeight",
      /// Right side X coordinate (excluded)
      RIGHT = "right",
      /// Top side Y coordinate
      TOP = "top",
      /// Width os
      WIDTH = "width";

   /// Layout function arguments
   const
      /// Number of columns in `grid` layout
      COLS =  "cols",
      /// Direction of filling in `fill` layout
      DIR  = "dir",
      /// Space between parts and edge
      GAP = "gap",
      /// Number of rows in `grid` layout
      ROWS = "rows";

   /// Default layout for [Group]s: `grid[COLS=>1, GAP=>2]`
   static function def( Group $g ) {
      return self::grid( $g, [self::COLS=>1, self::GAP=>2] );
   }

   /// Puts all contained Views in the center
   static function center( Group $g ) {
      [$cw,$ch] = $g->coords([self::CONTWIDTH,self::CONTHEIGHT]);
      $cw >>= 1;
      $ch >>= 1;
      foreach ( $g->items as $i ) {
         [$dw,$dh] = $i->coords([self::DEFWIDTH,self::DEFHEIGHT]);
         $i->coords([self::WIDTH,self::HEIGHT,self::CENTERX,self::CENTERY],
            [$dw,$dh,$cw,$ch]);
      }
   }

   /// Puts 0th View in `DIR` direction, using all possible space
   /// And the remaining ones to the other side
   /// \param `DIR` The direction to fill
   /// \param `GAP` Space between items
   static function fill( Group $g, array $args = [] ) {
      $dir = Tools::gd( $args, self::DIR, self::TOP );
      $gap = Tools::gd( $args, self::GAP, 0 );
      [$cw,$ch] = $g->coords( [self::CONTWIDTH,self::CONTHEIGHT] );
      switch ($dir) {
         case self::LEFT: $at=$gap; $m=1; $ce=self::LEFT; break;
         case self::RIGHT: $at=$cw-$gap; $m=-1; $ce=self::RIGHT; break;
         case self::TOP: $at=$gap; $m=1; $ce=self::TOP; break;
         case self::BOTTOM: $at=$ch-$gap; $m=-1; $ce=self::BOTTOM; break;
         default: throw new EVygu("Unknown direction: $dir");
      }
      if ($hor = in_array( $dir, [self::LEFT,self::RIGHT] )) {
         $cd = self::DEFWIDTH;
         $cf = self::TOP;
      } else {
         $cd = self::DEFHEIGHT;
         $cf = self::LEFT;
      }
      $sum = 2*$gap;
      $first = true;
      foreach ($g->items as $i) {
         if ($first)
            $first = false;
            else $sum += $i->coord( $cd )+$gap;
      }
      $first = true;
      $iw = $cw-2*$gap;
      $ih = $ch-2*$gap;
      foreach ($g->items as $i) {
         if ($first) {
            if ($hor)
               $iw = $cw-$sum;
               else $ih = $ch-$sum;
            $first = false;
         } else {
            if ($hor)
               $iw = $i->coord( $cd );
               else $ih = $i->coord( $cd );
         }
         $i->coords( [self::WIDTH,self::HEIGHT,$ce,$cf],
            [$iw,$ih,$at,$gap] );
         $at += $m*(($hor?$iw:$ih)+$gap);
      }
   }

   /// Grid placement with same sized cells
   /// \param `ROWS` Number of rows
   /// \param `COLS` Number of columns
   /// \param `GAP` Space between items
   static function grid( Group $g, array $args = [] ) {
      $rows = Tools::g( $args, self::ROWS );
      $cols = Tools::g( $args, self::COLS );
      $gap = Tools::g( $args, self::GAP );
      if ( ! $n = $g->count() )
         return;
      if ( ! $rows ) {
         if ( ! $cols )
            $cols = intval(sqrt( $n ));
         $rows = Tools::divu( $n, $cols );
      } else if ( ! $cols ) {
         $cols = Tools::divu( $n, $rows );
      }
      [$cw,$ch] = $g->coords( [self::CONTWIDTH,self::CONTHEIGHT] );
      $w = Tools::div( $cw-$gap*($cols+1), $cols );
      $h = Tools::div( $ch-$gap*($rows+1), $rows );
      for ($i=0; $i<$n; ++$i) {
         $c = $i % $cols;
         $r = Tools::div( $i, $cols );
         $g->items[$i]->coords( [self::WIDTH,self::HEIGHT,self::LEFT,self::TOP],
            [$w, $h, $gap+$c*($gap+$w), $gap+$r*($gap+$h)]);
      }
   }

}
