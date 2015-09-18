<?php

/**
 * Class MugoTaskController
 */
class MugoTaskController
{
	/**
	 * @var MugoTask
	 */
	protected $mugo_task;

	/**
	 *
	 * @var MugoQueue
	 */
	public $mugoQueue;

	/**
	 * @var string
	 */
	protected $log_destination;

	public function __construct( $queueHandler )
	{
		$this->mugoQueue = $queueHandler;
	}

	/**
	 * Uses a Task instance to get a list of task ids and add those to the queue
	 *
	 * @param $task_type_id
	 * @param null $parameters
	 * @param int $limit
	 */
	public function create( $task_type_id, $parameters = null, $limit = 0 )
	{
		//TODO: consider to get the mugo_task instance in __construct()

		$mugo_task = MugoTask::factory( $task_type_id );
		
		$task_ids = $mugo_task->create( $parameters );

		// Limit handling
		$limit = (int)$limit;
		if( $limit > 0 && $limit < count( $task_ids ) )
		{
			$task_ids = array_slice( $task_ids, 0, $limit );
		}

		if( !empty( $task_ids ) )
		{
			$this->mugoQueue->add_tasks( $task_type_id, $task_ids );
		}

		$this->log( count( $task_ids ) . ' task(s) created.' );
	}

	/**
	 * @param $task_type_id
	 * @param null $parameters
	 * @param int $limit
	 */
	public function execute( $task_type_id, $parameters = null, $limit = 0 )
	{
		$this->mugo_task = MugoTask::factory( $task_type_id );
		$tasks = $this->mugoQueue->get_tasks( $task_type_id, $limit );
		
		if( !empty( $tasks ) )
		{
			$this->pre_execute();
			
			foreach( $tasks as $task )
			{
				$this->mugo_task->pre_execute();
				$success = $this->mugo_task->execute( $task[ 'id' ], $parameters );
				$this->mugo_task->post_execute();
				
				if( $success )
				{
					$this->mugoQueue->remove_tasks( $task_type_id, array( $task[ 'id' ] ) );
				}
				else
				{
					$this->log( 'Failed to execute task: ' . $task[ 'id' ] );
				}

				//oom
				eZContentObject::clearCache();
				unset( $GLOBALS[ 'eZTemplateInstance' ] );
			}
			
			$this->post_execute();
			
			//$this->log( count( $task_ids ) . ' task(s) executed.' );
		}
		else
		{
			$this->log( 'no matching tasks found in queued' );
		}
	}

	/**
	 * @param string $task_type_id
	 * @return boolean
	 */
	public function remove( $task_type_id )
	{
		$this->log( 'Remove tasks' );
		
		return $this->mugoQueue->remove_tasks( $task_type_id );
	}

	/**
	 *
	 */
	protected function pre_execute()
	{
		$this->mugo_task->pre_controller_execute();
	}

	/**
	 *
	 */
	protected function post_execute()
	{
		$this->mugo_task->post_controller_execute();
	}

	/**
	 * @param $message
	 */
	protected function log( $message )
	{
		$logDestination = $this->log_destination ? $this->log_destination : ( get_class( $this ) . '.log' );
		$output = '[' . get_class( $this ) . '] ' . $message;
		eZLog::write( $output, $logDestination );
	}
}
