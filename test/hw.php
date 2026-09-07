<?php

namespace vygu;

require_once(__DIR__."/../autoload.php" );

$w = new Window([Window::TITLE=>"Hello",Window::MAIN=>true]);
$w->add( new Label("Hello, World!") );
$w->handler( View::LAYOUT, [Layout::class, "center"] );
Vygu::ins()->run();

