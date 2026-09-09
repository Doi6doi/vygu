<?php

namespace vygu;

/// Windows API engine
class WinApi extends Vygu {
	
   const
      WINAPI = "winapi";
      
   const
      CBUTTON = "BUTTON",
      CVYGU = "Vygu",
      CSTATIC = "STATIC",
      ERRLEN = 1024;

   /// temp adatok
   const
      TCONT = "tCont",
      TDEF  = "tDef",
      TLAST = "tLast",
      TRECT = "tRect";

   /// mapek
   const
     CURSORS = [
         0x7f00=>Cursor::DEFAULT,
         0x7f02=>Cursor::WAIT
     ];

   const
      BN_CLICKED = 0,
   
      COLOR_WINDOW = 5,
      
      DT_CALCRECT = 0x400,
     
      GWL_STYLE = -16,

      VK_SHIFT   = 0x10,
      VK_CONTROL = 0x11,
      VK_MENU    = 0x12,
      VK_PAUSE   = 0x13,
      VK_CAPITAL = 0x14,
      VK_ESCAPE  = 0x1b,
      VK_PRIOR   = 0x21,
      VK_NEXT    = 0x22,
      VK_END     = 0x23,
      VK_HOME    = 0x24,
      VK_LEFT    = 0x25,
      VK_UP      = 0x26,
      VK_RIGHT   = 0x27,
      VK_DOWN    = 0x28,
      VK_INSERT  = 0x2d,
      VK_DELETE  = 0x2e,
      VK_F1      = 0x70,
      VK_F2      = 0x71,
      VK_F3      = 0x72,
      VK_F4      = 0x73,
      VK_F5      = 0x74,
      VK_F6      = 0x75,
      VK_F7      = 0x76,
      VK_F8      = 0x77,
      VK_F9      = 0x78,
      VK_F10     = 0x79,
      VK_F11     = 0x7a,
      VK_F12     = 0x7b,
      VK_LWIN    = 0x5b,
      VK_LSHIFT  = 0xa0,
      VK_RSHIFT  = 0xa1,
      VK_LCONTROL = 0xa2,
      VK_RCONTROL = 0xa3,
      VK_LMENU   = 0xa4,  
      VK_RMENU   = 0xa5,  

      VK_SPECS = [ 
         self::VK_CAPITAL => Key::CAPS,
         self::VK_ESCAPE => Key::ESC,
         self::VK_PRIOR => Key::PGUP,
         self::VK_NEXT => Key::PGDN,
         self::VK_END => Key::END,
         self::VK_HOME => Key::HOME,
         self::VK_LEFT => Key::LEFT,
         self::VK_UP => Key::UP,
         self::VK_RIGHT => Key::RIGHT,
         self::VK_DOWN => Key::DOWN,
         self::VK_INSERT => Key::INS,
         self::VK_DELETE => Key::DEL,
         self::VK_F1 => Key::F1,
         self::VK_F2 => Key::F2,
         self::VK_F3 => Key::F3,
         self::VK_F4 => Key::F4,
         self::VK_F5 => Key::F5,
         self::VK_F6 => Key::F6,
         self::VK_F7 => Key::F7,
         self::VK_F8 => Key::F8,
         self::VK_F9 => Key::F9,
         self::VK_F10 => Key::F10,
         self::VK_F11 => Key::F11,
         self::VK_F12 => Key::F12,
         self::VK_LWIN => Key::META,
         self::VK_LSHIFT => Key::LSHIFT,
         self::VK_RSHIFT => Key::RSHIFT,
         self::VK_LCONTROL => Key::LCTRL,
         self::VK_RCONTROL => Key::RCTRL,
         self::VK_LMENU => Key::ALT,
         self::VK_RMENU => Key::ALTGR
      ],
   
      WM_DESTROY = 2,
      WM_SIZE = 5,
      WM_CLOSE = 0x10,
      WM_QUIT = 0x12,
      WM_KEYDOWN = 0x100,
      WM_KEYUP = 0x101,
      WM_COMMAND =0x111,

      WM_ALL = [ self::WM_CLOSE, self::WM_COMMAND, self::WM_DESTROY, 
         self::WM_KEYDOWN, self::WM_KEYUP, self::WM_SIZE ],
      
      WS_VISIBLE = 0x10000000,
      WS_CHILD = 0x40000000,
      WS_POPUP = 0x80000000,
      WS_OVERLAPPEDWINDOW = 0xcf0000,

      SPI_GETWORKAREA = 0x30,
      
      SW_HIDE = 0,
      SW_SHOW = 5;
      
   /// user32.dll
   protected $ffu;
   /// kernel32.dll
   protected $ffk;
   /// gdi32.dll
   protected $ffg;
   /// comctl32.dll
   protected $ffc;
   /// hwnd -> View
   protected $wnds;
   /// saját wndproc
   protected $wndPrc;
   /// subclass proc
   protected $subPrc;
   /// felső üzenet objektum
   protected $wndMsg;
   /// az aktuális hInstance
   protected $hins;
   /// wide stringek tárolva
   protected $swps;
   /// szükséges wm-ek
   protected $nwms;
   /// kivétel callback-ben
   protected $err;
   /// hasznos rect
   protected $rect;
   /// kurzorok betöltve
   protected $crsrs;
   /// DC számolásokhoz
   protected $dc;
   /// billentyű állapotok
   protected $keyState;
   /// bájtok
   protected $wchars;
      
   function __construct($args) {
	   parent::__construct($args);
      $this->wnds = [];
      $this->swps = [];
      $this->crsrs = [];
      $this->initNwms();
      $ht = Tools::loadFile( __DIR__."/win_type".Tools::sysBits().".h" );
	   $hu = Tools::loadFile( __DIR__."/win_user32.h" );
	   $u = $this->ffu = \FFI::cdef( $ht.$hu, "user32.dll" );
      $hk = Tools::loadFile( __DIR__."/win_kernel32.h" );
      $k = $this->ffk = \FFI::cdef( $ht.$hk, "kernel32.dll" );
      $hg = Tools::loadFile( __DIR__."/win_gdi32.h");
      $g =  $this->ffg = \FFI::cdef( $ht.$hg, "gdi32.dll" );
      $hc = Tools::loadFile( __DIR__."/win_comctl32.h");
      $c = $this->ffc = \FFI::cdef( $ht.$hc, "comctl32.dll" );
      $this->wndMsg = $u->new("MSG");
      $this->rect = $u->new("RECT");
      $this->keyState = $u->new("BYTE[256]");
      $this->wchars = $u->new("WCHAR[2]");
      $sp = $this->subPrc = $c->new("SSUBCLASSPROC");
      $sp->f = function($hwnd,$msg,$wparam,$lparam,$sub,$ref) {
         return $this->subProc($hwnd,$msg,$wparam,$lparam);
      };
      $wp = $this->wndPrc = $u->new("SWNDPROC");
      $wp->f = function($hwnd,$msg,$wparam,$lparam) {
         return $this->wndProc($hwnd,$msg,$wparam,$lparam);
      };
      $cn = $this->swp( self::CVYGU );
      $this->hins = $this->checkW( $k->GetModuleHandleW(null),
         "Could not get instance");
      $wc = $u->new("WNDCLASSEXW");
      $wc->size = \FFI::sizeof($wc);
      $wc->wndProc = $wp->f;
      $wc->clsName = $cn;
      $wc->inst = $this->hins;
      $wc->back = $u->cast("void *",self::COLOR_WINDOW);
      $wc->cursor = $this->fromCursor( Cursor::DEFAULT );
      $this->checkW( $u->RegisterClassExW( \FFI::addr($wc) ),
         "Could not register window class");
      $this->dc = $this->checkW( $g->CreateCompatibleDC(null),
         "Could not create DC" );
   }      
 
   function viewCreate( View $v ) {
      $u = $this->ffu;
      $k = $this->ffk;
      $ret = null;
      switch ($h = $v->kind()) {
         case Button::BUTTON:
            $ret = $u->CreateWindowExW( 0, $this->swp( self::CBUTTON ),
            null, self::WS_POPUP | self::WS_VISIBLE, 0, 0, 10, 10,
            null, null, $this->hins, null ); 
         break;
         case Group::GROUP:
         case Label::LABEL:
            $ret = $u->CreateWindowExW( 0, $this->swp( self::CSTATIC ),
            null, self::WS_POPUP | self::WS_VISIBLE, 0, 0, 10, 10,
            null, null, $this->hins, null ); 
         break;
         case Window::WINDOW:
            $ret = $u->CreateWindowExW( 0, $this->swp( self::CVYGU ), 
               null, self::WS_OVERLAPPEDWINDOW, 0, 0, 100, 100, 
               null, null, $this->hins, null );
         break;
         default: return parent::viewCreate( $v );
      }
      $this->checkW( $ret, "Could not create winapi $h" );
      $v->impl = $ret;
      $this->wnds[ $this->ptri( $ret ) ] = \WeakReference::create($v);
      if (Window::WINDOW != $h) {
         $this->checkW( $this->ffc->SetWindowSubclass(
            $ret,$this->subPrc->f,1,0),
            "Could not set window subclass");
      }
   }
 
   /// string WCHAR *-gá alakítás
   function sw( $s ) {
      $u = mb_convert_encoding( $s, Tools::U16L, Tools::UTF );
      $l = strlen($u);
      $buf = $this->ffu->new("WCHAR[".(($l >> 1)+1)."]");
      \FFI::memcpy($buf,$u,$l);
      return $buf;
   }

   /// wide stringként tárolás, és pointer az első betűre
   function swp( $s ) {
      if (! $ret = Tools::g( $this->swps, $s ))
         $ret = $this->swps[$s] = $this->sw($s);
      return \FFI::addr( $ret[0] );
   }

   /// WCHAR * stringgé alakítás
   function ws( $w, $l ) {
      $u = $this->ffu;
      $ret = \FFI::string( $u->cast("char *",\FFI::addr($w)), 2*$l );
      return mb_convert_encoding( $ret, Tools::UTF, Tools::U16L );
   }

   /// wndProc futtatás
   function wndProc( $hwnd, $msg, $wparam, $lparam ) {
      if ( array_key_exists( $msg, $this->nwms )) {
         try {
            if ($this->handleMsg( $hwnd, $msg, $wparam, $lparam ))
               return 0;
         } catch (Throwable $e) {
            $this->err = $e;
         }
      }
      return $this->ffu->DefWindowProcW($hwnd,$msg,$wparam,$lparam);
   }

   /// subProc futtatás
   function subProc( $hwnd, $msg, $wparam, $lparam ) {
      if ( array_key_exists( $msg, $this->nwms )) {
         try {
            if ($this->handleMsg($hwnd,$msg,$wparam,$lparam))
               return 0;
         } catch (Throwable $e) {
            $this->err = $e;
         }
      }
      return $this->ffc->DefSubclassProc($hwnd,$msg,$wparam,$lparam);
   }

   ///  egy view message kezelése
   function handleMsg($hwnd, $msg, $wparam, $lparam) {
      if ( ! $v = $this->viewByHwnd( $hwnd ) )
         return;
      $u = $this->ffu;
      switch ($msg) {
         case self::WM_CLOSE:
            if ( ! $v->handle( Window::CLOSING ))
               return true;
         case self::WM_SIZE:
            $v->handle( Group::LAYOUT );
            return true;
         case self::WM_COMMAND:
            if ( ! $s = $this->viewByHwnd( $u->cast("HWND",$lparam)))
               return;
            switch ( $e = ($wparam >> 16) & 0xffff ) {
               case self::BN_CLICKED:
                  $s->handle( Action::FIRE );
                  return  true;
            }
         break;
         case self::WM_KEYDOWN:
         case self::WM_KEYUP:
            $k = $this->key( $wparam, $lparam );
            if ($v->handle( View::KEY, [$k] ))
               return true;
         break;
      }   
   }

   /// check windows hibával
   function checkW( $x, $err ) {
      if ( ! (bool)$x )
         throw new EVygu("$err: ".$this->lastError());
      return $x;
   }
   
   /// utolsó hibaüzenet
   function lastError() {
      $k = $this->ffk;
      $ret = $k->GetLastError();
      if ( ! $ret ) return "";
      $buf = $k->new("WCHAR[".self::ERRLEN."]");
      if ( $l = $k->FormatMessageW( 0x1200, null, $ret, 0,
         $buf, self::ERRLEN, null )
      )
         return sprintf( "%s (%s)", $this->ws( $buf, $l ), $ret );
         else return "$ret";
   }

   function viewProperty( View $v, $p, $x ) {
      switch ($p) {
         case View::VISIBLE: return $this->viewVisible($v,$x);
         case View::TEXT: case Window::TITLE: 
            return $this->viewText($v,$x);
      }
      return parent::viewProperty($v,$p,$x);
   }

   /// ablak címsor olvasása vagy írása
   function viewText($v,$x) {
      $u = $this->ffu;
      if (Tools::GET == $x) {
         $l = $u->GetWindowTextLengthW( $v->impl );
         $buf = $u->new("WCHAR[".($l+1)."]");
         $bufp = \FFI::addr($buf[0]);
         $u->GetWindowTextW( $v->impl, $bufp, $l+1 );
         return $this->ws( $buf, $l );
      } else {
         $this->checkW( $u->SetWindowTextW( $v->impl, $this->sw( "$x" )),
            "Could not set text" );
      }
   }

   /// ablak elrejtése, vagy megjelenítése
   function viewVisible($v,$x) {
      $u = $this->ffu;
      if (Tools::GET === $x)
         return $u->IsWindowVisible( $v->impl );
      if ($x) {
         $u->ShowWindow( $v->impl, self::SW_SHOW );
      } else {
         $u->ShowWindow( $v->impl, self::SW_HIDE );
      }
   }

   function viewHandler(View $v, $e, ?Handler $o, ?Handler $h) {
   }

   function viewParent( View $v, ?Group $g ) {
      $u = $this->ffu;
      $vi = $v->impl;
      $s = $u->GetWindowLongPtrW( $vi, self::GWL_STYLE );
      if ($g) {
         $u->SetWindowLongPtrW( $vi, self::GWL_STYLE, 
            $s | self::WS_CHILD & ! self::WS_POPUP );
         $u->SetParent($vi, $g->impl);
      } else {
         $u->SetParent($vi, null);
         $u->SetWindowLongPtrW( $vi, self::GWL_STYLE, 
            $s | self::WS_POPUP & ! self::WS_CHILD );
      }
   }

   function runStep( $wait ) {
      $u = $this->ffu;
      $m = $this->wndMsg;
      $ma = \FFI::addr( $m );
      if (! $wait
            && ! $u->PeekMessageW( $ma, null, 0, 0, 0 ))
         return false;
      $ret = $u->GetMessageW( $ma, null, 0, 0 );
      if ( 0 > $ret )
         throw new EVygu("Cannot get winapi message: ".$this->lastError());
      $u->TranslateMessage( $ma );
      $u->DispatchMessageW( $ma );
      if ( self::WM_QUIT == $m->message ) {
         $this->over = true;
         return true;
      }
      $this->checkErr();
      return true;
   }

   function finish() {
      $this->ffu->PostQuitMessage(0);
   }


   function viewByHwnd( $hwnd ) {
      $i = $this->ptri( $hwnd );
      if ( $ret = Tools::g( $this->wnds, $i ) )
         return $ret->get();
         else return null;
   }

   /// c pointer -> int
   function ptri( $p ) {
      return $p - $this->ffu->cast("void*",0);
   }

   function screenCoord( Screen $s, $c ) {
      $u = $this->ffu;
      $r = $this->rect;
      switch ($c) {
         case Layout::CONTHEIGHT:
         case Layout::CONTWIDTH:
            $this->checkW( $u->SystemParametersInfoW(
               self::SPI_GETWORKAREA, 0, \FFI::addr($r), 0 ),
               "Could not get screen coord: $c");
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
      $u = $this->ffu;
      $im = $v->impl;
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

   /// speciális koordináták
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
            $r = $this->temp( $v, self::TDEF, $tmp );
            return $r[0];
         break;
         case Layout::DEFHEIGHT:
            $r = $this->temp( $v, self::TDEF, $tmp );
            return $r[1];
         break;
         default: throw new EVygu("Unknown spec coord: $c");
      }
   }

   function viewFocus(View $v) {
      $this->ffu->SetFocus( $v->impl );
   }

   /// Key eseményből
   function key( $wparam, $lparam ) {
      $ret = new Key();
      $this->getKeyState();
      $ret->event = Tools::bits($lparam,31,1) 
         ? Key::RELEASE : Key::PRESS;
      $ret->modif = $this->keyModif();
      $ret->scan = Tools::bits($lparam,16,8);
      $ret->unicode = $this->keyUnicode( $wparam, $ret->scan );
      $ret->special = Tools::g( self::VK_SPECS, intval( $wparam ) );
      return $ret;
   }

   /// módosítók a keytstate alapján
   protected function keyModif() {
      $s = $this->keyState;
      $ret = 0;
      if ($s[self::VK_SHIFT] & 0x80)
         $ret |= Key::MSHIFT;
      if ($s[self::VK_CONTROL] & 0x80)
         $ret |= Key::MCTRL;
      if ($s[self::VK_MENU] & 0x80)
         $ret |= Key::MALT;
      return $ret;
   }

   /// unicode kó
   protected function keyUnicode($wparam,$scan) {
      $n = $this->ffu->ToUnicode( $wparam,
         $scan, \FFI::addr($this->keyState[0]),
         \FFI::addr($this->wchars[0]), 2, 0 );
      if (1 == $n)
         return $this->wchars[0];
      return 0;
   }

   /// keyboard state lekérése
   protected function getKeyState() {
      $this->checkW( $this->ffu->GetKeyboardState( 
         \FFI::addr($this->keyState[0])), "Could not get keyboard state");
   }

   /// temp adat $v-ez
   protected function temp(View $v, $kind, & $tmp ) {
      $u = $this->ffu;
      if (true === $tmp)
         $tmp = [self::TLAST=>true];
      if ( ! $ret = Tools::g( $tmp, $kind )) {
         switch ($kind) {
            case self::TRECT:
               $this->checkW( $u->GetWindowRect( $v->impl, 
                  \FFI::addr($this->rect)),"Could not get window rect");
               $ret = $this->rect;
            break;
            case self::TCONT:
               $this->checkW( $u->GetClientRect( $v->impl, 
                  \FFI::addr($this->rect)),"Could not get client rect");
               $ret = $this->rect;
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
      $u = $this->ffu;
      switch ($k = $v->kind()) {
         case Label::LABEL:
            $txt = $v->text();
            $r = $this->rect;
            $u->DrawTextW( $this->dc, $this->sw( $txt ), -1,
               \FFI::addr($r), self::DT_CALCRECT );
            return [$r->right-$r->left,$r->bottom-$r->top];
         break;
         default: throw new EVygu("Cannot get default size: $k");
      }
   }
   

   /// nwms hash létrehozása
   protected function initNwms() {
      $this->nwms = [];
      foreach ( self::WM_ALL as $w )
         $this->nwms[$w] = true;
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
         $this->checkW( $this->ffu->MoveWindow( $v->impl, 
            $r->left, $r->top, $r->right-$r->left, $r->bottom-$r->top,
            true ), "Could not move window");
      }
   }

   /// kurzor konverzió vissza
   protected function fromCursor( $c ) {
      if ( ! $ret = Tools::g( $this->crsrs, $c )) {
         $u = $this->ffu;
         if ( ! $id = Tools::g( $this->map( View::CURSOR ), $c ))
            throw new EVygu("Unknown cursor: $c");
         $idp = $u->cast("void *",$id);
         $ret = $this->checkW( $u->LoadCursorW( null, $idp ),
            "Could not load cursor: $id");
         $this->crsrs[ $c ] = $ret;
      }
      return $ret;
   }

   protected function createMap( $name ) {
      switch ($name) {
         case View::CURSOR: return array_flip( self::CURSORS );
         default: return parent::createMap($name);
      }
   }


}
