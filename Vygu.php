<?php

namespace vygu;

class Vygu {

   const
      ENGINE = "engine",
      OVER = "over";

   /// singleton
   static function ins( $args=[] ) {
      if ( ! self::$ins )
         self::$ins = self::create( $args );
      return self::$ins;
   }

   /// singleton változó
   protected static $ins;
   /// kilépő bool
   static $over;

   /// új vygu készítés
   protected static function create( $args ) {
	  if ( ! $e = Tools::g( $args, self::ENGINE )) {
		 $iz = 8*PHP_INT_SIZE;
		 switch ($f = PHP_OS_FAMILY) {
			case "Windows": $e = WinApi::WINAPI; break;
			case "Linux": $e = Gtk4::GTK4; break;
			default: throw new EVygu("Unknown system: $f");
		 }
	  }
	  switch ($e) {
		 case WinApi::WINAPI: return new WinApi($args);
		 case Gtk4::GTK4: return new Gtk4($args);
		 default: throw new EVygu("Unknown engine: $e");
	  }
   }

   /// kezelők
   protected $handlers;

   function __construct($args) {
      $this->handlers = [];
   }

   /// view fókuszálása
   function viewFocus(View $v) {
      throw new EVygu("Cannot focus ".$v->kind());
   }

   /// menü létrehozása
   function menuCreate(Menu $m) {
      throw new EVygu("Cannot create menu");
   }

   /// hozzáadás menühöz
   function menuAdd(Menu $m, $x) {
      throw new EVygu("Cannot add ".Tools::type($x)." to menu");
   }

   /// view szülőjének beállítása
   function viewParent( View $v, ?Group $x ) {
      throw new EVygu("Cannot set ".$v->kind()." parent to ".Tools::str($g));
   }

   /// esemény kezelése
   function handle($kind) {
      if ( $h = Tools::g( $this->handlers, $kind )) {
         $a = func_get_args();
         array_shift( $a );
         return call_user_func_array( $h, $a );
      }
      return null;
   }

   /// handler készítés
   function handlerCreate(View $v, $e, callable $cb) {
      throw new EVygu("Cannot create handler: ".$v->kind().".".$e);
   }

   /// eseménysor futtatása
   function run() {
      while (! $this->runOver()) {
         $this->runStep( true );
      }
   }

   /// egy esemény lekérése és feldolgozása
   function runStep( $wait ) { Tools::notImpl( $this, __FUNCTION__ ); }

   /// vége van-e a futtatásnak
   function runOver() {
      return self::$over;
   }

   /// view készítés
   function viewCreate( View $v ) {
      throw new EVygu("Cannot create view: ".$v->kind() );
   }

   /// stílus készítés
   function styleCreate( Style $s ) {
      throw new EVygu("Cannot create style: ".$v->kind() );
   }

   /// view felszámolás
   function viewDestroy( View $v ) {
   }

   /// stílus felszámolás
   function styleDestroy( Style $s ) {
   }

   /// view property
   function viewProperty( View $v, $p, $x ) {
      throw new EVygu("Cannot access property: ".$v->kind($v).".$p");
   }

   /// view koordináta
   function viewCoord( View $v, $c, $x, & $tmp ) {
      throw new EVygu("Cannot access coord: ".$v->kind().".".Tools::str($c));
   }      

   /// view koordináták
   function viewCoords( View $v, array $cs, $x ) {
      $tmp = [];
      $ret = [];
      foreach ($cs as $k=>$c) {
         if ( null === $xx = Tools::g($x,$k))
            $xx = Tools::GET;
         $ret [] = $this->viewCoord( $v, $c, $xx, $tmp );
      }
      $this->viewCoordLast($v,$tmp);
      return $ret;
   }      

   /// insert művelet
   function viewInsert( View $v, $at, $x ) {
      throw new EVygu("Cannot insert ".Tools::type($x)." to ".$v->kind() );
   }

   /// view handler beállítás
   function viewHandler(View $v, $e, ?Handler $o, ?Handler $h) {
      throw new EVygu("Cannot set handler: ".get_class($v).".$e");
   }

   /// ellenőrzés, hogy nem üres-e
   protected function check( $x, $err ) {
      if ( ! $x || Tools::cIsNull($x))
         throw new EVygu($err);
      return $x;
   }

   /// utolsó viewcoord
   protected function viewCoordLast(View $v, $tmp) {
   }

}
