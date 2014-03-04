<?php 

class MugoTaskController
{
	protected $mugo_task;

	/**
	 *
	 * @var string
	 */
	public $log_destination = 'mugo_queue_controller.log';
	public $mugoQueue;
	
	/*
	 * Directly adds a list of task ids to the queue
	 * TODO: check if the task_type_id is correct
	 */
	public function add( $task_type_id, $task_ids )
	{
		$this->mugoQueue->add_tasks( $task_type_id, $task_ids );		
	}
	
	/* 
	 * Uses a Task instance to get a list of task ids and add those to the queue
	 */
	public function create( $task_type_id, $parameters = null, $limit = 0 )
	{
		//TODO: consider to get the mugo_task instance in __construct()
		$mugo_task = MugoTaskController::task_factory( $task_type_id );
		
		$task_ids = $mugo_task->create( $parameters );
		
		$this->mugoQueue->add_tasks( $task_type_id, $task_ids, $limit );
	
		$this->log( count( $task_ids ) . ' task(s) created.' );
	}	

	public function execute( $task_type_id, $parameters = null, $limit = 0 )
	{
		$mugo_task = MugoTaskController::task_factory( $task_type_id );
		$tasks  = $this->mugoQueue->get_tasks( $task_type_id, $limit );
		
		if( !empty( $tasks ) )
		{
			$this->pre_execute();
			
			foreach( $tasks as $task )
			{
				$this->mugo_task->pre_execute();
				$success = $mugo_task->execute( $task[ 'id' ], $parameters );
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
				unset( $GLOBALS[ 'eZContentObjectContentObjectCache' ] );
				unset( $GLOBALS[ 'eZContentObjectDataMapCache' ] );
				unset( $GLOBALS[ 'eZContentObjectVersionCache' ] );
				unset( $GLOBALS[ 'eZTemplateInstance' ] );
			}
			
			$this->post_execute();
			
			$this->log( count( $task_ids ) . ' task(s) executed.' );
		}
		else
		{
			$this->log( 'no matching tasks queued' );
		}
	}
		
	public function remove( $task_type_id )
	{
		$this->log( 'Remove tasks' );
		
		$this->mugoQueue->remove_tasks( $task_type_id );
	}
	
	public static function task_factory( $task_type_id )
	{
		$instance = null;
		
		if( class_exists( $task_type_id ) )
		{
			$instance = new $task_type_id;
			
			if( !( $instance instanceof MugoTask ) )
			{
				unset( $instance );
			}
		}

		if( ! $instance )
		{
			//self::log( 'Cannot find Task class "'. $task_type_id .'"' );
			echo 'Cannot find Task class "'. $task_type_id .'"';
		}
		
		return $instance;
	}	

	protected function pre_execute()
	{
		$this->mugo_task->pre_controller_execute();
	}
	
	protected function post_execute()
	{
		$this->mugo_task->post_controller_execute();
	}
	
	protected function log( $message )
	{
		$output = '[' . get_class( $this ) . '] ' . $message;
		eZLog::write( $output, $this->log_destination );
	}
}
