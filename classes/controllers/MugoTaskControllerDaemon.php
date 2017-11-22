<?php
class MugoTaskControllerDaemon extends MugoTaskController
{
	protected $settings;

	public function __construct( MugoQueue $queueHandler, MugoTask $taskHandler )
	{
		parent::__construct( $queueHandler, $taskHandler );

		$this->settings = eZINI::instance( 'mugo_queue.ini' );
	}

	/**
	 * @param null $parameters
	 * @param int $limit
	 */
	public function execute( $parameters = null, $limit = 0 )
	{
		$taskIds = $this->mugoQueue->getRandomTaskIds(
			1,
			$this->settings->variable( 'Worker', 'SupportedTasks' )
		);

		if( ! empty( $taskIds ) )
		{
			$task = $taskIds[ 0 ];
			
			$mugo_task = MugoTask::factory( $task[ 'type' ] );
			
			if( $mugo_task instanceof MugoTask )
			{
				$mugo_task->pre_execute();
				$success = $mugo_task->execute( $task[ 'id' ], null );
				$mugo_task->post_execute();

				if( $success )
				{
					$this->mugoQueue->remove_tasks( $task[ 'type' ], array( $task[ 'id' ] ) );
					
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

	/**
	 * @return bool
	 */
	public function hasWork()
	{
		$tasksSummary = $this->mugoQueue->getTaskTypeIdsWithCounts(
			$this->settings->variable( 'Worker', 'SupportedTasks' )
		);

		foreach( $tasksSummary as $taskSummary )
		{
			if( $taskSummary[ 'total' ] > 0 )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * checks the OS system load
	 *
	 * @return bool
	 */
	public function isTooBusy( $maxLoad )
	{
		$load = sys_getloadavg();
		return $load[ 0 ] >= $maxLoad;
	}

}
