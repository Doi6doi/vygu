<?php

namespace vygu;

/// An event handler.
/// Internal use only, you should use callables in your applications
class Handler {

   /// The [View] which owns the handler
   public $view;
   /// The callable
   public $cb;
   /// Additional data
   public $data;

}
