<?php

function vygu_autoload($cls) {
   if ( preg_match('#^vygu\\\\(.*)$#', $cls, $m ))
      require_once( __DIR__."/".$m[1].".php");
}

spl_autoload_register( "vygu_autoload", true, true );
