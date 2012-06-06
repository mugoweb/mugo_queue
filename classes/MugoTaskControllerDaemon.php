<?php
class MugoTaskControllerDaemon extends MugoTaskController
{
	function __construct()
	{}
		
	public function execute()
	{
		$tasks  = MugoQueue::get_random_tasks();
			
		if( ! empty( $tasks ) )
		{
			$task = $tasks[ 0 ];
			
			$mugo_task = $this->task_factory( $task[ 'type' ] );
			
			if( $mugo_task instanceof MugoTask )
			{
				$success = $mugo_task->execute( $task[ 'id' ], null );
				
				if( $success )
				{
					MugoQueue::remove_tasks( $task[ 'type' ], array( $task[ 'id' ] ) );
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
}
?>