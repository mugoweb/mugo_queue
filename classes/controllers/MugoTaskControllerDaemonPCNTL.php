<?php

declare(ticks = 1);

class MugoTaskControllerDaemonPCNTL extends MugoTaskController
{
	function __construct()
	{
		pcntl_signal( SIGTERM, 'MugoTaskControllerDaemonPCNTL::daemonSignalHandler' );
		pcntl_signal( SIGINT,  'MugoTaskControllerDaemonPCNTL::daemonSignalHandler' );
	}
		
	public function execute( $parameters = null, $limit = 0 )
	{
		$tasks  = $this->mugoQueue->get_random_tasks();
			
		if( ! empty( $tasks ) )
		{
			$task = $tasks[ 0 ];
			
			$mugo_task = MugoTask::factory( $task[ 'type' ] );
			
			if( $mugo_task instanceof MugoTask )
			{
				$success = $mugo_task->execute( $task[ 'id' ], null );
				
				if( $success )
				{
					$this->mugoQueue->remove_tasks( $task[ 'type' ], array( $task[ 'id' ] ) );
					$mugo_task->post_execute();
					
					$this->log( 'Task "' . get_class( $mugo_task ) . '" with ID ' . $task[ 'id' ] . ' executed.' );
				}
				else
				{
					$this->log( 'Task "' . get_class( $mugo_task ) . '" with ID ' . $task[ 'id' ] . ' execution faild.' );
				}
				
				//oom
				unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
				unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
				unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );
				unset( $GLOBALS[ 'eZTemplateInstance' ] );
			}
		}
	}
	
	public static function daemonSignalHandler( $signo )
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
	
}
?>