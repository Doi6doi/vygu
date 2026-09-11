<?php

namespace vygu;

/// Gtk4 engine
class Gtk4 extends Vygu {

   const
      /// Kind name
      GTK4 = "gtk4";

   const
      GVYGU = "gvygu",
      GSHORTCUT = "gShortcut",
      KEYCTRL = "keyCtrl",
      LAYOUTMGR = "layoutMgr",
      HANDLERIDS = "handlerIDs",
      HANDLERREC = "handlerRec",
      HANDLERPTR = "handlerPtr",
      MENUBAR = "menuBar",
      MENUBOX = "menuBox",
      WINDOWBOX = "windowBox",
      TEXTBUFFER ="textBuffer",
      TEXTITER = "textIter",
      TEXTITER2 = "textIter2",
      TEXTVIEW = "textView";

   // temp részek
   const
      LAST = "last",
      MESX = "mesx",
      MESY = "mesy",
      RECT = "rect";

   const
      MALIGN = [
         "0"=>Layout::LEFT,
         "0.5"=>Layout::CENTERX,
         "1"=>Layout::RIGHT
      ],
      MCURSOR = [
         "default"=>Cursor::DEFAULT,
         "wait"=>Cursor::WAIT
      ],
      MKEY = [
         0xfe03 => Key::ALTGR,
         0xff1b => Key::ESC,
         0xff50 => Key::HOME,
         0xff51 => Key::LEFT,
         0xff52 => Key::UP,
         0xff53 => Key::RIGHT,
         0xff54 => Key::DOWN,
         0xff55 => Key::PGUP,
         0xff56 => Key::PGDN,
         0xff57 => Key::END,
         0xff63 => Key::INS,
         0xff7f => Key::NUM,
         0xff8d => Key::KPENTER,
         0xffbe => Key::F1,
         0xffbf => Key::F2,
         0xffc0 => Key::F3,
         0xffc1 => Key::F4,
         0xffc2 => Key::F5,
         0xffc3 => Key::F6,
         0xffc4 => Key::F7,
         0xffc5 => Key::F8,
         0xffc6 => Key::F9,
         0xffc7 => Key::F10,
         0xffc8 => Key::F11,
         0xffc9 => Key::F12,
         0xffe1 => Key::LSHIFT,
         0xffe2 => Key::RSHIFT,
         0xffe3 => Key::LCTRL,
         0xffe4 => Key::RCTRL,
         0xffe5 => Key::CAPS,
         0xffe9 => Key::ALT,
         0xffeb => Key::META,
         0xffff => Key::DEL
      ];

   // gtk konstansok
   const
      GTK_ORIENTATION_VERTICAL = 1;

   // ffi kapcsolat
   protected $ffi;
   // kivétel eseménykezelőben
   protected $err;
   // alloc lekéréshez
   protected $allo;
   // rect lekéréshez
   protected $rect;
   // intek visszaadásához
   protected $ints;
   // igen/nem konstansok
   protected $yesno;
   /// action számozás
   protected $nact;
   /// action group
   protected $actgrp;

   function __construct($args) {
      parent::__construct($args);
      $this->maps = [];
      $h = Tools::loadFile( __DIR__."/gtk4.h" );
      $f = $this->ffi = \FFI::cdef( $h, "libgtk-4.so.1" );
      $f->gtk_init();
      $this->allo = $f->new("GtkAllocation");
      $this->rect = $f->new("GdkRectangle");
      $this->actgrp = $f->g_simple_action_group_new();
      $this->ints = $f->new("int[4]");
      $this->yesno = $f->new("int[2]");
      $this->yesno[0] = 0;
      $this->yesno[1] = 1;
   }

   function runStep( $wait ) {
      if ( $ret = $this->ffi->g_main_context_iteration(null,$wait))
         $this->checkErr();
      return $ret;
   }

   function handlerCreate(Elem $v, $e, callable $cb) {
      $ret = parent::handlerCreate($v,$e,$cb);
      $ret->data = [Elem::ELEM =>$v];
      switch ($e) {
         case View::KEY: $this->handlerCreateKey( $ret ); break;
         case Elem::FIRE:
            $this->handlerCreateSignal( $ret, false );
         break;
         case Window::CLOSING:
            $this->handlerCreateSignal( $ret, true );
         break;
         case Group::LAYOUT: $this->handlerCreateLayout( $ret ); break;
         default: parent::handlerCreate($e,$cb);
      }
      return $ret;
   }

   function elemCreate( Elem $v ) {
      $f = $this->ffi;
      switch ($k = $v->kind()) {
         case Button::BUTTON: $ret = $f->gtk_button_new(); break;
         case Group::GROUP: $ret = $f->gtk_fixed_new(); break;
         case Label::LABEL: $ret = $f->gtk_label_new(null); break;
         case Memo::MEMO: case Rich::RICH:
            $ret = $this->createMemo($v);
         break;
         case Menu::MENU: 
            $ret = $f->g_menu_new();
            $v->data = [];
         break;
         case Action::ACTION:
            $id = Action::ACTION.(++$this->nact);
            $ret = $f->g_simple_action_new( $id, null );
            $f->g_action_map_add_action( $this->actgrp , $ret );
            $v->data = [ Elem::ID => $id ];
         break;
         case Window::WINDOW:
            $wb = $this->check( $f->gtk_fixed_new(),
               "Could not create window box");
            $mb = $this->check( $f->gtk_box_new(self::GTK_ORIENTATION_VERTICAL, 0),
               "Could not create menu box");
            $f->gtk_widget_set_hexpand( $wb, true );
            $f->gtk_widget_set_vexpand( $wb, true );
            $f->gtk_box_append( $mb, $wb );
            $v->data = [
               self::MENUBOX=>$mb,
               self::WINDOWBOX => $wb
            ];
            $ret = $f->gtk_window_new();
            $f->gtk_window_set_child( $ret, $mb );
         break;
         default: return parent::elemCreate($v);
      }
      $this->check( $ret, "Could not create gtk4 $k" );
      $v->impl = $this->sink( $ret );
   }

   function menuAdd( Menu $m, $x ) {
      $f = $this->ffi;
      if ($x instanceof Menu) {
         $f->g_menu_append_submenu(
            $m->impl, $x->name(), $x->impl );
      } else if ($x instanceof Action) {
         $f->g_menu_append( $m->impl, $x->name(), 
            self::GVYGU.".".$x->data[ Elem::ID ] );
      } else
         parent::menuAdd( $m, $x );
   }

   function styleCreate( Style $s ) {
   }

   // View style jellemzője
   function viewStyle( View $v, $x ) {
      switch ( $k = $v->kind() ) {
         case Rich::RICH: return $this->richStyle( $v, $x );
         default: throw new EVygu("Cannot access style of $k");
      }
   }

   // View kurzor jellemzője
   function viewCursor( View $v, $x ) {
      $f = $this->ffi;
      if ( Tools::GET === $x ) {
         $c = $f->gtk_widget_get_cursor( $v->impl );
         if (\FFI::isNull($c))
            return null;
         $n = $f->gdk_cursor_get_name( $c );
         return Tools::gg( self::MCURSOR, $n );
      } else {
         $c = $this->fromCursor( $x );
         $f->gtk_widget_set_cursor_from_name( $v->impl, $c );
         return $v;
      }
   }

   // Elem name jellemzője
   function elemName( Elem $v, $x ) {
      $g = Tools::GET === $x;
      switch ($k=$v->kind()) {
         case Action::ACTION:
         case Menu::MENU:
            if ($g)
               return Tools::g( $v->data, Elem::NAME );
               else $v->data[Elem::NAME] = "$x";
         break;
         default: return parent::elemProperty( $v, Elem::NAME, $x );
      }
      return $v;
   }

   /// Action shortcut jellemzője
   function actionShortcut( Action $a, $x ) {
      $f = $this->ffi;
      $old = Tools::g( $a->data, Action::SHORTCUT );
      if (Tools::GET === $x)
         return $old;
      if ( $old  ) {
         $f->gtk_shortcut_controller_remove_shortcut( $this->sctc, 
            $a->data[ self::GSHORTCUT ] );
         $a->data[ self::GSHORTCUT ] = $a->data[ Action::SHORTCUT ] = null;
      }
      if ( $x ) {
         if ( ! $x instanceof Key )
            $x = Key::parse( $x );
         $ss = $this->shortcutStr( $x );
         $st = $f->gtk_shortcut_trigger_parse_string( $ss );
         $this->check( $st, "Could not create shortcut trigger: $ss");
         $sa = $f->gtk_shortcut_action_parse_string( 
            sprintf("action(%s.%s)", self::GVYGU, $a->data[ Elem::ID ] ));
         $this->check( $sa, "Could not create shortcut action");
         $sc = $f->gtk_shortcut_new( $st, $sa );
         $this->check( $sc, "Could not create shortcut" );
         $a->data[ self::GSHORTCUT ] = $this->sink( $sc );
         $a->data[ Action::SHORTCUT ] = $x;
      }
      return $a;
   }

   // View align jellemzője
   function viewAlign( View $v, $x ) {
      $f = $this->ffi;
      $g = Tools::GET === $x;
      switch ($k = $v->kind()) {
         case Label::LABEL:
            if ($g) {
               return Tools::g( self::MALIGN,
                  "".$f->gtk_label_get_xalign( $v->impl ));
            } else {
               $f->gtk_label_set_xalign( $v->impl,
                  Tools::g($this->map( View::ALIGN ), $x ) );
            }
         break;
         default: throw new EVygu("Cannot access $k.align");
      }
      return $v;
   }

   // richedit stílusa
   function richStyle( Rich $r, $s ) {
      if ( Tools::GET === $s )
         return $this->richStyleRead( $r );
      throw new EVygu("Cannot set rich style");
      return $r;
   }

   function elemDestroy( Elem $v ) {
      if ( $v instanceof Group )
         $v->clear();
      $this->ffi->g_object_unref( $v->impl );
   }

   function elemHandler(Elem $v, $e, ?Handler $o, ?Handler $h) {
      switch ($e) {
         case Window::CLOSING: case Elem::FIRE:
            return $this->elemHandlerSignal( $v, $e, $o, $h );
         case View::KEY:
            return $this->elemHandlerKey( $v, $e, $o, $h );
         case Group::LAYOUT:
            return $this->elemHandlerLayout( $v, $e, $o, $h );
         default:
            return parent::elemHandler( $v, $e, $o, $h );
      }
   }

   // insert művelet
   function viewInsert( View $v, $at, $x ) {
      $f = $this->ffi;
      switch ($k = $v->kind()) {
         case Memo::MEMO:
         case Rich::RICH:
            $x = Tools::str($x);
            $b = $v->data[ self::TEXTBUFFER ];
            $i = $v->data[ self::TEXTITER ];
            $this->bufferMove( $b, $at, $i );
            $f->gtk_text_buffer_insert( $b, $i, $x, -1 );
        break;
        default: return parent::viewInsert( $v, $at, $x );
      }
   }

   function elemProperty( Elem $v, $p, $x ) {
      switch ($p) {
         case Action::SHORTCUT: return $this->actionShortcut( $v, $x );
         case Elem::NAME: return $this->elemName($v,$x);
         case View::ALIGN: return $this->viewAlign($v,$x);
         case View::CURSOR: return $this->viewCursor($v,$x);
         case View::STYLE: return $this->viewStyle($v,$x);
         case View::TEXT: return $this->viewText($v,$x);
         case Window::MENU: return $this->windowMenu($v,$x);
      }
      $f = $this->ffi;
      $im = $v->impl;
      if (Tools::GET === $x) {
         switch ($p) {
            case View::VISIBLE: return $f->gtk_widget_get_visible($im);
            case Window::TITLE: return $f->gtk_window_get_title($im);
            default: return parent::elemProperty($v,$p,$x);
         }
      } else {
         switch ($p) {
            case View::VISIBLE: $f->gtk_widget_set_visible($im,$x); break;
            case Window::TITLE: $f->gtk_window_set_title($im,$x); break;
            default: return parent::elemProperty($v,$p,$x);
         }
      }
      return $v;
   }

   // view koordináta
   function viewCoord( View $v, $c, $x, & $tmp ) {
      $f = $this->ffi;
      $im = $v->impl;
      $g = Tools::GET === $x;
      // amihez nem kell rect
      switch ($c) {
         case Layout::ASCENT:
         case Layout::DEFWIDTH:
         case Layout::DEFHEIGHT:
         case Layout::DESCENT:
            return $this->viewCoordSpec( $v, $c, $x, $tmp );
         case Layout::CONTWIDTH:
            $cim = $this->contImpl($v);
            $cw = $f->gtk_widget_get_width( $cim );
            if ($g) return $cw;
         break;
         case Layout::CONTHEIGHT:
            $cim = $this->contImpl($v);
            $ch = $f->gtk_widget_get_height( $cim );
            if ($g) return $ch;
         break;
         case Layout::HEIGHT:
            if ($g) return $f->gtk_widget_get_height( $im );
         break;
         case Layout::WIDTH:
            if ($g) return $f->gtk_widget_get_width( $im );
         break;
      }
      $r = $this->temp( $v, self::RECT, $tmp );
      if ($g) {
         switch ($c) {
            case Layout::BOTTOM: return $r->y + $r->height;
            case Layout::CENTERX: return $r->x + ($r->width >> 1);
            case Layout::CENTERY: return $r->y + ($r->height >> 1);
            case Layout::LEFT: return $r->x;
            case Layout::RIGHT: return $r->x + $r->width;
            case Layout::TOP: return $r->y;
            default: return parent::viewCoord($v,$c,$x,$tmp);
         }
      } else {
         switch ($c) {
            case Layout::BOTTOM: $r->y = $x-$r->height; break;
            case Layout::CENTERX: $r->x = $x-($r->width >> 1); break;
            case Layout::CENTERY: $r->y = $x-($r->height >> 1); break;
            case Layout::CONTWIDTH: $r->width += $x - $cw; break;
            case Layout::CONTHEIGHT: $r->height += $x - $ch; break;
            case Layout::HEIGHT: $r->height = $x; break;
            case Layout::LEFT: $r->x = $x; break;
            case Layout::RIGHT: $r->x = $x-$r->width; break;
            case Layout::TOP: $r->y = $x; break;
            case Layout::WIDTH: $r->width = $x; break;
            default: return parent::viewCoord($v,$c,$x,$tmp);
         }
         if ( Tools::g($tmp,self::LAST) )
            $this->viewCoordLast( $v, $tmp );
      }
   }

   function dialog( $kind, array $args ) {
      $f = $this->ffi;
      $g = new Guard();
      switch ($kind) {
         case Dialog::OPEN:
            $d = $this->check( $f->gtk_file_dialog_new(),
               "Coulld not open file dialog");
            $cb = function( $source, $result, $data ) use ($f,$g,$d) {
               $gf = $f->gtk_file_dialog_open_finish(
                  $d, $result, null );
               $gfc = $f->g_file_get_path( $gf );
               $g->data = \FFI::string( $gfc );
               $g->over = true;
               $f->g_free( $gfc );
               $f->g_object_unref( $gf );
            };
            $f->gtk_file_dialog_open( $d, null, null, $cb, null );
            $this->run( $g );
            return $g->data;
         break;
         default:
            return parent::dialog( $kind, $args );
      }
   }

   function viewParent( View $v, ?Group $g ) {
	  $f = $this->ffi;
	  if ( ! $g )
	     return $f->gtk_widget_unparent( $v->impl );
	  switch ($g->kind()) {
         case Group::GROUP:
         case Window::WINDOW:
            return $f->gtk_widget_set_parent( $v->impl,
               $this->contImpl( $g ) );
         default: parent::viewParent( $v, $g );
      }
   }

   function viewFocus(View $v) {
      return $this->ffi->gtk_widget_grab_focus( $this->realImpl( $v ) );
   }

   function screenCreate( Screen $s ) {
      $s->data = $this->check( $this->ffi->gdk_display_get_default(),
         "Could not get display" );
   }

   function screenCoord( Screen $s, $c ) {
      $f = $this->ffi;
      switch ($c) {
         case Layout::CONTWIDTH: return $this->screenCoord( $s, Layout::WIDTH );
         case Layout::CONTHEIGHT: return $this->screenCoord( $s, Layout::HEIGHT );
         case Layout::WIDTH:
         case Layout::HEIGHT:
            break;
         default:
            return parent::screenCoord($s,$c);
      }
      $f->gdk_monitor_get_geometry( $this->monitor($s),
         \FFI::addr( $this->rect ));
      switch ($c) {
         case Layout::WIDTH: return $this->rect->width;
         case Layout::HEIGHT: return $this->rect->height;
      }
      return parent::screenCoord($s,$c);
   }

   /// gtk-s shortcut string
   protected function shortcutStr( Key $k ) {
      if ($u = $k->unicode)
         $ret = Tools::ulower( $u );
         else $ret = $this->shortcutSpec( $k->special );
      $m = $k->modif;
      if ( $m & Key::MSHIFT )
         $ret = "<Shift>$ret";
      if ( $m & Key::MALT )
         $ret = "<Alt>$ret";
      if ( $m & Key::MCTRL )
         $ret = "<Control>$ret";
      return $ret;
   }

   /// speciális key gtk-shortcut értéke
   protected function shortcutSpec( $s ) {
      switch ($s) {
         default: throw new EVygu("Unknown shortcut spec: $s");
      }
   }

   // a valódi widget impl
   protected function realImpl( View $v ) {
      switch ($v->kind()) {
         case Memo::MEMO:
         case Rich::RICH:
            return $v->data[ self::TEXTVIEW ];
         default: return $v->impl;
      }
   }

   // a konténer impl
   protected function contImpl( Group $g ) {
      switch ($g->kind()) {
         case Window::WINDOW:
            return $g->data[ self::WINDOWBOX ];
         default: return $g->impl;
      }
   }

   // event-hez tartozó signal neve
   protected function eventSignal(Elem $v,$e,$d=null) {
      switch ($e) {
         case Elem::FIRE:
            switch ($k = $v->kind()) {
               case Button::BUTTON: return "clicked";
               case Action::ACTION: return "activate";
               default: throw new EVygu("Unknown fire signal for $k");
            }
         break;
         case View::KEY: return $d[0] ? "key-pressed":"key-released";
         case Window::CLOSING: return "close-request";
         default: throw new EVygu("Unknown event: $e");
      }
   }

   // a globális $err ellenőrzése, és dobása
   protected function checkErr() {
      if ( $e = $this->err ) {
         $this->err = null;
         throw $e;
      }
   }

   // valamilyen view text-je
   protected function viewText($v,$x) {
      $im = $v->impl;
      $f = $this->ffi;
      $g = Tools::GET === $x;
      switch ($v->kind()) {
         case Label::LABEL:
            if ($g)
               return $f->gtk_label_get_text($im);
               else $f->gtk_label_set_text($im,"$x");
         break;
         case Button::BUTTON:
            if ($g)
               return $f->gtk_button_get_label($im);
               else $f->gtk_button_set_label($im,"$x");
         break;
         case Memo::MEMO:
         case Rich::RICH:
            $b = $v->data[self::TEXTBUFFER];
            if ($g) {
               $i = $v->data[ self::TEXTITER ];
               $i2 = $v->data[ self::TEXTITER2 ];
               $f->gtk_text_buffer_get_bounds( $b, $i, $i2 );
               return $f->gtk_text_buffer_get_text( $b, $i, $i2, false );
            } else {
               $f->gtk_text_buffer_set_text( $b, "$x", -1 );
            }
         break;
         default:
            return parent::elemProperty($v,View::TEXT,$x);
      }
      return $v;
   }

   // window menüje
   protected function windowMenu( Window $w, $x ) {
      $old = Tools::g( $w->data, Menu::MENU );
      if ( Tools::GET == $x )
         return $old;
      $f = $this->ffi;
      $mb = $w->data[ self::MENUBOX ];
      if ( $old ) {
         $f->gtk_box_remove( $mb, $w->data[self::MENUBAR] );
         $w->data[self::MENUBAR] = $w->data[Menu::MENU] = null;
      }
      if ( $x ) {
         $mr = $this->check( $f->gtk_popover_menu_bar_new_from_model( $x->impl ),
            "Could not create menu bar");
         $f->gtk_widget_insert_action_group( $mr, self::GVYGU, $this->actgrp );
         $f->gtk_box_prepend( $mb, $mr );
         $w->data[ self::MENUBAR ] = $mr;
         $w->data[ Menu::MENU ] = $x;
      }
      return $w;
   }

   // richedit készítése és összerakása
   protected function createMemo(View $r) {
      $f = $this->ffi;
      $s = $f->gtk_scrolled_window_new();
      $this->check( $s, "Could not create gtk scrolled window" );
      $v = $f->gtk_text_view_new();
      $this->check( $s, "Could not create gtk text view" );
      $b = $f->gtk_text_view_get_buffer( $v );
      $this->check( $b, "Could not get gtk text buffer" );
      $i = \FFI::addr( $f->new("GtkTextIter") );
      $i2 = \FFI::addr( $f->new("GtkTextIter") );
      $f->gtk_scrolled_window_set_child( $s, $v );
      $r->data = [
         self::TEXTVIEW => $v,
         self::TEXTBUFFER => $b,
         self::TEXTITER => $i,
         self::TEXTITER2 => $i2
      ];
      return $s;
   }

   // bufferen belüli mozgás
   protected function bufferMove( $b, $at, $i ) {
      $f = $this->ffi;
      if ( false === $at )
         $f->gtk_text_buffer_get_start_iter( $b, $i );
      else if ( true === $at )
         $f->gtk_text_buffer_get_end_iter( $b, $i );
      else
         $f->gtk_text_buffer_get_iter_at_offset( $b, $i, $at );
   }

   // floating reference sink
   protected function sink( $x ) {
      $this->ffi->g_object_ref_sink( $x );
      return $x;
   }

   // signal kezelő
   protected function handlerCreateSignal( Handler $h, $inv ) {
      $f = $this->ffi;
      $c = $f->new( "sSignalCallback" );
      $c->c = function( $impl, $udata ) use ($h,$inv) {
         $ret = $this->callCallback($h->cb);
         return $inv ? ! $ret : $ret;
      };
      $h->data = [
         self::HANDLERPTR => $c->c,
         self::HANDLERIDS => []
      ];
   }

   // gombynomás kezelő
   protected function handlerCreateKey( Handler $h ) {
      $f = $this->ffi;
      $c = $f->new( "sKeyCallback" );
      $c->c = function( $ctrl, $kVal, $kCode, $state, $data ) use ($h) {
         return $this->callCallback( $h->cb,
            [$this->key( $kVal, $kCode, $state, $data )] );
      };
      $h->data = [
         self::HANDLERPTR => $c->c,
         self::HANDLERIDS => []
      ];
   }

   // layout kezelő
   protected function handlerCreateLayout( Handler $h ) {
      $f = $this->ffi;
      $c = $f->new( "sLayoutCallback" );
      $c->measure = function( $widget, $ori, $fors, $min, $nat,
         $min_base, $nat_base )
      {
         $min[0] = 0;
         $nat[0] = 0;
         $min_base[0] = -1;
         $nat_base[0] = -1;
      };
      $c->allocate = function( $widget, $width, $height, $base ) use ($h) {
         return $this->callCallback( $h->cb, [$h->data[ Elem::ELEM ]] );
      };
      $h->data[ self::HANDLERREC ] = $c;
   }

   // signal kezelő be-vagy kikapcsolása
   protected function handlerSignal( Elem $v, $e, $h, $on, $data = null ) {
      if ( ! $h ) return;
      $f = $this->ffi;
      switch ($e) {
         case View::KEY: $im = $this->viewController( $v, self::KEYCTRL ); break;
         default: $im = $v->impl;
      }
      if ($on) {
         $s = $this->eventSignal($v,$e,$data);
         $h->data[ self::HANDLERIDS ] [] =
            $f->g_signal_connect_data( $im, $s,
               $f->cast("gpointer",$h->data[ self::HANDLERPTR ]),
               $data, null, 0 );
      } else {
         foreach ( $h->data[ self::HANDLERIDS ] as $i  )
            $f->g_signal_handler_disconnect( $im, $i );
      }
   }

   // signal kezelő beállítása
   protected function elemHandlerSignal( $v, $e, $o, $h ) {
      $this->handlerSignal( $v, $e, $o, false );
      $this->handlerSignal( $v, $e, $h, true );
   }

   // key kezelő beállítása
   protected function elemHandlerKey( $v, $e, $o, $h ) {
      $f = $this->ffi;
      $this->handlerSignal( $v, $e, $o, false );
      $this->handlerSignal( $v, $e, $h, true, \FFI::addr($this->yesno[0]));
      $this->handlerSignal( $v, $e, $h, true, \FFI::addr($this->yesno[1]));
   }

   // layout kezelő beállítása
   protected function elemHandlerLayout( $v, $e, $o, $h ) {
      $f = $this->ffi;
      if ($h) {
         $hr = $h->data[ self::HANDLERREC ];
         $cl = $f->gtk_custom_layout_new( null, $hr->measure, $hr->allocate  );
      } else {
         $cl = null;
      }
      $f->gtk_widget_set_layout_manager( $this->contImpl($v), $cl );
   }

   // cotroller gyártása view-hoz ha kell
   protected function viewController( $v, $c ) {
      if ( ! $ret = Tools::g( $v->data, $c )) {
         $f = $this->ffi;
         switch ($c) {
            case self::KEYCTRL:
               $ret = $f->gtk_event_controller_key_new();
               $f->gtk_widget_add_controller( $this->realImpl( $v ), $ret );
            break;
            default: throw new EVygu("Unknown controller: $c");
         }
         $v->data[ $c ] = $ret;
      }
      return $ret;
   }

   // vygu billentyű
   protected function key( $kVal, $kCode, $state, $data ) {
      $f = $this->ffi;
      $ret = new Key();
      $db = $f->cast("int *",$data);
      $ret->event = $db[0] ? Key::PRESS : Key::RELEASE;
      $ret->scan = $kCode;
      if ( ! $ret->unicode = $f->gdk_keyval_to_unicode( $kVal ))
         $ret->special = Tools::gg( self::MKEY, $kVal );
      $ret->modif = $this->keyState( $state );
      return $ret;
   }

   // módosítók
   protected function keyState( $state ) {
      $ret = 0;
      if ($state & 1)
         $ret |= Key::MSHIFT;
      if ($state & 4)
         $ret |= Key::MCTRL;
      if ($state & 8)
         $ret |= Key::MALT;
      return $ret;
   }

   protected function createMap( $name ) {
      switch ($name) {
         case View::CURSOR: return array_flip( self::MCURSOR );
         default: return parent::createMap($name);
      }
   }

   // kurzor konverzió vissza
   protected function fromCursor( $c ) {
      if ( $ret = Tools::g( $this->map( View::CURSOR ), $c ))
         return $ret;
      throw new EVygu("Unknown cursor: $c");
   }

   // callback hívása, a kivétel eltárolása
   protected function callCallback($cb, array $args = [] ) {
      try {
         return call_user_func_array( $cb, $args );
      } catch (\Throwable $e) {
         $this->err = $e;
      }
   }

   // speciális view koordináta
   protected function viewCoordSpec( $v, $c, $x, & $tmp ) {
      $f = $this->ffi;
      $g = Tools::GET === $x;
      switch ($c) {
         case Layout::ASCENT:
            if ($g) return $this->viewCoord($v,Layout::HEIGHT,$x,$tmp);
         break;
         case Layout::DEFWIDTH:
            $mx = $this->temp( $v, self::MESX, $tmp );
            if ($g) return (int)$mx[1];
         break;
         case Layout::DEFHEIGHT:
            $my = $this->temp( $v, self::MESY, $tmp );
            if ($g) return (int)$my[1];
         break;
         case Layout::DESCENT:
            if ($g) return 0;
         break;
      }
      return parent::viewCoord($v, $c, $x, $tmp);
   }

   // temp adat $v-ez
   protected function temp(View $v, $kind, & $tmp ) {
      $f = $this->ffi;
      if (true === $tmp)
         $tmp = [self::LAST=>true];
      if ( ! $ret = Tools::g( $tmp, $kind )) {
         switch ($kind) {
            case self::RECT:
               $f->gtk_widget_get_allocation( $v->impl, \FFI::addr($this->allo));
               $ret = $this->allo;
            break;
            case self::MESX:
               $ret = $this->ints;
               $f->gtk_widget_measure( $v->impl, 0, -1, \FFI::addr($ret[0]),
                  \FFI::addr($ret[1]), \FFI::addr($ret[2]), \FFI::addr($ret[3]));
            break;
            case self::MESY:
               $ret = $this->ints;
               $f->gtk_widget_measure( $v->impl, 1, -1, \FFI::addr($ret[0]),
                  \FFI::addr($ret[1]), \FFI::addr($ret[2]), \FFI::addr($ret[3]));
            break;
            default: throw new EVygu("Unknown temp kind: $kind");
         }
         $tmp[$kind] = $ret;
      }
      return $ret;
   }

   protected function viewCoordLast(View $v, $tmp) {
      if ($r = Tools::g($tmp,self::RECT)) {
         $this->ffi->gtk_widget_size_allocate( $v->impl, \FFI::addr($r), -1 );
      }
   }

   // első gdk monitor
   protected function monitor($s) {
      $f = $this->ffi;
      $mts = $f->gdk_display_get_monitors($s->data);
      return $f->g_list_model_get_item( $mts, 0 );
   }

}
