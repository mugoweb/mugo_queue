<?php

/**
 * 
 */
class MugoTask
{

	/**
	 * TODO: consider to protect the var
	 * @var string
	 */
	public $log_destination = 'mugo_task.log';
	
	public function __construct(){}
	
	/*
	 * Creates an array of task IDs that get added to the queue for a later execute.
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

	/**
	 * The controller calls it for each task execution
	 */
	public function pre_execute() {}

	/**
	 * The controller calls it for each task execution
	 */
	public function post_execute() {}

	/**
	 * The controller calls it before it starts executing tasks
	 */
	public function pre_controller_execute() {}

	/**
	 * The controller calls it after it executed all tasks
	 */
	public function post_controller_execute() {}

	/**
	 * Helper function to log messages
	 * TODO: considert to forward the call to the controller -- it has the same log function
	 * 
	 * @param type $message
	 */
	protected function log( $message )
	{
		$output = '[' . get_class( $this ) . '] ' . $message;
		eZLog::write( $output, $this->log_destination );
	}
}
