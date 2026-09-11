<?php

namespace vygu;

require_once(__DIR__."/../autoload.php" );

(new Calc())->run();

/// a simple vygu calculator
class Calc {

   const
      BUTS = ["7","8","9","/","4","5","6","*","1","2","3","-","0",".","=","+"];

   public $old;
   public $op;
   public $curr;
   public $done;
   public $lab;
   public $wind;

   function run() {
      $this->init();
      Vygu::ins()->run();
   }

   /// builds the window
   function init() {
      $this->old = null;
      $this->op = null;
      $this->curr = null;
      $this->done = false;
      $w = $this->wind = (new Window([Window::TITLE=>"Calculator", Window::MAIN=>true]))
         ->handler( Group::LAYOUT, function ($g) {
            Layout::fill( $g, [Layout::DIR=>Layout::BOTTOM, Layout::GAP=>4] );
         })->handler( View::KEY, [$this,"key"]);
      $g = $w->add(new Group())
         ->handler( Group::LAYOUT, function($g) {
            Layout::grid( $g, [Layout::COLS=>4,Layout::GAP=>4]);
         });
      $this->lab = $w->add( new Label([View::TEXT=>"0",View::ALIGN=>Layout::CENTERX]) );
      foreach (self::BUTS as $b) {
         $g->add(new Button($b))
            ->handler( Elem::FIRE, function() use ($b) {
               $this->butFire($b);
            })->handler( View::KEY, [$this,"key"] );
      }
      $g->items[14]->focus();
      $this->show();
   }

   /// keyboard handler
   function key($k) {
      if ( Key::PRESS != $k->event ) return;
      $u = Tools::utf( $k->unicode );
      if ( "\r" == $u || Key::KPENTER == $k->special)
         $u = "=";
      if (in_array($u,self::BUTS)) {
         $this->butFire( $u );
            return true;
      }
   }

   /// fire handler for all buttons
   function butFire( $t ) {
      if ("0" <= $t && $t <= "9") {
         if (null === $this->op)
            $this->old = null;
         $this->curr .= $t;
      } else if (in_array($t,["+","-","*","/"])) {
         $this->calc();
         $this->op = $t;
      } else if ("." == $t) {
         if (false !== strpos($this->curr, $t))
            return;
         $this->curr .= $t;
      } else if ("=" == $t) {
         $this->calc();
      }
      $this->show();
   }

   /// a calculation for a non-number key
   function calc() {
      switch ($this->op) {
         case "+": $this->old += $this->curr; break;
         case "-": $this->old -= $this->curr; break;
         case "*": $this->old *= $this->curr; break;
         case "/": $this->old /= $this->curr; break;
         default:
            if ( null !== $this->curr )
               $this->old = $this->curr;
      }
      $this->op = null;
      $this->curr = null;
   }

   /// show the current operation in label
   function show() {
      $ret = [];
      if (null !== $this->old)
         array_push( $ret, $this->old );
      if (null !== $this->op)
         array_push( $ret, $this->op );
      if (null !== $this->curr)
         array_push( $ret, $this->curr );
      if ( $ret )
         $ret = implode(" ",$ret);
         else $ret = 0;
      $this->lab->text($ret);
   }

}


