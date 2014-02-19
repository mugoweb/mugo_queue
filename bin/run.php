<?php
/*
 * Call me like this:
 * 
 * php extension/mugo_queue/bin/run.php 
 * 
 * See help screen for more options.
 * 
 */
ini_set( 'memory_limit', '-1' );

require 'autoload.php';

if ( file_exists( "config.php" ) )
{
    require "config.php";
}

$params = new ezcConsoleInput();

$helpOption = new ezcConsoleOption( 'h', 'help' );
$helpOption->mandatory = false;
$helpOption->shorthelp = "Show help information";
$params->registerOption( $helpOption );

$action_option = new ezcConsoleOption( 'a', 'action', ezcConsoleInput::TYPE_STRING );
$action_option->mandatory = true;
$action_option->shorthelp = 'The action: create, execute, list, count, remove, remove_all';
$params->registerOption( $action_option );

$task_type_id_option = new ezcConsoleOption( 'k', 'task_type_id', ezcConsoleInput::TYPE_STRING );
$task_type_id_option->mandatory = true;
$task_type_id_option->shorthelp = 'Task ID string.';
$params->registerOption( $task_type_id_option );

$queueClass_option = new ezcConsoleOption( 'q', 'queue', ezcConsoleInput::TYPE_STRING );
$queueClass_option->mandatory = false;
$queueClass_option->shorthelp = 'Queue PHP class name.';
$queueClass_option->default = 'MugoQueueEz';
$params->registerOption( $queueClass_option );

$limit_option = new ezcConsoleOption( 'l', 'limit', ezcConsoleInput::TYPE_INT );
$limit_option->mandatory = false;
$limit_option->shorthelp = 'Task execution limit.';
$params->registerOption( $limit_option );

$options_option = new ezcConsoleOption( 'o', 'options', ezcConsoleInput::TYPE_STRING );
$options_option->mandatory = false;
$options_option->shorthelp = 'Options string (For example -o foo=bar,foo2=bar2 ).';
$params->registerOption( $options_option );

$threads_option = new ezcConsoleOption( 't', 'threads', ezcConsoleInput::TYPE_INT );
$threads_option->mandatory = false;
$threads_option->shorthelp = 'Number of threads. Default is a single thread - the script itself.';
$threads_option->default = 1;
$params->registerOption( $threads_option );

$siteaccess_option = new ezcConsoleOption( 's', 'siteaccess', ezcConsoleInput::TYPE_STRING );
$siteaccess_option->mandatory = false;
$siteaccess_option->shorthelp = "The siteaccess name. Not yet supported";
$params->registerOption( $siteaccess_option );

$user_option = new ezcConsoleOption( 'u', 'user', ezcConsoleInput::TYPE_STRING );
$user_option->mandatory = false;
$user_option->shorthelp = 'Specify a user context. Handy if the task needs specific user permissions';
$params->registerOption( $user_option );

// Process console parameters
try
{
    $params->process();
}
catch ( ezcConsoleOptionException $e )
{
	echo $e->getMessage(). "\n";
	echo "\n";
	echo $params->getHelpText( 'Mugo Queue shell dispatcher script.' ) . "\n";
    echo "\n";
    exit();
}

#################
#  Setting up env
#################
// Init an eZ Publish script - needed for some API function calls
// and a siteaccess switcher
$ezp_script_env = eZScript::instance( array( 'debug-message' => '',
                                             'use-session' => true,
                                             'use-modules' => true,
                                             'use-extensions' => true ) );

$ezp_script_env->startup();

// Set siteaccess
if( $siteaccess_option->value )
{
	$ezp_script_env->setUseSiteAccess( $siteaccess_option->value );
}

$ezp_script_env->initialize();

if( $user_option->value )
{
	$user = eZUser::fetchByName( $user_option->value );
	
	if( $user )
	{
		$userID = $user->attribute( 'contentobject_id' );
		eZUser::setCurrentlyLoggedInUser( $user, $userID );
	}
	else
	{
		echo 'Unkown user specified.' . "\n";
		die();
	}	
}

####################
# Script process
####################

$start_time = time();
$is_quiet   = false;
$cli        = eZCLI::instance();

// arguments
$action       = $action_option->value;
$task_type_id = $task_type_id_option->value;
$limit        = (int) $limit_option->value;
$options_str  = $options_option->value;

// Build parameter array from given string
$parameters = null;
if( $options_str )
{
	$options_parts = explode( ',', $options_str );
	
	if( !empty( $options_parts ) )
	{
		foreach( $options_parts as $part )
		{
			$i_operation = explode( '=', $part );
			
			if( !empty( $i_operation) && count( $i_operation ) == 2 )
			{
				$parameters[ $i_operation[0] ] = $i_operation[1];
			}
		}
	}
}

if ( !$is_quiet ) $cli->output( '== INIT ==' );
if ( !$is_quiet ) $cli->output( 'Action: '. $action . ' Task Type Id: ' . $task_type_id . ' Limit: ' . $limit . ' Parameter Count: ' . count( $parameters ) );

$mugoQueue = MugoQueueFactory::factory( $queueClass_option->value );

switch( $action )
{
	case 'create':
	{
		$task_controller = new MugoTaskController( $mugoQueue );
		$task_controller->create( $task_type_id, $parameters, $limit );
	}
	break;

	case 'execute':
	{
		if( $threads_option->value > 1 )
		{
			$task_controller = new MugoTaskControllerMultiThread( $mugoQueue );
			$task_controller->setPoolSize( $threads_option->value );
		}
		else
		{
			$task_controller = new MugoTaskController( $mugoQueue );
		}
		$task_controller->execute( $task_type_id, $parameters, $limit );
	}
	break;
	
	case 'list':
	{
		$tasks = $mugoQueue->get_tasks( $task_type_id, $limit );
		
		foreach( $tasks as $index => $task )
		{
			if ( !$is_quiet ) $cli->output( ( $index + 1 ) . ') ' . $task[ 'type' ] . ': ' . $task[ 'id' ] );
		}
	}
	break;

    case 'count':
    {
        $task_count = $mugoQueue->get_tasks_count( $task_type_id );
        $cli->output( $task_count );
    }
    break;

	case 'remove':
	{
		$mugoQueue->remove_tasks( $task_type_id );
	}
	break;

	case 'remove_all':
	{
		$mugoQueue->remove_tasks();
	}
	break;
	
	default:
	{}
}

$end_time = time();

if ( !$is_quiet ) $cli->output( 'Total run time: ' . gmdate("H:i:s", ( $end_time - $start_time ) ) );

$ezp_script_env->shutdown();

?>