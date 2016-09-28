<?php
$Module = array( 'name' => 'mugo_queue' );

$ViewList = array();

$ViewList[ 'home' ] = array(
	'default_navigation_part' => 'mugosystemtoolsnavigationpart',
	'script' => 'home.php',
	'functions' => array( 'view' )
);

$ViewList[ 'list' ] = array(
	'default_navigation_part' => 'mugosystemtoolsnavigationpart',
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
	'functions' => array( 'remove' )
);

$ViewList[ 'remove_all' ] = array(
	'script' => 'remove_all.php',
	'functions' => array( 'remove_all' )
);

$FunctionList = array();
$FunctionList[ 'view' ] = array();
$FunctionList[ 'edit' ] = array();
$FunctionList[ 'remove' ] = array();
$FunctionList[ 'remove_all' ] = array();
