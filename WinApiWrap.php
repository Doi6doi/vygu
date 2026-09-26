<?php

namespace vygu;

/// Wrapper for WinApi calls
class WinApiWrap {

   const
      CBUTTON = "BUTTON",
      CEDIT = "EDIT",
      CVYGU = "Vygu",
      CSTATIC = "STATIC",
      ERRLEN = 1024,
      PATHLEN = 1024;

   const
      HMENU = "hmenu";

   // temp adatok
   const
      TCONT = "tCont",
      TDEF  = "tDef",
      TLAST = "tLast",
      TRECT = "tRect";

   const
      BCM_GETIDEALSIZE = 0x1601,
      
      BN_CLICKED = 0,

      CF_UNICODETEXT = 0xd,

      COLOR_WINDOW = 5,
    
      CS_HREDRAW = 2,
      CS_VREDRAW = 1,

      DT_CALCRECT = 0x400,

      EM_GETSEL = 0xb0,
      EM_SETSEL = 0xb1,
      EM_REPLACESEL = 0xc2,

      ES_AUTOVSCROLL = 0x40,
      ES_MULTILINE = 0x4,
      ES_WANTRETURN = 0x1000,

      GMEM_MOVEABLE = 2,

      GWL_STYLE = -16,

      MF_POPUP = 0x10,
      MF_STRING = 0,

      OFN_FILEMUSTEXIST = 0x1000,
      OFN_NOCHANGEDIR = 8,
      OFN_OVERWRITEPROMPT = 2,
      OFN_PATHMUSTEXIST = 0x800,

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

      WM_DESTROY = 2,
      WM_SIZE = 5,
      WM_CLOSE = 0x10,
      WM_QUIT = 0x12,
      WM_KEYDOWN = 0x100,
      WM_KEYUP = 0x101,
      WM_COMMAND =0x111,

      WM_ALL = [ self::WM_CLOSE, self::WM_COMMAND, self::WM_DESTROY,
         self::WM_KEYDOWN, self::WM_KEYUP, self::WM_SIZE ],

      WS_EX_CLIENTEDGE = 0x200,
      WS_VISIBLE = 0x10000000,
      WS_VSCROLL = 0x200000,
      WS_CHILD = 0x40000000,
      WS_POPUP = 0x80000000,
      WS_OVERLAPPEDWINDOW = 0xcf0000,

      SPI_GETWORKAREA = 0x30,

      SW_HIDE = 0,
      SW_SHOW = 5;

   // billentyű állapotok
   public $keyState;
   // saját wndproc
   public $wndPrc;
   // subclass proc
   public $subPrc;

   // szükséges wm-ek
   public $nwms;
   // user32.dll
   protected $ffu;
   // kernel32.dll
   protected $ffk;
   // gdi32.dll
   protected $ffg;
   // comctl32.dll
   protected $ffc;
   // comdlg32.dll
   protected $ffd;
   // felső üzenet objektum
   protected $wndMsg;
   // az aktuális hInstance
   protected $hins;
   // wide stringek tárolva
   protected $swps;
   // hasznos rect
   protected $rect;
   // hasznos size
   protected $siz;
   // kurzorok betöltve
   protected $crsrs;
   // DC számolásokhoz
   protected $dc;
   // bájtok
   protected $wchars;
   // longok
   protected $longs;
   // dummy parent
   protected $dummy;


   function __construct() {
      $hu = Tools::loadFile( __DIR__."/win_user32.h" );
      $this->swps = [];
      $this->crsrs = [];
      $this->initNwms();
      $ht = Tools::loadFile( __DIR__."/win_type".Tools::sysBits().".h" );
      $hk = Tools::loadFile( __DIR__."/win_kernel32.h" );
      $k = $this->ffk = \FFI::cdef( $ht.$hk, "kernel32.dll" );
      $hg = Tools::loadFile( __DIR__."/win_gdi32.h");
      $g =  $this->ffg = \FFI::cdef( $ht.$hg, "gdi32.dll" );
      $hc = Tools::loadFile( __DIR__."/win_comctl32.h");
      $c = $this->ffc = \FFI::cdef( $ht.$hc, "comctl32.dll" );
      $hd = Tools::loadFile( __DIR__."/win_comdlg32.h");
      $this->ffd = \FFI::cdef( $ht.$hd, "comdlg32.dll" );
      $hu = Tools::loadFile( __DIR__."/win_user32.h");
	   $u = $this->ffu = \FFI::cdef( $ht.$hu, "user32.dll" );
      $this->wndMsg = $u->new("MSG");
      $this->rect = $u->new("RECT");
      $this->siz = $u->new("SIZE");
      $this->keyState = $u->new("BYTE[256]");
      $this->wchars = $u->new("WCHAR[2]");
      $this->longs = $u->new("LONG[2]");
      $this->activateContext( "comctl6.manifest");
      $sp = $this->subPrc = $c->new("SSUBCLASSPROC");
      $wp = $this->wndPrc = $u->new("SWNDPROC");
      $cn = $this->swp( self::CVYGU );
      $this->hins = $this->checkW( $k->GetModuleHandleW(null),
         "Could not get instance");
      $this->dummy = $this->CreateWindowDummy();
      $wc = $u->new("WNDCLASSEXW");
      $wc->size = \FFI::sizeof($wc);
      $wc->style = 3;
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

   function cast( $typ, $obj ) {
      return $this->ffu->cast( $typ, $obj );
   }

   // string WCHAR *-gá alakítás
   function sw( $s ) {
      $u = mb_convert_encoding( $s, Tools::U16L, Tools::UTF );
      $l = strlen($u);
      $buf = $this->ffu->new("WCHAR[".(($l >> 1)+1)."]");
      \FFI::memcpy($buf,$u,$l);
      return $buf;
   }

   // wide stringként tárolás, és pointer az első betűre
   function swp( $s ) {
      if (! $ret = Tools::g( $this->swps, $s ))
         $ret = $this->swps[$s] = $this->sw($s);
      return \FFI::addr( $ret[0] );
   }

   // WCHAR * stringgé alakítás
   function ws( $w, $l ) {
      if (null === $l)
         for ($l=0; 0 != $w[$l]; ++$l);
      $u = $this->ffu;
      $ret = \FFI::string( $u->cast("char *",\FFI::addr($w)), 2*$l );
      return mb_convert_encoding( $ret, Tools::UTF, Tools::U16L );
   }

   // ellenőrzés, hogy nem üres-e
   protected function check( $x, $err ) {
      if ( ! $x || Tools::cIsNull($x))
         throw new EVygu($err);
      return $x;
   }

   // check windows hibával
   function checkW( $x, $err ) {
      if ( ! (bool)$x )
         throw new EVygu("$err: ".$this->lastError());
      return $x;
   }

   // check handle windows hibával
   function checkH( $x, $err ) {
      $i = $this->ptri($x);
      if ( 0 == $i || -1 == $i)
         throw new EVygu("$err: ".$this->lastError());
      return $x;
   }

   // utolsó hibaüzenet
   function lastError() {
      $k = $this->ffk;
      $ret = $k->GetLastError();
      if ( ! $ret ) return "";
      $buf = $k->new("WCHAR[".self::ERRLEN."]");
      $l = $k->FormatMessageW( 0x1200, null, $ret, 0,
         \FFI::addr($buf[0]), self::ERRLEN, null );
      if ($l)
         return sprintf( "%s (%s)", $this->ws( $buf, $l ), $ret );
         else return "$ret";
   }
    
   // activationcontext aktiválás
   function activateContext( $fname ) {
      $k = $this->ffk;
      $act = $k->new("ACTCTXW");
      $act->cbSize = \FFI::sizeof($act);
      $act->lpSource = $this->swp( __DIR__."\\".$fname );
      $acok = $k->new("ULONG_PTR");
      $h = $this->checkH( $k->CreateActCtxW(\FFI::addr($act)),
         "Could not create activation context");
      $this->checkW($k->ActivateActCtx($h, \FFI::addr($acok)),
         "Could not activate context");
   }
    
   function appendMenuItem( $m, $id, $name ) {
      $ret = $this->ffu->AppendMenuW( $m, self::MF_STRING, 
         $id, $this->sw( $name ));
      return $this->checkW( $ret, "Could not append menu item");

   }

   function appendMenuSub( $m, $w, $name ) {
      $ret = $this->ffu->AppendMenuW( $m, self::MF_POPUP, 
         $this->ptri( $w ), $this->sw( $name ));
      return $this->checkW( $ret, "Could not append submenu");
   }

   function calcText( $txt ) {
      $this->ffu->DrawTextW( $this->dc, $this->sw( $txt ), -1,
         \FFI::addr($this->rect), self::DT_CALCRECT );
      return $this->rect;
   }

   /// clpiboard nyitás / zárás
   function closeClipboard($open) {
      $ret = $this->ffu->CloseClipboard( $this->dummy );
      $this->checkW( $ret, "Could not close clipboard");
   }
    
   // Button Wnd készítése
   function CreateWindowButton() {
      $ret = $this->ffu->CreateWindowExW( 0, 
         $this->swp( self::CBUTTON ),
         null, self::WS_CHILD | self::WS_VISIBLE, 0, 0, 10, 10,
         $this->dummy, null, $this->hins, null );
      return $this->checkH( $ret, "Could not create button");
   }

   // Dummy Wnd készítése
   function CreateWindowDummy() {
      $ret = $this->ffu->CreateWindowExW( 0, 
         $this->swp( self::CSTATIC ), null, 
         self::WS_POPUP | self::WS_VISIBLE, 0, 0, 10, 10,
         null, null, $this->hins, null );
      return $this->check( $ret, "Could not create dummy window" );
   }

   // Edit Wnd készítése
   function CreateWindowEdit() {
      $ret = $this->ffu->CreateWindowExW( self::WS_EX_CLIENTEDGE, 
         $this->swp( self::CEDIT ), null, self::WS_CHILD
            | self::WS_VISIBLE | self::WS_VSCROLL | self::ES_MULTILINE 
            | self::ES_AUTOVSCROLL | self::ES_WANTRETURN,
            0, 0, 30, 30, $this->dummy, null, $this->hins, null );
      return $this->checkH( $ret, "Could not create edit");
   }

   // Static Wnd készítés
   function CreateWindowStatic() {
      $ret = $this->ffu->CreateWindowExW( 0, $this->swp( self::CSTATIC ),
       null, self::WS_CHILD | self::WS_VISIBLE, 0, 0, 10, 10,
            $this->dummy, null, $this->hins, null );
      return $this->checkH( $ret, "Could not create static");
   }

   // Window wnd készítés
   function CreateWindowWindow() {
      return $this->ffu->CreateWindowExW( 0, $this->swp( self::CVYGU ),
         null, self::WS_OVERLAPPEDWINDOW, 0, 0, 100, 100,
         null, null, $this->hins, null );
   }

   // Popup menü készítés
   function CreatePopupMenu() {
      $ret = $this->ffu->CreatePopupMenu();
      return $this->checkH( "Could not create menu");
   }

   /// menü felszámolás
   function destroyMenu( $m ) {
      $ret = $this->ffu->DestroyMenu( $m );
      return $this->checkW( "Could not destroy menu");
   }

   /// default wndproc
   function DefWindowProc( $w, $msg, $wparam, $lparam ) {
      return $this->ffu->DefWindowProcW($w,$msg,$wparam,$lparam);
   }

   /// default subproc
   function DefSubclassProc( $w, $msg, $wparam, $lparam ) {
      return $this->ffc->DefSubclassProc($w,$msg,$wparam,$lparam);
   }

   function getClientRect( $w ) {
      $ret = $this->ffu->GetClientRect( $v->impl, \FFI::addr($this->rect));
      $this->checkW( $ret, "Could not get client rect");
      return $this->rect;
   }      

   /// clipboard contents
   function getClipboard() {
      $this->openClipboard();
      try {
         $h = $this->ffu->GetClipboardData( self::CF_UNICODETEXT );
         $this->checkH( $h, "Could not get clipboard data");
         $s = $this->globalLock($h);
         try {
            $l = $k->lstrlenW( $s );
            return $this->ws( $s, $l );
         } finally {
            $this->globalUnlock( $h );
         }
      } finally {
         $this->closeClipboard();
      }
   }

   function getIdealSize( $w ) {
      $zp = $this->ptri( \FFI::addr( $this->siz ) );
      $ret = $u->SendMessageW( $w, self::BCM_GETIDEALSIZE, 0, $zp );
      $this->checkW( $ret, "Could not get ideal size");
      return $this->siz;
   }

   // keyboard state lekérése
   protected function getKeyState() {
      $ret = $this->ffu->GetKeyboardState( \FFI::addr($this->keyState[0]));
      $this->checkW( $ret, "Could not get keyboard state");
   }

   /// openfile dilaog
   function getOpenFileName( $kind ) {
      $d = $this->ffd;
      $s = $d->new("OPENFILENAMEW");
      $fn = $d->new("WCHAR[".self::PATHLEN."]");
      $s->lStructSize = \FFI::sizeof( $s );
      $s->lpstrFile = \FFI::addr($fn[0]);
      $s->nMaxFile = self::PATHLEN;
      if ( Dialog::OPEN == $kind) {
         $s->Flags = self::OFN_FILEMUSTEXIST | self::OFN_PATHMUSTEXIST;
         $r = $d->GetOpenFileNameW( \FFI::addr($s));
      } else {
         $s->Flags = self::OFN_OVERWRITEPROMPT 
            | self::OFN_PATHMUSTEXIST | self::OFN_NOCHANGEDIR;
         $r = $d->GetSaveFileNameW( \FFI::addr($s));
      }
      if ( $r )
         return $this->ws( $fn, null );
         else return null;
   }

   /// get bounding rect
   function getRect( $w ) {
      $ret = $this->ffu->GetWindowRect( $w, \FFI::addr($this->rect));
      $this->checkW( $ret, "Could not get window rect");
      return $this->rect;
   }

   /// getsel hívás
   function getSel( $h ) {
      $lp0 = $this->ptri( \FFI::addr( $this->longs[0] ));
      $lp1 = $this->ptri( \FFI::addr( $this->longs[1] ));
      $this->ffu->SendMessageW( $h, self::EM_GETSEL, $lp0, $lp1 );
      return [$this->longs[0],$this->longs[1]];
   }

   function getStyle( $w ) {
      return $this->ffu->GetWindowLongPtrW( $w, self::GWL_STYLE );
   }

   // ablak teljes szövege
   function getText( $w ) {
      $u = $this->ffu;
      $l = $u->GetWindowTextLengthW( $w );
      $buf = $u->new("WCHAR[".($l+1)."]");
      $bufp = \FFI::addr($buf[0]);
      $u->GetWindowTextW( $w, $bufp, $l+1 );
      return $this->ws( $buf, $l );
   }

   // ablak szövegének része
   function getTextPart( $w, $head, $tail ) {
      $this->getRowCol( $head, $sr, $sc );
      $this->getRowCol( $tail, $er, $ec );
      $ret = "";
      for ($i=$sr; $i<=$er; ++$i) {
         $r = $this->getRow( $v->impl, $i );
         if ( $er == $i )
            $r = $this->wsub( $r, 0, $ec );
         if ( $sr == $i )
            $r = $this->wsub( $r, $sc );
            $ret .= $this->ws( $r, $this->wlen( $r ) );
      }
      return $ret;
   }      

   /// látható-e
   function getVisible( $w ) {
      return $this->ffu->IsWindowVisible( $w );
   }

   function globalAlloc( $z ) {
      $ret = $this->ffk->GlobalAlloc( self::GMEM_MOVEABLE, $wz );
      $this->checkH( $ret, "Could not alloc data");
   }

   function globalLock($h) {
      $ret = $this->ffk->GlobalLock($h);
      $this->checkW( $ret, "Could not lock data" );
   }

   function globalUnlock($h) {
      $this->ffk->GlobalUnlock($h);
   }

   /// invalidate
   function invalidate( $w ) {
      $this->ffu->InvalidateRect($w,null,false);
   }

   /// módosítók a keytstate alapján
   function keyModif() {
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

   // unicode kód
   protected function keyUnicode($wparam,$scan) {
      $n = $this->ffu->ToUnicode( $wparam,
         $scan, \FFI::addr($this->keyState[0]),
         \FFI::addr($this->wchars[0]), 2, 0 );
      if (1 == $n)
         return $this->wchars[0];
      return 0;
   }

   /// fő message lépés
   function messageStep( $wait ) {
      $m = $this->wndMsg;
      $ma = \FFI::addr( $m );
      if (! $wait && ! $u->PeekMessageW( $ma, null, 0, 0, 0 ))
         return false;
      $ret = $u->GetMessageW( $ma, null, 0, 0 );
      if ( 0 > $ret )
         throw new EVygu("Cannot get winapi message: ".$this->lastError());
      $u->TranslateMessage( $ma );
      $u->DispatchMessageW( $ma );
      return $m;
   }

   function loadCursor( $id ) {
      $idp = $u->cast("void *",$id);
      $ret = $u->LoadCursorW( null, $idp );
      return $this->checkW( $ret, "Could not load cursor: $id");
   }

   function moveWindow( $w, $r ) {
      $ret = $this->ffu->MoveWindow( $w,
         $r->left, $r->top, $r->right-$r->left, $r->bottom-$r->top,
         false );
      $this->checkW( $ret,"Could not move window");
   }

   /// clpiboard nyitás / zárás
   function openClipboard($open) {
      $ret = $this->ffu->OpenClipboard( $this->dummy );
      $this->checkW( $ret, "Could not open clipboard");
   }
    
   // c pointer -> int
   function ptri( $p ) {
      $u = $this->ffu;
      return $u->cast("void *",$p) - $u->cast("void*",0);
   }

   function setClipboard($x) {
      $this->openClipboard();
      try {
         $w = $this->sw( "$x" );
         $wz = \FFI::sizeof($w);
         $h = $this->globalAlloc( $wz );
         $s = $this->globalLock($h);
         try {
            \FFI::memcpy( $s, \FFI::addr( $w ), $wz );
         } finally {
            $this->globalUnlock($h);
         }
         $ret = $u->SetClipboardData( self::CF_UNICODETEXT, $h );
         $this->checkW( $ret, "Could not set clipboard data");
      } finally {
         $this->closeClipboard();
      }
   }


   function setFocus( $w ) {
      $this->ffu->SetFocus( $w );
   }

   /// menü beállítása
   function setMenu( $w, $m ) {
      $ret = $this->ffu->SetMenu( $w, $m );
      $this->checkW( $ret, "Could not set menu");
   }

   function setParent( $w, $x ) {
      $this->ffu->SetParent( $w, $x );
   }

   /// setsel hívás
   function setSel( $h, $head, $tail ) {
      $this->longs[0] = $head;
      $lp0 = $this->ptri( \FFI::addr( $this->longs[0] ));
      if (null === $tail ) {
         $lp1 = 0;
      } else {
         $this->longs[1] = $tail;
         $lp1 = $this->ptri( \FFI::addr( $this->longs[1] ));
      }
      $this->ffu->SendMessageW( $h, self::EM_SETSEL, $lp0, $lp1 );
   }

   function setStyle( $w, $x ) {
      $this->ffu->SetWindowLongPtrW( $w, self::GWL_STYLE, $x );
   }

   // subclass beállítása
   function setSubclass( $w ) {
      $ret = $this->ffc->SetWindowSubclass( $h, $this->subPrc->f,1,0);
      $this->checkW( $ret, "Could not set window subclass");
   }

   function setText( $w, $txt ) {
      $ret = $this->ffu->SetWindowTextW( $w, $this->sw( "$x" ));
      $this->checkW( $ret, "Could not set text" );
   }
    
   function setTextPart( $w, $head, $tail, $x ) {
      $s = $this->getSel( $w );
      $this->setSel( $w, $head, $tail );
      $wx = $this->sw( "$x" );
      $this->ffu->SendMessageW( $w, self::EM_REPLACESEL,
         true, $this->ptri( \FFI::addr( $w[0] ) ));
         $d = $len - ($s[1]-$s[0]);
         if ( $s[0] >= $tail )
            $s[0] += $d;
         if ( $s[1] >= $tail )
            $s[1] += $d;
         $this->setSel( $w, $s[0], $s[1] );
   }
    
   function setVisible( $w, $x ) {
      $ret = $this->ffu->ShowWindow( $w, 
         $x ? self::SW_SHOW : self::SW_HIDE );
      $this->checkW( $ret );
   }

   /// screen work area lekérdezés
   function workArea() {
      $ret = $this->ffu->SystemParametersInfoW(
         self::SPI_GETWORKAREA, 0, \FFI::addr($this->rect), 0 );
      $this->checkW( "Could not get workArea" );
      return $this->rect;
   }

   /// nwms hash létrehozása
   protected function initNwms() {
      $this->nwms = [];
      foreach ( self::WM_ALL as $w )
         $this->nwms[$w] = true;
   }

}
