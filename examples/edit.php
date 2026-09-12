<?php

namespace vygu;

require_once(__DIR__."/../autoload.php" );

(new VyguEdit())->run();

/// Notepad-like simple editor
class VyguEdit {

   public $window;
   public $memo;

   public $fname;
   public $ftext;

   function run() {
      $this->init();
      Vygu::ins()->run();
   }

   /// Build window
   function init() {
      Lang::ins( Lang::ENV );
      $this->window = (new Window([Window::TITLE=>"title|Edit",Window::MAIN=>true]))
         ->handler( Group::LAYOUT, [$this,"layout"] )
         ->handler( Window::CLOSING, [$this, "fileQuit"] )
         ->menu( $this->initMenu() );
      ($this->memo = $this->window->add( new Memo() ))
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
      $e = $ret->add( new Menu("menu|Edit") );
      $e->add( new Action("Cut"))
         ->shortcut( "ctrl+x" )
         ->handler( Elem::FIRE, [$this,"editCut"]);
      $e->add( new Action("Copy"))
         ->shortcut( "ctrl+c" )
         ->handler( Elem::FIRE, [$this,"editCopy"]);
      $e->add( new Action("Paste"))
         ->shortcut( "ctrl+v" )
         ->handler( Elem::FIRE, [$this,"editPaste"]);
      return $ret;
   }

   /// layout window
   function layout() {
      Layout::fill( $this->window, [Layout::GAP=>2] );
   }

   /// Ask if file needs to be saved
   function saveQuery() {
      if ($this->memo->text() != $this->ftext) {
         switch (Dialog::confirm3("Save changes?")) {
            case Dialog::YES: $this->fileSave(); break;
            case Dialog::NO: break;
            case Dialog::CANCEL: return false;
         }
      }
      return true;
   }

   /// File/new handler
   function fileNew() { 
      if (! $this->saveQuery())
         return;
      $this->fname = null;
      $this->ftext = "";
      $this->memo->text("");
   }

   /// File/open handler
   function fileOpen() { 
      if (! $this->saveQuery())
         return;
      if (! $this->fname = Dialog::openFile())
         return;
      $t = $this->ftext = Tools::loadFile( $this->fname );
      $this->memo->text( $t );
   }

   /// File/quit handler
   function fileQuit() {
      if (! $this->saveQuery())
         return false;
      Vygu::ins()->finish();
      return true;
   }

   /// File/save handler
   function fileSave() { 
      if ( ! $this->fname )
         return $this->fileSaveAs();
      $t = $this->memo->text();
      Tools::saveFile( $this->fname, $t );
      $this->ftext = $t;
   }

   /// File/saveAs handler
   function fileSaveAs() {
      if ( ! $fn = Dialog::saveFile() )
         return;
      $this->fname = $fn;
      return $this->fileSave();
   }

   /// Edit/cut handler
   function editCut() {
      $this->editCopy();
      $this->memo->selPart( "" );
  }
   
   /// Edit/copy handler
   function editCopy() { 
      Clipboard::ins()->value( $this->memo->selPart() );
   }
      
   /// Edit/paste handler
   function editPaste() { 
      $this->memo->selPart( Clipboard::ins()->value() );
   }

}

