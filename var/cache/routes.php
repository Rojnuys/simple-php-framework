<?php return array (
  '' => 
  array (
    0 => 
    array (
      'methods' => 
      array (
        0 => 'GET',
      ),
      'controller' => 'App\\Controllers\\HomeController',
      'action' => 'index',
    ),
  ),
  'profile' => 
  array (
    0 => 
    array (
      'methods' => 
      array (
        0 => 'GET',
      ),
      'controller' => 'App\\Controllers\\HomeController',
      'action' => 'profile',
    ),
  ),
  'shortener' => 
  array (
    0 => 
    array (
      'methods' => 
      array (
        0 => 'GET',
      ),
      'controller' => 'App\\Controllers\\UrlController',
      'action' => 'index',
    ),
  ),
  'shortener/create' => 
  array (
    0 => 
    array (
      'methods' => 
      array (
        0 => 'GET',
      ),
      'controller' => 'App\\Controllers\\UrlController',
      'action' => 'create',
    ),
  ),
  'shortener/encode' => 
  array (
    0 => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'controller' => 'App\\Controllers\\UrlController',
      'action' => 'encode',
    ),
  ),
  'shortener/decode/<code>' => 
  array (
    0 => 
    array (
      'methods' => 
      array (
        0 => 'GET',
      ),
      'controller' => 'App\\Controllers\\UrlController',
      'action' => 'decode',
    ),
  ),
);