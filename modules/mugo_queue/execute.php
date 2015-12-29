<?php

$errorMessage = '';

if( $_REQUEST[ 'task_type_id' ] && $_REQUEST[ 'task_id' ] )
{
	$mugoQueue = MugoQueueFactory::factory( 'MugoQueueEz' );
	$mugoTask = MugoTask::factory( $_REQUEST[ 'task_type_id' ] );

	if( $mugoTask )
	{
		$success = $mugoTask->execute( $_REQUEST[ 'task_id' ] );

		if( $success )
		{
			$mugoQueue->remove_tasks(
				$mugoTask->getQueueIdentifier(),
				array( $_REQUEST[ 'task_id' ] )
			);
		}
		else
		{
			$errorMessage = 'Failed to execute task';
		}
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
