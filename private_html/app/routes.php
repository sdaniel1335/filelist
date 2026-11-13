<?php
return array(
  'GET' => array(
    '/' => array('controller'=>'files','class'=>'files','action'=>'index'),
    '/download' => array('controller'=>'files','class'=>'files','action'=>'download'),
    '/login' => array('controller'=>'auth','class'=>'auth','action'=>'login'),
    '/password' => array('controller'=>'auth','class'=>'auth','action'=>'password')
  ),
  'POST' => array(
    '/upload' => array('controller'=>'files','class'=>'files','action'=>'upload'),
    '/rename' => array('controller'=>'files','class'=>'files','action'=>'rename'),
    '/delete' => array('controller'=>'files','class'=>'files','action'=>'delete'),
    '/login' => array('controller'=>'auth','class'=>'auth','action'=>'login_post'),
    '/logout' => array('controller'=>'auth','class'=>'auth','action'=>'logout'),
    '/password' => array('controller'=>'auth','class'=>'auth','action'=>'password_post')
  )
);