<?php

/**
 * 
 */
class MugoTask
{
	/**
	 * @var string
	 */
	protected $log_destination;

	/**
	 * Identifier used for items in the queue. Must be unique. Falls back to class name.
	 *
	 * @var string
	 */
	protected $queueIdentifier;

	/**
	 * For larger creation processes, it's required to do it in batches
	 * MugoTask::create will recieve the limit and
	 * @var int
	 */
	protected $createBatchSize = 0;

	/**
	 * MugoTask constructor.
	 */
	public function __construct(){}

	/**
	 * Creates an array of task IDs that get added to the queue for a later execute.
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function create( $parameters )
	{
		return array();
	}
	
	/**
	 * Executes on single task id.
	 *
	 * @param string $task_id
	 * @param array $parameters
	 * @return boolean
	 */
	public function execute( $task_id, $parameters = null )
	{
		return true;
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
	public function pre_thread_execute() {}

	/**
	 * The controller calls it after it executed all tasks
	 */
	public function post_thread_execute() {}

	/**
	 * Helper function to log messages
	 * TODO: considert to forward the call to the controller -- it has the same log function
	 * 
	 * @param type $message
	 */
	protected function log( $message )
	{
		$logDestination = $this->log_destination ? $this->log_destination : ( get_class( $this ) . '.log' );

		$output = '[' . get_class( $this ) . '] ' . $message;
		eZLog::write( $output, $logDestination );
	}

	/**
	 * @return string
	 */
	public function getQueueIdentifier()
	{
		return $this->queueIdentifier ? $this->queueIdentifier : get_class( $this );
	}

	/**
	 * @return int
	 */
	public function getCreateBatchSize()
	{
		return $this->createBatchSize;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return get_class( $this );
	}

	/**
	 * @param string $taskTypeId
	 * @return null|MugoTask
	 */
	public static function factory( $taskTypeId )
	{
		$instance = null;

		// resolve to class name
		$settings = eZINI::instance( 'mugo_queue.ini' );
		$map = $settings->variable( 'General', 'TaskTypeIdToClassMap' );

		$className = isset( $map[ $taskTypeId ] ) ? $map[ $taskTypeId ] : $taskTypeId;

		// Try to get an instance of the class
		if( class_exists( $className ) )
		{
			$instance = new $className;

			if( !( $instance instanceof MugoTask ) )
			{
				unset( $instance );
			}
		}

		return $instance;
	}

	public static function getAllTaskTypes()
	{
		$settings = eZINI::instance( 'mugo_queue.ini' );
		$map = $settings->variable( 'General', 'TaskTypeIdToClassMap' );

		return array_values( $map );
	}

}
