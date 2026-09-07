<?php

namespace vygu;

require_once(__DIR__."/../autoload.php" );

(new Edit())->run();

class Edit {

   public $memo;

   function run() {
      $this->init();
      Vygu::ins()->run();
   }

   /// view-k elkészítése
   function init() {
      $w = new Window([Window::TITLE=>"Edit",Window::MAIN=>true]);
      $w->handler( View::LAYOUT, function($g) { Layout::fill($g,[Layout::GAP=>2]); });
      $this->memo = $w->add( new Memo() );
      $w->menu( $this->initMenu() );
      $this->memo->focus();
   }

   /// menü elkészítése
   function initMenu() {
      $ret = new Menu();
      $f = $ret->add( new Menu("File") );
      $f->add( new Action("New") )
         ->short( new Key("ctrl+n"))
         ->handler( Action::FIRE, [$this,"fireNew"]);
      $f->add( new Action("Open"))
         ->short( new Key("ctrl+o"))
         ->handler( Action::FIRE, [$this,"fireOpen"]);
      $f->add( new Action("Save"))
         ->short( new Key("ctrl+s"))
         ->handler( Action::FIRE, [$this,"fireSave"]);
      $f->add( new Action("Save as"))
         ->short( new Key("shift+ctrl+s"))
         ->handler( Action::FIRE, [$this,"fireSaveAs"]);
      $f->add( new Action("Quit"))
         ->short( new Key("shift+ctrl+q"))
         ->handler( Action::FIRE, [$this,"fireQuit"]);
      $e = $ret->add( new Menu("Edit") );
      $e->add( new Action("Cut"))
         ->short( new Key("ctrl+x"))
         ->handler( Action::FIRE, [$this,"fireCut"]);
      $e->add( new Action("Copy"))
         ->short( new Key("ctrl+c"))
         ->handler( Action::FIRE, [$this,"fireCopy"]);
      $e->add( new Action("Paste"))
         ->short( new Key("ctrl+v"))
         ->handler( Action::FIRE, [$this,"firePaste"]);
   }

}

