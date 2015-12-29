<?php

$errorMessage = '';

if( $_REQUEST[ 'task_type_id' ] && $_REQUEST[ 'task_id' ] )
{
	$mugoQueue = MugoQueueFactory::factory( 'MugoQueueEz' );
	$mugoTask = MugoTask::factory( $_REQUEST[ 'task_type_id' ] );

	if( $mugoTask )
	{
		$success = $mugoQueue->add_tasks( $_REQUEST[ 'task_type_id' ], array( $_REQUEST[ 'task_id' ] ) );

		if( $success )
		{

		}
		else
		{
			$errorMessage = 'Failed to add task to queue';
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
