<?php

namespace vygu;

/// Windows API engine
class WinApi extends Vygu {

   const
      /// Kind constant
      WINAPI = "winapi";

   // mapek
   const
      MALIGN = [
         0 => Layout::LEFT,
         1 => Layout::CENTERX,
         2 => Layout::RIGHT
      ],
      MCURSORS = [
         0x7f00=>Cursor::DEFAULT,
         0x7f02=>Cursor::WAIT
      ],
      MSPECS = [
         WinApiWrap::VK_CAPITAL => Key::CAPS,
         WinApiWrap::VK_ESCAPE => Key::ESC,
         WinApiWrap::VK_PRIOR => Key::PGUP,
         WinApiWrap::VK_NEXT => Key::PGDN,
         WinApiWrap::VK_END => Key::END,
         WinApiWrap::VK_HOME => Key::HOME,
         WinApiWrap::VK_LEFT => Key::LEFT,
         WinApiWrap::VK_UP => Key::UP,
         WinApiWrap::VK_RIGHT => Key::RIGHT,
         WinApiWrap::VK_DOWN => Key::DOWN,
         WinApiWrap::VK_INSERT => Key::INS,
         WinApiWrap::VK_DELETE => Key::DEL,
         WinApiWrap::VK_F1 => Key::F1,
         WinApiWrap::VK_F2 => Key::F2,
         WinApiWrap::VK_F3 => Key::F3,
         WinApiWrap::VK_F4 => Key::F4,
         WinApiWrap::VK_F5 => Key::F5,
         WinApiWrap::VK_F6 => Key::F6,
         WinApiWrap::VK_F7 => Key::F7,
         WinApiWrap::VK_F8 => Key::F8,
         WinApiWrap::VK_F9 => Key::F9,
         WinApiWrap::VK_F10 => Key::F10,
         WinApiWrap::VK_F11 => Key::F11,
         WinApiWrap::VK_F12 => Key::F12,
         WinApiWrap::VK_LWIN => Key::META,
         WinApiWrap::VK_LSHIFT => Key::LSHIFT,
         WinApiWrap::VK_RSHIFT => Key::RSHIFT,
         WinApiWrap::VK_LCONTROL => Key::LCTRL,
         WinApiWrap::VK_RCONTROL => Key::RCTRL,
         WinApiWrap::VK_LMENU => Key::ALT,
         WinApiWrap::VK_RMENU => Key::ALTGR
      ];

   /// wrapper
   protected $wrap;
   // hwnd -> View
   protected $wnds;
   // id -> Action
   protected $acs;
   // kivétel callback-ben
   protected $err;
   // action sorszám
   protected $nact;

   function __construct($args) {
	   parent::__construct($args);
      $w = $this->wrap = new WinApiWrap();
      $this->wnds = [];
      $this->acs = [];
      $w->subPrc->f = function($hwnd,$msg,$wparam,$lparam,$sub,$ref) {
         return $this->subProc($hwnd,$msg,$wparam,$lparam);
      };
      $w->wndPrc->f = function($hwnd,$msg,$wparam,$lparam) {
         return $this->wndProc($hwnd,$msg,$wparam,$lparam);
      };
   }

   function elemCreate( Elem $v ) {
      $w = $this->wrap;
      $ret = null;
      switch ($h = $v->kind()) {
         case Action::ACTION:
            $v->data  = [ Elem::ID => ++$this->nact ];
            $this->acs[ $this->nact ] = \WeakReference::create($v);
            return;
         break;
         case Button::BUTTON: $ret = $w->CreateWindowButton(); break;
         case Group::GROUP:
         case Label::LABEL:
            $ret = $w->CreateWindowStatic();
         break;
         case Memo::MEMO: $ret = $w->CreateWindowEdit(); break;
         case Menu::MENU:
            $ret = $w->CreatePopupMenu();
            $v->data = [];
         break;
         case Window::WINDOW: $ret = $w->CreateWindowWindow(); break;
         default: return parent::elemCreate( $v );
      }
      $v->impl = $ret;
      $this->wnds[ $w->ptri( $ret ) ] = \WeakReference::create($v);
      if ( ! in_array( $h, [Window::WINDOW, Menu::MENU, Action::ACTION] ))
         $w->SetWindowSublcass( $ret );
   }

   // wndProc futtatás
   function wndProc( $hwnd, $msg, $wparam, $lparam ) {
      if ( array_key_exists( $msg, $this->nwms )) {
         try {
            if ($this->handleMsg( $hwnd, $msg, $wparam, $lparam ))
               return 0;
         } catch (Throwable $e) {
            $this->err = $e;
         }
      }
      return $this->wrap->DefWindowProc($hwnd,$msg,$wparam,$lparam);
   }

   // subProc futtatás
   function subProc( $hwnd, $msg, $wparam, $lparam ) {
      if ( array_key_exists( $msg, $this->nwms )) {
         try {
            if ($this->handleMsg($hwnd,$msg,$wparam,$lparam))
               return 0;
         } catch (Throwable $e) {
            $this->err = $e;
         }
      }
      return $this->wrap->DefSubclassProc($hwnd,$msg,$wparam,$lparam);
   }

   //  egy view message kezelése
   function handleMsg($hwnd, $msg, $wparam, $lparam) {
      $w = $this->wrap;
      if ( ! $v = $this->viewByHwnd( $hwnd ) )
         return;
      switch ($msg) {
         case WinApiWrap::WM_CLOSE:
            if ( ! $v->handle( Window::CLOSING ))
               return true;
         case WinApiWrap::WM_SIZE:
            if (! $v instanceof Group)
               return;
            $v->layout();
            return true;
         case WinApiWrap::WM_COMMAND:
            if ( ! $lparam ) {
               $id = $wparam & 0xffff;
               if ($a = $this->actById( $id )) {
                  $a->fire();
                  return true;
               }
            }
            if ( ! $s = $this->viewByHwnd( $w->cast("HWND",$lparam)))
               return;
            switch ( $e = ($wparam >> 16) & 0xffff ) {
               case WinApiWrap::BN_CLICKED:
                  $s->handle( Elem::FIRE );
                  return  true;
            }
         break;
         case WinApiWrap::WM_KEYDOWN:
         case WinApiWrap::WM_KEYUP:
            $k = $this->key( $wparam, $lparam );
            if ($v->handle( View::KEY, [$k] ))
               return true;
         break;
      }
   }

   function elemProperty( Elem $v, $p, $x ) {
      switch ($p) {
         case Action::SHORTCUT: 
         case Elem::NAME: 
            return $this->dataProperty($v,$p,$x);
         case View::POSITION: return $this->viewPosition($v,$x);
         case View::SELLENGTH: return $this->viewSelLength($v,$x);
         case View::VISIBLE: return $this->viewVisible($v,$x);
         case View::TEXT: case Window::TITLE:
            return $this->viewText($v,$x);
         case View::ALIGN: return $this->viewAlign($v,$x);
         case Window::MENU: return $this->windowMenu($v,$x);
         default:
            return parent::elemProperty($v,$p,$x);
      }
   }

   // view text olvasása vagy írása
   function viewText($v,$x) {
      $w = $this->wrap;
      if (Tools::GET == $x)
         return $w->getText( $v->impl );
         else $w->setText( $v->impl, $x );
      return $v;
   }

   // view elrejtése, vagy megjelenítése
   function viewVisible($v,$x) {
      $w = $this->wrap;
      if (Tools::GET === $x)
         return $w->getVisible( $v->impl );
         else $w->setVisible( $v->impl, $x );
      return $v;
   }

   // view position-je
   function viewPosition($v,$x) {
      $w = $this->wrap;
      $g = Tools::GET === $x;
      switch ($v->kind()) {
         case Edit::EDIT:
         case Memo::MEMO:
         case Rich::RICH:
            if ($g)
               return $w->getSel( $v->impl )[0];
               else $w->setSel( $v->impl, $x, null );
         break;
         default: 
            return parent::elemProperty($v,View::POSITION,$x);
      }
      return $v;
   }

   // view selLength-je
   function viewSelLength($v,$x) {
      $w = $this->wrap;
      $g = Tools::GET === $x;
      switch ($v->kind()) {
         case Edit::EDIT:
         case Memo::MEMO:
         case Rich::RICH:
            $s = $w->getSel( $v->impl );  
            if ($g)
               return $s[1]-$s[0];
            $w->setSel( $v->impl, $s[0], $s[0]+$x );
         default: 
            return parent::elemProperty($v,View::SELLENGTH,$x);
      }
      return $this;
   }

   // ablak-hoz tartozó menü
   function windowMenu($v,$x) {
      $old = Tools::g( $v->data, [Window::MENU] );
      if ( Tools::GET === $x )
         return $old;
      if ( $x === $old )
         return $v;
      $u = $this->ffu;
      $m = $u->CreateMenu();
      foreach ($x->items as $i) {
         switch ($k = $i->kind()) {
            case Action::ACTION:
               $ret = $w->appendMenuItem( $m, $i->data[ Elem::ID ], 
                  $i->name() );
            break;
            case Menu::MENU:
               $ret = $w->appendMenuSub( $m, $i->impl, $i->name() );
            break;
            default: throw new EVygu("Could not append menu: $k");
         }
      }
      if ( $old ) {
         $w->setMenu( $v->impl, null );
         $w->destroyMenu( $old->data[ self::HMENU ] );
      }
      $w->setMenu( $v->impl, $m );
      $v->data[ self::HMENU ] = $m;
      $v->data[ Window::MENU ] = $x;
      return $v;
   }

   function menuAdd(Menu $m, $x) {
      $w = $this->wrap;
      switch ($k = $x->kind()) {
         case Menu::MENU:
            return $w->appendMenuSub( $m->impl, $x->impl, $x->name() );
         break;
         case Action::ACTION:
            return $w->appendMenuItem( $m->impl, $x->data[ Elem::ID ], 
               $x->name() );
         break;
      }
      return parent::menuAdd($m,$x);
   }

   // view igazítás
   function viewAlign($v,$x) {
      $s = $this->viewStyleWord($v);
      if (Tools::GET === $x) {
         return Tools::g( self::MALIGN, Tools::bits($s,0,2));
      } else {
         $s = Tools::withBits( $s, 0, 2,
            Tools::g($this->map(View::ALIGN),$x));
         $this->viewStyleWord($v,$s);
         return $v;
      }
   }

   // stílus szó írás vagy olvasás
   function viewStyleWord($v,$x=Tools::GET) {
      $w = $this->wrap;
      if (Tools::GET === $x)
         return $w->getStyle( $v->impl );
      $w->setStyle( $v->impl, $x );
      $v->invalidate();
   }

   function elemHandler(Elem $v, $e, ?Handler $o, ?Handler $h) {
   }

   function viewInvalidate(View $v) {
      $this->wrap->invalidate( $v->impl );
   }

   function viewParent( View $v, ?Group $g ) {
      $w = $this->wrap;
      if ($g)
         $u->setParent($v->impl, $g->impl);
         else $u->setParent($v->impl, null);
   }

   function runStep( $wait ) {
      $m = $this->wrap->messageStep( $wait );
      if ( WinApiWrap::WM_QUIT == $m->message ) {
         $this->finish();
         return true;
      }
      $this->checkErr();
      return true;
   }

   // view hwnd alapján
   function viewByHwnd( $hwnd ) {
      $i = $this->wrap->ptri( $hwnd );
      if ( $ret = Tools::g( $this->wnds, $i ) )
         return $ret->get();
         else return null;
   }
   
   // Action id alapján
   function actByID( $id ) {
      if ( $ret = Tools::g( $this->acs, $id ))
         return $ret->get();
      return null;
   }

   function screenCoord( Screen $s, $c ) {
      switch ($c) {
         case Layout::CONTHEIGHT:
         case Layout::CONTWIDTH:
            $r = $this->wrap->workArea();
         break;
      }
      switch ($c) {
         case Layout::CONTHEIGHT: return $r->bottom - $r->top;
         case Layout::CONTWIDTH: return $r->right - $r->left;
         default:
            return parent::screenCoord($c);
      }
   }

   function viewCoord( View $v, $c, $x, & $tmp ) {
      $g = Tools::GET === $x;
      // amihez nem kell rect
      switch ($c) {
         case Layout::ASCENT:
         case Layout::DEFWIDTH:
         case Layout::DEFHEIGHT:
         case Layout::DESCENT:
         case Layout::CONTWIDTH:
         case Layout::CONTHEIGHT:
            return $this->viewCoordSpec( $v, $c, $x, $tmp );
      }
      $r = $this->temp( $v, self::TRECT, $tmp );
      if ($g) {
         switch ($c) {
            case Layout::BOTTOM: return $r->bottom;
            case Layout::CENTERX: return ($r->left + $r->right) >> 1;
            case Layout::CENTERY: return ($r->top + $r->bottom) >> 1;
            case Layout::HEIGHT: return $r->bottom - $r->top;
            case Layout::LEFT: return $r->left;
            case Layout::RIGHT: return $r->right;
            case Layout::TOP: return $r->top;
            case Layout::WIDTH: return $r->right - $r->left;
            default: return parent::viewCoord($v,$c,$x,$tmp);
         }
      } else {
         switch ($c) {
            case Layout::BOTTOM:
               $r->top += $x - $r->bottom;
               $r->bottom = $x;
            break;
            case Layout::CENTERX:
               $w = $r->right - $r->left;
               $r->left = $x - ($w >> 1);
               $r->right = $r->left + $w;
            break;
            case Layout::CENTERY:
               $h = $r->bottom - $r->top;
               $r->top = $x - ($h >> 1);
               $r->bottom = $r->top + $h;
            break;
            case Layout::HEIGHT: $r->bottom = $r->top + $x; break;
            case Layout::LEFT:
               $r->right += $x - $r->left;
               $r->left = $x;
            break;
            case Layout::RIGHT:
               $r->left += $x-$r->right;
               $r->right = $x;
            break;
            case Layout::TOP:
               $r->bottom += $x - $r->top;
               $r->top = $x;
            break;
            case Layout::WIDTH: $r->right = $r->left + $x; break;
            default: return parent::viewCoord($v,$c,$x,$tmp);
         }
         if ( Tools::g($tmp,self::TLAST) )
            $this->viewCoordLast( $v, $tmp );
      }
   }

   // speciális koordináták
   function viewCoordSpec( $v, $c, $x, &$tmp ) {
      switch ($c) {
         case Layout::CONTWIDTH:
            $r = $this->temp( $v, self::TCONT, $tmp );
            return $r->right - $r->left;
         break;
         case Layout::CONTHEIGHT:
            $r = $this->temp( $v, self::TCONT, $tmp );
            return $r->bottom - $r->top;
         break;
         case Layout::DEFWIDTH:
            if ( Tools::g( $v->handlers, View::MEASURE ))
               return $v->handle( View::MEASURE, [$v, $c] );
            $r = $this->temp( $v, self::TDEF, $tmp );
            return $r[0];
         break;
         case Layout::DEFHEIGHT:
            if ( Tools::g( $v->handlers, View::MEASURE ))
               return $v->handle( View::MEASURE, [$v, $c] );
            $r = $this->temp( $v, self::TDEF, $tmp );
            return $r[1];
         break;
         default: throw new EVygu("Unknown spec coord: $c");
      }
   }

   function viewFocus(View $v) {
      $this->wrap->setFocus( $v->impl );
   }

   function dialog( $kind, array $args ) {
      $w = $this->wrap;
      switch ($kind) {
         case Dialog::OPEN:
         case Dialog::SAVE:
            return $w->getOpenFileName($kind);
         break;
         default: 
            return parent::dialog( $kind, $args );
      }
   }

   // Key eseményből
   function key( $wparam, $lparam ) {
      $w = $this->wrap;
      $ret = new Key();
      $w->getKeyState();
      $ret->event = Tools::bits($lparam,31,1)
         ? Key::RELEASE : Key::PRESS;
      $ret->modif = $w->keyModif();
      $ret->scan = Tools::bits($lparam,16,8);
      $ret->unicode = $w->keyUnicode( $wparam, $ret->scan );
      $ret->special = Tools::g( self::MSPECS, intval( $wparam ) );
      return $ret;
   }

   /// a szöveg egy része
   function textPart( Edit $v, $at, $len, $x = Tools::GET ) {
      $w = $this->wrap;
      $g = Tools::GET == $x;
      switch ($v->kind()) {
         case Memo::MEMO:
            if ($g) 
               return $w->getTextPart( $v, $at, $at+len );
               else $w->setTextPart( $v, $at, $at+$len, $x );
         break;
         default: return parent::textPart( $v, $at, $len, $x );
      }
      return $v;
   }

   function clipboardValue( Clipboard $c, $x = Tools::GET ) {
      $w = $this->wrap;
      if ( Tools::GET === $x )
         return $w->getClipboard();
         else $w->setClipboard($x);
   }

   // temp adat $v-ez
   protected function temp(View $v, $kind, & $tmp ) {
      $w = $this->wrap;
      if (true === $tmp)
         $tmp = [self::TLAST=>true];
      if ( ! $ret = Tools::g( $tmp, $kind )) {
         switch ($kind) {
            case self::TRECT:
               $ret = $w->getWindowRect( $v->impl );
            break;
            case self::TCONT:
               $ret = $w->getClientRect( $v->impl );
            break;
            case self::TDEF:
               $ret = $this->viewDefSize($v);
            break;
            default: throw new EVygu("Unknown temp kind: $kind");
         }
         $tmp[$kind] = $ret;
      }
      return $ret;
   }

   /// view default mérete
   protected function viewDefSize( View $v ) {
      $w = $this->wrap;
      switch ($k = $v->kind()) {
         case Label::LABEL:
            $r = $w->calcText( $v->text() );
            return [$r->right-$r->left,$r->bottom-$r->top];
         break;
         case Button::BUTTON:
            $s = $w->getIdealSize( $v->impl );
            return [$s->cx, $s->cy];
         break;
         default: 
      }
      throw new EVygu("Cannot get default size: $k");
   }

   /// a globális $err ellenőrzése, és dobása
   protected function checkErr() {
      if ( $e = $this->err ) {
         $this->err = null;
         throw $e;
      }
   }

   protected function viewCoordLast(View $v, $tmp) {
      if ($r = Tools::g($tmp,self::TRECT)) {
         $this->wrap->moveWindow( $v->impl, $r );
         $this->viewInvalidate($v);
      }
   }

   // kurzor konverzió vissza
   protected function fromCursor( $c ) {
      if ( ! $ret = Tools::g( $this->crsrs, $c )) {
         $u = $this->ffu;
         if ( ! $id = Tools::g( $this->map( View::CURSOR ), $c ))
            throw new EVygu("Unknown cursor: $c");
         $ret = $this->wrap->loadCursor( $id );
         $this->crsrs[ $c ] = $ret;
      }
      return $ret;
   }

   protected function createMap( $name ) {
      switch ($name) {
         case View::ALIGN: return array_flip( self::MALIGN );
         case View::CURSOR: return array_flip( self::MCURSORS );
         default: return parent::createMap($name);
      }
   }


}
