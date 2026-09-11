<?php

namespace vygu;

require_once(__DIR__."/../autoload.php" );

(new Edit())->run();

/// Notepad-like simple editor
class Edit {

   public $window;
   public $memo;
   public $changed;

   function run() {
      $this->init();
      Vygu::ins()->run();
   }

   /// Build window
   function init() {
      $this->window = (new Window([Window::TITLE=>"Edit",Window::MAIN=>true]))
         ->handler( Group::LAYOUT, [$this,"layout"] )
         ->handler( Window::CLOSING, [$this, "fileQuit"] )
         ->menu( $this->initMenu() );
      ($this->memo = $w->add( new Memo() ))
         ->focus();
   }

   /// Build menu
   function initMenu() {
      $ret = new Menu();
      $f = $ret->add( new Menu("File") );
      $f->add( new Action("New") )
         ->shortcut( "ctrl+n" )
         ->handler( Elem::FIRE, [$this,"fileNew"]);
      $f->add( new Action("Open"))
         ->shortcut( "ctrl+o" )
         ->handler( Elem::FIRE, [$this,"fileOpen"]);
      $f->add( new Action("Save"))
         ->shortcut( "ctrl+s" )
         ->handler( Elem::FIRE, [$this,"fileSave"]);
      $f->add( new Action("Save as"))
         ->shortcut( "shift+ctrl+s" )
         ->handler( Elem::FIRE, [$this,"fileSaveAs"]);
      $f->add( new Action("Quit"))
         ->shortcut( "shift+ctrl+q" )
         ->handler( Elem::FIRE, [$this,"fileQuit"]);
      $e = $ret->add( new Menu("Edit") );
      $e->add( new Action("Cut"))
         ->shortcut( "ctrl+x" )
         ->handler( Elem::FIRE, [$this,"editCut"]);
      $e->add( new Action("Copy"))
         ->shortcut( "ctrl+c" )
         ->handler( Elem::FIRE, [$this,"editCopy"]);
      $e->add( new Action("Paste"))
         ->shortcut( "ctrl+v" )
         ->handler( Elem::FIRE, [$this,"editPaste"]);
   }

   /// layout window
   function layout() {
      Layout::fill( $this->window, [Layout::GAP=>2] );
   }

   /// File/new handler
   function fileNew() { Tools::notImpl( $this, __FUNCTION__ ); }
   /// File/open handler
   function fileOpen() { Tools::notImpl( $this, __FUNCTION__ ); }
   /// File/save handler
   function fileSave() { Tools::notImpl( $this, __FUNCTION__ ); }
   /// File/saveAs handler
   function fileSaveAs() { Tools::notImpl( $this, __FUNCTION__ ); }
   /// File/quit handler
   function fileQuit() { Tools::notImpl( $this, __FUNCTION__ ); }
   /// Edit/cut handler
   function editCut() { Tools::notImpl( $this, __FUNCTION__ ); }
   /// Edit/copy handler
   function editCopy() { Tools::notImpl( $this, __FUNCTION__ ); }
   /// Edit/paste handler
   function editPaste() { Tools::notImpl( $this, __FUNCTION__ ); }

}

