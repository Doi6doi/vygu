<?php

namespace vygu;

/// The cardinal class for a vygu application.
/// It is a singleton, which loads an appropriate
/// engine on first use.
class Vygu {

   const
      /// The engine argument for `ins()`
      ENGINE = "engine",
      OVER = "over";

   /// Get singleton value
   /// \param $args (`ENGINE`: [Gtk4]`::GTK4` | [WinApi]::`WINAPI )
   /// \return The singleton
   static function ins( $args=[] ) {
      if ( ! self::$ins )
         self::$ins = self::create( $args );
      return self::$ins;
   }

   // singleton változó
   protected static $ins;
   // hashek
   protected $maps;
   // kilépő bool
   protected $over;

   // új vygu készítés
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

   function __construct($args) {
      $this->maps = [];
   }

   /// Run the even loop until termination
   function run() {
      while (! $this->runOver()) {
         $this->runStep( true );
      }
   }

   /// Do a single step on the event loop
   /// \param $wait Wait for at least one event to happen (bool)
   /// \return Has there been any events (bool)
   function runStep( $wait ) { Tools::notImpl( $this, __FUNCTION__ ); }

   // view fókuszálása
   function viewFocus(View $v) {
      throw new EVygu("Cannot focus ".$v->kind());
   }

   // képernyő létrehozása
   function screenCreate( Screen $s ) {
   }

   // handler hívása
   function handlerCall( Handler $h, array $args ) {
	  return call_user_func_array( $h->cb, $args );
   }

   // hozzáadás menühöz
   function menuAdd(Menu $m, $x) {
      throw new EVygu("Cannot add ".Tools::type($x)." to menu");
   }

   // view újrarajzolása szükséges
   function viewInvalidate(View $v) {
	   throw new EVygu("Cannot invalidate ".$v->kind());
   }

   // view szülőjének beállítása
   function viewParent( View $v, ?Group $g ) {
      throw new EVygu("Cannot set ".$v->kind()." parent to ".Tools::str($g));
   }

   // handler készítés
   function handlerCreate(Elem $v, $e, callable $cb) {
      $ret = new Handler();
      $ret->elem = $v;
      $ret->cb = $cb;
      return $ret;
   }

   // képernyő koordináta lekérdezés
   function screenCoord( Screen $s, $c ) {
	  throw new EVygu("Cannot get screen coord: $c");
   }

   // vége van-e a futtatásnak
   function runOver() {
      return $this->over;
   }

   // futtatás befejezése
   function finish() {
	   $this->over = true;
   }

   // elem készítés
   function elemCreate( Elem $v ) {
      throw new EVygu("Cannot create elem: ".$v->kind() );
   }

   // stílus készítés
   function styleCreate( Style $s ) {
      throw new EVygu("Cannot create style: ".$v->kind() );
   }

   // elem felszámolás
   function elemDestroy( Elem $v ) {
   }

   // stílus felszámolás
   function styleDestroy( Style $s ) {
   }

   // view property
   function elemProperty( Elem $v, $p, $x ) {
      throw new EVygu("Cannot access property: ".$v->kind($v).".$p");
   }

   // view koordináta
   function viewCoord( View $v, $c, $x, & $tmp ) {
      throw new EVygu("Cannot access coord: ".$v->kind().".".Tools::str($c));
   }

   // view koordináták
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

   // insert művelet
   function viewInsert( View $v, $at, $x ) {
      throw new EVygu("Cannot insert ".Tools::type($x)." to ".$v->kind() );
   }

   // elem handler beállítás
   function elemHandler(Elem $v, $e, ?Handler $o, ?Handler $h) {
      throw new EVygu("Cannot set handler: ".get_class($v).".$e");
   }

   // ellenőrzés, hogy nem üres-e
   protected function check( $x, $err ) {
      if ( ! $x || Tools::cIsNull($x))
         throw new EVygu($err);
      return $x;
   }

   // utolsó viewcoord
   protected function viewCoordLast(View $v, $tmp) {
   }

   // vissza hash
   protected function map($name) {
      if ( ! $ret = Tools::g( $this->maps, $name )) {
		 $ret = $this->createMap( $name );
         $this->maps[$name] = $ret;
      }
      return $ret;
   }

   // vissza hash készítés
   protected function createMap( $name ) {
	  throw new EVygu("Unknown map: $name");
   }

}
