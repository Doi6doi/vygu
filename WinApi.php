<?php

namespace vygu;

/// Windows API engine
class WinApi extends Vygu {
	
   const
      WINAPI = "winapi";
      
   const
      CVYGU = "Vygu",
      CSTATIC = "STATIC",
      ERRLEN = 1024;

   /// temp adatok
   const
      TLAST = "tLast",
      TRECT = "tRect";

   const
      GWL_STYLE = -16,
   
      WM_DESTROY = 2,
      WM_SIZE = 5,
      WM_CLOSE = 0x10,
      WM_QUIT = 0x12,

      WM_ALL = [ self::WM_DESTROY, self::WM_SIZE, self::WM_CLOSE ],
      
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
   /// hwnd -> View
   protected $wnds;
   /// saját wndproc
   protected $wndPrc;
   /// saját class atom
   protected $wndAtom;
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
      
   function __construct($args) {
	   parent::__construct($args);
      $this->wnds = [];
      $this->swps = [];
      $this->initNwms();
      $ht = Tools::loadFile( __DIR__."/win_type".Tools::sysBits().".h" );
	   $hu = Tools::loadFile( __DIR__."/win_user32.h" );
	   $u = $this->ffu = \FFI::cdef( $ht.$hu, "user32.dll" );
      $hk = Tools::loadFile( __DIR__."/win_kernel32.h" );
      $k = $this->ffk = \FFI::cdef( $ht.$hk, "kernel32.dll" );
      $this->wndMsg = $u->new("MSG");
      $this->rect = $u->new("RECT");
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
      $this->wndAtom = $u->RegisterClassExW( \FFI::addr($wc) );
      $this->checkW( $this->wndAtom, "Could not register window class" );
   }      
 
   function viewCreate( View $v ) {
      $u = $this->ffu;
      $k = $this->ffk;
      $ret = null;
      switch ($h = $v->kind()) {
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
      }
      $this->checkW( $ret, "Could not create winapi $h" );
      $v->impl = $ret;
      $this->wnds[ $this->ptri( $ret ) ] = \WeakReference::create($v);
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
      $ret = \FFI::string( \FFI::cast("char *",$w), 2*$l );
      return mb_convert_encoding( $ret, Tools::UTF, Tools::U16L );
   }

   /// wndProc futtatás
   function wndProc( $hwnd, $msg, $wparam, $lparam ) {
      $u = $this->ffu;
      if ( array_key_exists( $msg, $this->nwms )) {
         try {
            $v = $this->viewByHwnd( $hwnd );
            switch ($msg) {
               case self::WM_CLOSE:
                  if ( $v instanceof Window 
                     && $h = $v->handler( Window::CLOSING )
                  ) {
                     if ( ! call_user_func( $h->impl, $v ) )
                        return 0;
                  }
               break;
            }
         } catch (Throwable $e) {
            $this->err = $e;
         }
      }
      return $u->DefWindowProcW( $hwnd, $msg, $wparam, $lparam );
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
         $this->checkW( $u->GetWindowTextW( $v->impl, $bufp, $l+1 ),
            "Could not get text" );
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
      $u->ShowWindow( $v->impl, $x ? self::SW_SHOW : self::SW_HIDE );
   }

   function handlerCreate(View $v, $e, callable $cb) {
      $ret = new Handler();
      $ret->view = $v;
      $ret->impl = $cb;
      return $ret;
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

   /// temp adat $v-ez
   protected function temp(View $v, $kind, & $tmp ) {
      $u = $this->ffu;
      if (true === $tmp)
         $tmp = [self::TLAST=>true];
      if ( ! $ret = Tools::g( $tmp, $kind )) {
         switch ($kind) {
            case self::TRECT:
               $this->checkW( $u->GetWindowRect( $v->impl, \FFI::addr($this->rect)),
                  "Could not get window rect");
               $ret = $this->rect;
            break;
            default: throw new EVygu("Unknown temp kind: $kind");
         }
         $tmp[$kind] = $ret;
      }
      return $ret;
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

}
