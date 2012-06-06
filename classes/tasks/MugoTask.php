<?php

class MugoTask
{

	public $log_destination = 'mugo_task.log';
		
	public function __construct(){}
	
	/*
	 * Creates an array of task IDs that get added to the queue for a later execute
	 */
	public function create( $parameters )
	{
		$task_ids = array();
		return $task_ids;
	}
	
	/*
	 * Executes on single task id.
	 */
	public function execute( $task_id, $parameters )
	{
		return array();
	}

	public function post_execute()
	{}

	protected function log( $message )
	{
		$output = '[' . get_class( $this ) . '] ' . $message;
		eZLog::write( $output, $this->log_destination );
	}
}

?>