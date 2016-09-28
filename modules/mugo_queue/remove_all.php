<?php

$errorMessage = '';

if( $_REQUEST[ 'task_type_id' ] )
{
	/** @var MugoQueueEz $mugoQueue */
	$mugoQueue = MugoQueueFactory::factory( 'MugoQueueEz' );
	$mugoTask = MugoTask::factory( $_REQUEST[ 'task_type_id' ] );

	if( $mugoTask )
	{
		$success = $mugoQueue->remove_tasks(
			$mugoTask->getQueueIdentifier()
		);
	}
	else
	{
		$errorMessage = 'Could not get an instance for the MugoTask.';
	}
}
else
{
	$errorMessage = 'Missing input parameters.';
}

echo $errorMessage;
eZExecution::cleanExit();
