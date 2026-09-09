<?php

namespace vygu;

require_once(__DIR__."/../autoload.php" );

$w = new Window([Window::TITLE=>"Hello", Window::MAIN=>true,
   Group::LAYOUT=>[Layout::class,"center"]]);
$w->add( new Label("Hello, World!") );
Vygu::ins()->run();
