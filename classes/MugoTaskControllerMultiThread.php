<?php
class MugoTaskControllerMultiThread extends MugoTaskController
{
	protected $pool;
	protected $pool_size;

	function __construct( $pool_size = 2 )
	{
		$this->pool_size  = $pool_size;
		$this->pool = array();
	}
		
    public function execute( $task_type_id, $parameters = null, $limit = 0 )
	{
		$mugo_task = $this->task_factory( $task_type_id );
		$task_ids = $this->get_task_ids( $task_type_id, $limit );
		
		if( !empty( $task_ids ) && $mugo_task instanceof MugoTaskMultiThread )
		{
			$i = 0;
			$batch_size = $mugo_task->get_batch_size();
			
			while( 1 )
			{
				// Stop forking and waiting for a client to finish
				if( count( $this->pool ) >= $this->pool_size )
				{
					$pid = pcntl_wait( $extra );
					$this->finish_task( $pid );
				}

				if( $i < count( $task_ids ) )
				{
					$mugo_task_thread = clone $mugo_task;
					
					$batch = array_slice( $task_ids, $i, $batch_size );

					$i += $batch_size;

					$mugo_task_thread->init( $batch, $parameters );
					
					$child_pid = $mugo_task_thread->fork();
					
					$this->pool[ $child_pid ] = $mugo_task_thread;
				}
				else
				{
					// Finishing remaining forks in pool
					$pid = pcntl_wait( $extra );

					if( $pid == -1 )
					{
						// pool is empty
						break;
					}
					
					$this->finish_task( $pid );
				}
			}
		}
		else
		{
			$this->log( 'no matching tasks queued or invalid task id' );
		}
	}
    
	function finish_task( $pid )
	{
		$mugo_task = $this->pool[ $pid ];

		$task_ids = $mugo_task->get_task_ids();
		$mugo_task->post_execute();
		
		MugoQueue::remove_tasks( get_class( $mugo_task ), $task_ids );

		$this->log( 'Batch execution finished. PID: ' . $pid );
		unset( $this->pool[ $pid ] );
    }

    private function get_task_ids( $task_type_id, $limit )
    {
    	$return = array();
    	
    	$tasks = MugoQueue::get_tasks( $task_type_id, $limit );
    	foreach( $tasks as $task )
    	{
    		$return[] = $task[ 'id' ];
    	}
    	
    	return $return;
    }
}
?>