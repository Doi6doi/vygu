<?php

namespace vygu;

class Layout {

   const
      ASCENT = "ascent",
      BOTTOM = "bottom",
      CENTERX = "centerX",
      CENTERY = "centerY",
      CONTWIDTH = "contWidth",
      CONTHEIGHT = "contHeight",
      DEFWIDTH = "defWidth",
      DEFHEIGHT = "defHeight",
      DESCENT = "descent",
      HEIGHT = "height",
      LEFT = "left",
      MINWIDTH = "minWidth",
      MINHEIGHT = "minHeight",
      RIGHT = "right",
      TOP = "top",
      WIDTH = "width";

   const
      COLS =  "cols",
      DIR  = "dir",
      GAP = "gap",
      ROWS = "rows";

   /// alap igazítás, egy oszlopos, gap:2
   static function def( Group $g ) {
      return self::grid( $g, [self::COLS=>1, self::GAP=>2] );
   }

   /// középre igazítja az összeset
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

   /// az első kitölti a helyet DIR irányba, a többi GAP-ekkel az ellenkező irányba
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

   /// rács elrendezés
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
