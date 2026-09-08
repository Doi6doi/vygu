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

   const
      WM_DESTROY = 2,
      WM_SIZE = 5,
      WM_CLOSE = 0x10,

      WM_ALL = [ self::WM_DESTROY, self::WM_SIZE, self::WM_CLOSE ],
      
      WS_VISIBLE = 0x10000000,
      WS_CHILD = 0x40000000,
      WS_OVERLAPPEDWINDOW = 0xcf0000,
      
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
   /// az aktuális hInstance
   protected $hins;
   /// dummy window a parent nélküli view-knak
   protected $dummy;
   /// wide stringek tárolva
   protected $swps;
   /// szükséges wm-ek
   protected $nwms;
   /// kivétel callback-ben
   protected $err;
      
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
      $this->dummy = $u->CreateWindowExW( 0, $cn, null, 
         0, 0, 0, 100, 100, null, null,
         $this->hins, null );
      $this->checkW( $this->dummy, "Could not create dummy window" );
   }      
 
   function viewCreate( View $v ) {
      $u = $this->ffu;
      $k = $this->ffk;
      $ret = null;
      switch ($h = $v->kind()) {
         case Label::LABEL:
            $ret = $u->CreateWindowExW( 0, $this->swp( self::CSTATIC ),
            null, self::WS_CHILD | self::WS_VISIBLE, 0, 0, 10, 10,
            $this->dummy, null, $this->hins, null ); 
         break;
         case Window::WINDOW:
            $ret = $u->CreateWindowExW( 0, $this->swp( self::CVYGU ), 
               null, self::WS_OVERLAPPEDWINDOW, 0, 0, 100, 100, 
               null, null, $this->hins, null );
         break;
      }
      $this->checkW( $ret, "Could not create winapi $h" );
      $v->impl = $ret;
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
                  )
                     return intval( call_user_func( $h->impl, $v ) );
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
         case Window::TITLE: return $this->viewText($v,$x);
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
      if (Tools::GET == $x)
         return $u->IsWindowVisible( $v->impl );
         else $this->checkW( $u->ShowWindow( $v->impl, 
            $x ? self::SW_SHOW : self::SW_HIDE ));
   }

   function handlerCreate(View $v, $e, callable $cb) {
      $ret = new Handler();
      $ret->view = $v;
      $ret->impl = $cb;
      return $ret;
   }

   function viewHandler(View $v, $e, ?Handler $o, ?Handler $h) {
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
   
   
}
