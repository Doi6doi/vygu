<?php

namespace vygu;

class Label extends View {

   const
      LABEL = "label";

   function __construct($args) {
      parent::__construct($args);
      if ( null !== $args && ! is_array($args))
         $args = [ self::TEXT=>$args ];
      if ( null !== $t = Tools::g( $args, self::TEXT ))
         $this->text($t);
   }

   function kind() { return self::LABEL; }

   function text($x=Tools::GET) { return Vygu::ins()->viewProperty($this,self::TEXT,$x); }

}
