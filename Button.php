<?php

namespace vygu;

/// gomb
class Button extends View {

   const
      BUTTON = "button";

   function __construct($args) {
      parent::__construct($args);
      if ( null !== $args && ! is_array($args))
         $args = [ self::TEXT=>$args ];
      if ( null !== $t = Tools::g( $args, self::TEXT ))
         $this->text($t);
   }

   function kind() { return self::BUTTON; }

   function text($x=Tools::GET) { return $this->prop(self::TEXT,$x); }

}
