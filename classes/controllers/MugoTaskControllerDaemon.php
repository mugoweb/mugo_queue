<?php
class MugoTaskControllerDaemon extends MugoTaskController
{
	function __construct()
	{}
	
	/**
	 * 
	 */
	public function execute( $parameters = null, $limit = 0 )
	{
		$tasks  = MugoQueue::get_random_tasks();
			
		if( ! empty( $tasks ) )
		{
			$task = $tasks[ 0 ];
			
			$mugo_task = MugoTask::factory( $task[ 'type' ] );
			
			if( $mugo_task instanceof MugoTask )
			{
				$mugo_task->pre_execute();
				$success = $mugo_task->execute( $task[ 'id' ], null );
				$mugo_task->post_execute();
				
				if( $success )
				{
					MugoQueue::remove_tasks( $task[ 'type' ], array( $task[ 'id' ] ) );
					
					$this->log( 'Task "' . get_class( $mugo_task ) . '" with ID ' . $task[ 'id' ] . ' executed.' );
				}
				else
				{
					$this->log( 'Task "' . get_class( $mugo_task ) . '" with ID ' . $task[ 'id' ] . ' execution failed.' );
				}
				
				//oom
				unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
				unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
				unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );
				unset( $GLOBALS[ 'eZTemplateInstance' ] );
			}
		}
	}	
}
