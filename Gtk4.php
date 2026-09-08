<?php

namespace vygu;

/// Gtk4 rendszer
class Gtk4 extends Vygu {

   const
      GTK4 = "gtk4";

   const
      KEYCTRL = "keyCtrl",
      LAYOUTMGR = "layoutMgr",
      HANDLERID = "handlerID",
      TEXTBUFFER ="textbuffer",
      TEXTITER = "textiter",
      TEXTITER2 = "textiter2",
      TEXTVIEW = "textview",
      WINDOWBOX = "windowbox";

   /// temp részek
   const
      LAST = "last",
      MESX = "mesx",
      MESY = "mesy",
      RECT = "rect";

   const
      CURSORS = [
         "default"=>Cursor::DEFAULT,
         "wait"=>Cursor::WAIT
      ],
      KEYS = [
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
         0xffff => Key::DEL,
         0xff7f => Key::NUM,
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
         0xffeb => Key::META
      ];

   /// ffi kapcsolat
   protected $ffi;
   /// kivétel eseménykezelőben
   protected $err;
   /// hashek
   protected $maps;
   /// alloc lekéréshez
   protected $rect;
   /// intek visszaadásához
   protected $ints;

   function __construct($args) {
      parent::__construct($args);
      $this->maps = [];
      $h = Tools::loadFile( __DIR__."/gtk4.h" );
      $f = $this->ffi = \FFI::cdef( $h, "libgtk-4.so.1" );
      $f->gtk_init();
      $this->rect = $f->new("GtkAllocation");
      $this->ints = $f->new("int[4]");
   }

   function runStep( $wait ) {
      if ( $ret = $this->ffi->g_main_context_iteration(null,$wait))
         $this->checkErr();
      return $ret;
   }

   function handlerCreate(View $v, $e, callable $cb) {
      $ret = new Handler();
      $ret->view = $v;
      $ret->data = [];
      switch ($e) {
         case View::KEYPRESS: $this->handlerCreateKey( $ret, $cb ); break;
         case Action::FIRE: case Window::CLOSING:
            $this->handlerCreateSignal( $ret, $cb );
         break;
         case Group::LAYOUT: $this->handlerCreateLayout( $ret, $cb ); break;
         default: parent::handlerCreate($e,$cb);
      }
      return $ret;
   }

   function viewCreate( View $v ) {
      $f = $this->ffi;
      switch ($k = $v->kind()) {
         case Button::BUTTON: $ret = $f->gtk_button_new(); break;
         case Group::GROUP: $ret = $f->gtk_fixed_new(); break;
         case Label::LABEL: $ret = $f->gtk_label_new(null); break;
         case Memo::MEMO: case Rich::RICH:
            $ret = $this->createMemo($v);
         break;
         case Window::WINDOW:
            $ret = $f->gtk_window_new();
            $b = $this->check( $f->gtk_fixed_new(), "Could not create gtk4 fixed" );
            $v->data = [
               self::WINDOWBOX => $b
            ];
            $f->gtk_window_set_child( $ret, $b );
         break;
         default: return parent::viewCreate($v);
      }
      $this->check( $ret, "Could not create gtk4 $k" );
      $v->impl = $this->sink( $ret );
   }

   function menuCreate( Menu $m ) {
      $m->impl = $this->ffi->g_menu_new();
   }

   function styleCreate( Style $s ) {
   }

   /// view style jellemzője
   function viewStyle( View $v, $x ) {
      switch ( $k = $v->kind() ) {
         case Rich::RICH: return $this->richStyle( $v, $x );
         default: throw new EVygu("Cannot access style of $k");
      }
   }

   /// View kurzor jellemzője
   function viewCursor( View $v, $x ) {
      $f = $this->ffi;
      if ( Tools::GET === $x ) {
         $c = $f->gtk_widget_get_cursor( $v->impl );
         if (\FFI::isNull($c))
            return null;
         $n = $f->gdk_cursor_get_name( $c );
         return $this->cursor( $n );
      } else {
         $c = $this->fromCursor( $x );
         $f->gtk_widget_set_cursor_from_name( $v->impl, $c );
      }
   }

   /// richedit stílusa
   function richStyle( Rich $r, $s ) {
      if ( Tools::GET === $s )
         return $this->richStyleRead( $r );
   }

   /// view felszámolás
   function viewDestroy( View $v ) {
      $this->ffi->g_object_unref( $v->impl );
   }


   function viewHandler(View $v, $e, ?Handler $o, ?Handler $h) {
      switch ($e) {
         case Window::CLOSING: case Action::FIRE:
            return $this->viewHandlerSignal( $v, $e, $o, $h );
         case View::KEYPRESS:
            return $this->viewHandlerKey( $v, $e, $o, $h );
         case Group::LAYOUT:
            return $this->viewHandlerLayout( $v, $e, $o, $h );
         default: 
            return parent::viewHandler( $v, $e, $o, $h );
      }
   }

   /// insert művelet
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

   function viewProperty( View $v, $p, $x ) {
      switch ($p) {
         case View::CURSOR: return $this->viewCursor($v,$x);
         case View::STYLE: return $this->viewStyle($v,$x);
         case View::TEXT: return $this->viewText($v,$x);
      }
      $f = $this->ffi;
      $im = $v->impl;
      if (Tools::GET === $x) {
         switch ($p) {
            case View::VISIBLE: return $f->gtk_widget_get_visible($im);
            case Window::TITLE: return $f->gtk_window_get_title($im);
         }
      } else {
         switch ($p) {
            case View::VISIBLE: return $f->gtk_widget_set_visible($im,$x);
            case Window::TITLE: return $f->gtk_window_set_title($im,$x);
         }
      }
      return parent::viewProperty($v,$p,$x);
   }

   /// view koordináta
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

   function viewDestroy( View $v ) {
      if ( $v instanceof Group )
         $v->clear();
   }


   /// a valódi widget impl
   protected function realImpl( View $v ) {
      switch ($v->kind()) {
         case Memo::MEMO:
         case Rich::RICH:
            return $v->data[ self::TEXTVIEW ];
         default: return $v->impl;
      }
   }

   /// a konténer impl
   protected function contImpl( Group $g ) {
      switch ($g->kind()) {
         case Window::WINDOW:
            return $g->data[ self::WINDOWBOX ];
         default: return $g->impl;
      }
   }

   /// venet-hez tartozó signal neve
   protected function eventSignal($e) {
      switch ($e) {
         case Action::FIRE: return "clicked";
         case View::KEYPRESS: return "key-pressed";
         case Window::CLOSING: return "close-request";
         default: throw new EVygu("Unknown event: $e");
      }
   }

   /// a globális $err ellenőrzése, és dobása
   protected function checkErr() {
      if ( $e = $this->err ) {
         $this->err = null;
         throw $e;
      }
   }

   /// valamilyen view text-je
   protected function viewText($v,$x) {
      $im = $v->impl;
      $f = $this->ffi;
      $g = Tools::GET === $x;
      switch ($v->kind()) {
         case Label::LABEL:
            if ($g)
               return $f->gtk_label_get_text($im);
               else return $f->gtk_label_set_text($im,"$x");
         break;
         case Button::BUTTON:
            if ($g)
               return $f->gtk_button_get_label($im);
               else return $f->gtk_button_set_label($im,"$x");
         break;
         case Memo::MEMO:
         case Rich::RICH:
            $b = $v->data[self::TEXTBUFFER];
            if ($g) {
               $i = $v->data[ self::TEXTITER ];
               $i2 = $v->data[ self::TEXTITER2 ];
               $f->gtk_text_buffer_get_bounds( $b, $i, $i2 );
               return \FFI::string( $f->gtk_text_buffer_get_text( $b, $i, $i2, false ) );
            } else {
               return $f->gtk_text_buffer_set_text( $b, "$x", -1 );
            }
         break;
               
      }
      return parent::viewProperty($v,View::TEXT,$x);
   }

   /// richedit készítése és összerakása
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

   /// bufferen belüli mozgás
   protected function bufferMove( $b, $at, $i ) {
      $f = $this->ffi;
      if ( false === $at )
         $f->gtk_text_buffer_get_start_iter( $b, $i );
      else if ( true === $at )
         $f->gtk_text_buffer_get_end_iter( $b, $i );
      else
         $f->gtk_text_buffer_get_iter_at_offset( $b, $i, $at );
   }

   /// floating reference sink
   protected function sink( $x ) {
      $this->ffi->g_object_ref_sink( $x );
      return $x;
   }

   /// signal kezelő
   protected function handlerCreateSignal( Handler $h, $cb ) {
      $f = $this->ffi;
      $c = $f->new( "struct sSignalCallback" );
      $c->c = function( $impl, $udata ) use ($cb) {
         return $this->callCallback( $cb );
      };
      $h->impl = $c->c;
   }      

   /// gombynomás kezelő
   protected function handlerCreateKey( Handler $h, $cb ) {
      $f = $this->ffi;
      $c = $f->new( "struct sKeyPressCallback" );
      $c->c = function( $ctrl, $kVal, $kCode, $state, $data ) use ($cb) {
         return $this->callCallback(  $cb, [$this->key( $kVal, $kCode, $state )] );
      };
      $h->impl = $c->c;
   }

   /// layout kezelő
   protected function handlerCreateLayout( Handler $h, $cb ) {
      $f = $this->ffi;
      $c = $f->new( "struct sLayoutCallback" );
      $c->measure = function( $widget, $ori, $fors, $min, $nat, 
         $min_base, $nat_base ) 
      {
         $min[0] = 0;
         $nat[0] = 0;
         $min_base[0] = -1;
         $nat_base[0] = -1;
      };
      $c->allocate = function( $widget, $width, $height, $base ) use ($h,$cb) {
         return $this->callCallback( $cb, [$h->view] );
      };
      $h->impl = $c;
   }

   /// signal kezelő beállítása
   protected function viewHandlerSignal( $v, $e, $o, $h ) {
      $f = $this->ffi;
      if ($o)
         $f->g_signal_handler_disconnect( $v->impl, $o->data[ self::HANDLERID ] );
      if ($h) {
         $h->data[ self::HANDLERID ] = 
            $f->g_signal_connect_data( $v->impl, $this->eventSignal($e), 
               $h->impl, null, null, 0 );
      }
   }

   /// key kezelő beállítása
   protected function viewHandlerKey( $v, $e, $o, $h ) {
      $f = $this->ffi;
      $c = $this->viewController( $v, self::KEYCTRL );
      if ($o)
         $f->g_signal_handler_disconnect( $c, $o->data[ self::HANDLEID ] );
      if ($h)
         $h->data[ self::HANDLERID ] = 
            $f->g_signal_connect_data( $c, $this->eventSignal($e), 
               $h->impl, null, null, 0 );
   }

   /// layout kezelő beállítása
   protected function viewHandlerLayout( $v, $e, $o, $h ) {
      $f = $this->ffi;
      if ($h)
         $cl = $f->gtk_custom_layout_new( null, $h->impl->measure, 
            $h->impl->allocate );
         else $cl = null;
      $f->gtk_widget_set_layout_manager( $this->contImpl($v), $cl );
   }

   /// cotroller gyártása view-hoz ha kell
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

   /// vygu billentyű 
   protected function key( $kVal, $kCode, $state ) {
      $ret = new Key();
      $ret->scan = $kCode;
      if ( ! $ret->unicode = $this->ffi->gdk_keyval_to_unicode( $kVal ))
         $ret->special = $this->keySpecial( $kVal );
      $ret->modif = $this->keyState( $state );
      return $ret;
   }
          
   /// vygu speciális billentyű
   protected function keySpecial( $kVal ) {
      if ($ret = Tools::g( self::KEYS, $kVal ))
         return $ret;
      throw new EVygu("Unknown special key: 0x".dechex($kVal));
   }

   /// módosítók
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

   //// vissza hash
   protected function map($name) {
      if ( ! $ret = Tools::g( $this->maps, $name )) {
         switch ($name) {
            case View::CURSOR: $ret = array_flip( self::CURSORS ); break;
            default: throw new EVygu("Unknown map: $name");
         }
         $this->maps[$name] = $ret;
      }
      return $ret;
   }

   /// kurzor konverzió oda
   protected function cursor( $c ) {
      if ( $ret = Tools::g( self::CURSORS, $c ))
         return $ret;
      throw new EVygu("Unknown cursor: $c");
   }

   /// kurzor konverzió vissza
   protected function fromCursor( $c ) {
      if ( $ret = Tools::g( $this->map( View::CURSOR ), $c ))
         return $ret;
      throw new EVygu("Unknown cursor: $c");
   }

   /// callback hívása, a kivétel eltárolása
   protected function callCallback($cb, array $args = [] ) {
      try {
         return call_user_func_array( $cb, $args );
      } catch (\Throwable $e) {
         $this->err = $e;
      }
   }

   /// speciális view koordináta
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

   /// temp adat $v-ez
   protected function temp(View $v, $kind, & $tmp ) {
      $f = $this->ffi;
      if (true === $tmp)
         $tmp = [self::LAST=>true];
      if ( ! $ret = Tools::g( $tmp, $kind )) {
         switch ($kind) {
            case self::RECT:
               $f->gtk_widget_get_allocation( $v->impl, \FFI::addr($this->rect));
               $ret = $this->rect;
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


}
