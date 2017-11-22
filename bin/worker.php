<?php

/*
 * Call me like this:
 * 
 * php extension/mugo_queue/bin/worker.php 
 * 
 */
require 'autoload.php';

$params = new ezcConsoleInput();

$helpOption = new ezcConsoleOption( 'h', 'help' );
$helpOption->mandatory = false;
$helpOption->shorthelp = "Show help information";
$params->registerOption( $helpOption );

#$threads_option = new ezcConsoleOption( 't', 'childthreads', ezcConsoleInput::TYPE_INT );
#$threads_option->mandatory = false;
#$threads_option->shorthelp = 'Number of child threads. Default is a single child thread.';
#$threads_option->default = 1;
#$params->registerOption( $threads_option );

$maxload_option = new ezcConsoleOption( 'm', 'maxload', ezcConsoleInput::TYPE_INT );
$maxload_option->mandatory = false;
$maxload_option->shorthelp = 'Limitation to a maximal load (uses 5min average load value).';
$maxload_option->default = 5;
$params->registerOption( $maxload_option );

$verboseOption = new ezcConsoleOption( 'v', 'verbose', ezcConsoleInput::TYPE_NONE );
$verboseOption->mandatory = false;
$verboseOption->shorthelp = 'Echos verbose information to console.';
$verboseOption->default = false;
$params->registerOption( $verboseOption );

// Process console parameters
try
{
	$params->process();
}
catch ( ezcConsoleOptionException $e )
{
	echo $e->getMessage(). "\n";
	echo "\n";
	echo $params->getHelpText( 'Mugo Queue Worker.' ) . "\n";
	echo "\n";
	exit();
}

/***************
 * Setup env
 ***************/
// Init an eZ Publish script - needed for some API function calls
// and a siteaccess switcher
$ezp_script_env = eZScript::instance( array(
	'debug-message' => '',
	'use-session' => true,
	'use-modules' => true,
	'use-extensions' => true
) );

$ezp_script_env->startup();
$ezp_script_env->initialize();

$settings = eZINI::instance( 'mugo_queue.ini' );


$mugoQueue = MugoQueueFactory::factory();
$controller = MugoTaskControllerFactory::factory(
	'MugoTaskControllerDaemon',
	$mugoQueue,
	null
);

// The endless loop
$GLOBALS[ 'mugo_daemon' ][ 'running' ] = true;
$GLOBALS[ 'mugo_daemon' ][ 'force_quit' ] = false;

while( $GLOBALS[ 'mugo_daemon' ][ 'running' ] )
{
	if( ! $controller->isTooBusy( $maxload_option->value ) )
	{
		customDbPing();

		if( $controller->hasWork( $mugoQueue ) )
		{
			if( $verboseOption->value ) echo '+';
			$controller->execute();
			
			sleep( $settings->variable( 'Worker', 'HasWorkDelay' ) );
		}
		else
		{
			if( $verboseOption->value ) echo '0';
			sleep( $settings->variable( 'Worker', 'EmptyQueueDelay' ) );
		}
	}
	else
	{
		if( $verboseOption->value ) echo '.';
		sleep( $settings->variable( 'Worker', 'LoadToHighDelay' ) );
	}
}

$ezp_script_env->shutdown();
if( $verboseOption->value ) echo 'Script done' . "\n";


/**
 * mysqli_ping custom solution
 */
function customDbPing()
{
	$db = eZDB::instance();
	$db->query( 'SELECT * FROM ezpending_actions LIMIT 1' );

	if( $db->ErrorNumber == 2006 )
	{
		eZDB::setInstance( eZDB::instance( false, false, true ) );
	}
}