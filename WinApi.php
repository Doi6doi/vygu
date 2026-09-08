<?php

namespace vygu;

/// Windows API engine
class WinApi extends Vygu {
	
   const
      WINAPI = "winapi";
      
   const
      CLSNAME = "VyguWindow",
      ERRLEN = 1024;

   const
      WNDATOM = "wndAtom",
      WNDCLS = "wndCls",
      WNDPROC = "wndProc";

   const
      WS_OVERLAPPEDWINDOW = 0xcf0000;
      
   /// user32.dll
   protected $ffu;
   /// kernel32.dll
   protected $ffk;
   /// ablakok száma
   protected $nwind;
      
   function __construct($args) {
	   parent::__construct($args);
      $this->nwind = 0;
	   $hu = Tools::loadFile( __DIR__."/win_user32.h" );
	   $this->ffu = \FFI::cdef( $hu, "user32.dll" );
      $hk = Tools::loadFile( __DIR__."/win_kernel32.h" );
      $this->ffk = \FFI::cdef( $hk, "kernel32.dll" );
   }      
 
   function viewCreate( View $v ) {
      $u = $this->ffu;
      $k = $this->ffk;
      switch ($h = $v->kind()) {
         case Window::WINDOW:
            $v->data = [];
            $wp = $u->new("SWNDPROC");
            $wp->f = function($hwnd,$msg,$wparam,$lparam) use ($v) {
               return $this->wndProc($v,$hwnd,$msg,$wparam,$lparam);
            };
            $v->data[ self::WNDPROC ] = $wp;
            $cn = $v->data[ self::WNDCLS ] = 
               $this->sw( self::CLSNAME.(++$this->nwind) );
            $hi = $k->GetModuleHandleW(null);
            $wc = $u->new("WNDCLASSEXW");
            $wc->size = \FFI::sizeof($wc);
            $wc->wndProc = $wp->f;
            $wc->inst = $hi;
            $wc->clsName = \FFI::addr( $cn[0] );
            $a = $this->checkW( $u->RegisterClassExW( \FFI::addr($wc) ),
               "Could not register window class" );
            $v->data[ self::WNDATOM ] = $a;
            $ret = $u->CreateWindowExW( 0, $cn, null, 
               self::WS_OVERLAPPEDWINDOW, 0, 0, 100, 100, null, null,
               $hi, null );
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

   /// WCHAR * stringgé alakítás
   function ws( $w, $l ) {
      $ret = \FFI::string( \FFI::cast("char *",$w), 2*$l );
      return mb_convert_encoding( $ret, Tools::UTF, Tools::U16L );
   }

   /// wndProc futtatás
   function wndProc( View $v, $hwnd, $msg, $wparam, $lparam ) {
      return $this->ffu->DefWindowProcW( $hwnd, $msg, $wparam, $lparam );
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
   
}
