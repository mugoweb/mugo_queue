<?php

$tpl = eZTemplate::factory();

$task_type_id = $_REQUEST[ 'task_type_id' ];
$offset = isset( $_REQUEST[ 'offset' ] ) ? (int) $_REQUEST[ 'offset' ] : 0;
$limit = 100;

if( $task_type_id )
{
	$task = MugoTask::factory( $task_type_id );

	if( $task )
	{
		// build task data
		$task_data = array(
			'name' => $task->getName(),
			'queue_identifier' => $task->getQueueIdentifier(),
		);

		$mugoQueue = MugoQueueFactory::factory( 'MugoQueueEz' );

		$tasks = $mugoQueue->get_tasks( $task->getQueueIdentifier(), $limit, $offset );
		$tasksCount = $mugoQueue->get_tasks_count( $task->getQueueIdentifier() );

		$tpl->setVariable( 'task_data', $task_data );
		$tpl->setVariable( 'tasks', $tasks );
		$tpl->setVariable( 'limit', $limit );
		$tpl->setVariable( 'tasks_count', $tasksCount );
		$tpl->setVariable( 'task_type_id', $_REQUEST[ 'task_type_id' ] );
	}
}

$Result[ 'content' ] = $tpl->fetch( 'design:modules/mugo_queue/tasks.tpl' );
$Result[ 'path' ] = array(
	array( 'url' => '/mugo_queue/home', 'text' => 'Mugo Queue Home' ),
	array( 'url' => false, 'text' => 'Task' ),
);
