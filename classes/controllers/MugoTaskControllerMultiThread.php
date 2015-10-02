<?php
class MugoTaskControllerMultiThread extends MugoTaskController
{
	protected $pool = array();
	protected $pool_size = 2;
		
    public function execute( $parameters = null, $limit = 0 )
	{
		if( $this->mugo_task instanceof MugoTaskMultiThread )
		{
			$task_ids = $this->get_task_ids( $limit );

			if( !empty( $task_ids ) )
			{
				$i = 0;
				$batch_size = $this->mugo_task->get_batch_size();

				while( true )
				{
					// Stop forking and waiting for a client to finish
					if( count( $this->pool ) >= $this->pool_size )
					{
						$pid = pcntl_wait( $status );
						$this->finish_task( $pid );
					}

					if( $i < count( $task_ids ) )
					{
						$mugo_task_thread = clone $this->mugo_task;

						$taskIdsBatch = array_slice( $task_ids, $i, $batch_size );

						$i += $batch_size;

						$mugo_task_thread->init( $taskIdsBatch, $parameters );

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
		else
		{
			$this->log( 'Task is not a child of MugoTaskMultiThread' );
		}
	}
    
	protected function finish_task( $pid )
	{
		$mugo_task = $this->pool[ $pid ];

		$task_ids = $mugo_task->get_task_ids();
		$this->mugo_task->post_thread_execute();
		
		$this->mugoQueue->remove_tasks(
			$this->mugo_task->getQueueIdentifier(),
			$task_ids
		);

		$this->log( 'Batch execution finished. PID: ' . $pid );
		unset( $this->pool[ $pid ] );
    }

	public function setPoolSize( $size )
	{
		$this->pool_size = $size;
	}
    
    private function get_task_ids( $limit )
    {
		$return = array();
    	
		$tasks = $this->mugoQueue->get_tasks(
			$this->mugo_task->getQueueIdentifier(),
			$limit
		);
		foreach( $tasks as $task )
		{
			$return[] = $task[ 'id' ];
		}
    	
		return $return;
    }
}
