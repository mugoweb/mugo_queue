<?php
/*
 * Call me like this:
 * 
 * php extension/mugo_queue/bin/daemon.php 
 * 
 */
declare( ticks = 1 );

require 'autoload.php';

if( file_exists( 'config.php' ) )
{
	require 'config.php';
}

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
$maxload_option->default = 1;
$params->registerOption( $maxload_option );

// Process console parameters
try
{
	$params->process();
}
catch ( ezcConsoleOptionException $e )
{
	echo $e->getMessage(). "\n";
	echo "\n";
	echo $params->getHelpText( 'Mugo Queue Daemon.' ) . "\n";
	echo "\n";
	exit();
}

/***************
 * Setup env
 ***************/
// Init an eZ Publish script - needed for some API function calls
// and a siteaccess switcher
$ezp_script_env = eZScript::instance( array( 'debug-message' => '',
                                             'use-session' => true,
                                             'use-modules' => true,
                                             'use-extensions' => true ) );

$ezp_script_env->startup();
$ezp_script_env->initialize();

pcntl_signal( SIGTERM, 'daemonSignalHandler' );
pcntl_signal( SIGINT,  'daemonSignalHandler' );

// The endless loop
$GLOBALS[ 'mugo_daemon' ][ 'running' ] = true;
$GLOBALS[ 'mugo_daemon' ][ 'force_quit' ] = false;

while( $GLOBALS[ 'mugo_daemon' ][ 'running' ] )
{
	if( ! is_too_busy() )
	{
		if( has_work() )
		{
			echo '+';
			$controller = new MugoTaskControllerDaemon();
			$controller->execute();
			
			sleep( 3 );
		}
		else
		{
			echo '0';
			sleep( 30 );
		}
	}
	else
	{
		echo '.';
		sleep( 60 );
	}
}

$ezp_script_env->shutdown();
echo 'Script done' . "\n";

/*
 * Functions
 */

/*
 * just checks the OS system load
 */
function is_too_busy()
{
	$load = sys_getloadavg();
	return $load[ 0 ] > 1;
}

function has_work()
{
	return MugoQueue::get_tasks_count() > 0;
}

function daemonSignalHandler( $signo )
{
	switch( $signo )
	{
		case SIGTERM:
		case SIGINT:
		{
			if( ! $GLOBALS[ 'mugo_daemon' ][ 'force_quit' ] )
			{
				$GLOBALS[ 'mugo_daemon' ][ 'running' ] = false;
				$GLOBALS[ 'mugo_daemon' ][ 'force_quit' ] = true;
				echo 'Please wait for script to terminate... ';
			}
			else
			{
				exit(1);
			}
		}
		break;
	}
}

?>