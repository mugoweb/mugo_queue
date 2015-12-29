<?php
$Module = array( 'name' => 'mugo_queue' );

$ViewList = array();

$ViewList[ 'home' ] = array(
	'script' => 'home.php',
	'functions' => array( 'view' )
);

$ViewList[ 'list' ] = array(
	'script' => 'list.php',
	'functions' => array( 'view' )
);

$ViewList[ 'execute' ] = array(
	'script' => 'execute.php',
	'functions' => array( 'edit' )
);

$ViewList[ 'add' ] = array(
	'script' => 'add.php',
	'functions' => array( 'edit' )
);

$ViewList[ 'remove' ] = array(
	'script' => 'remove.php',
	'functions' => array( 'edit' )
);

$FunctionList = array();
$FunctionList[ 'view' ] = array();
$FunctionList[ 'edit' ] = array();
