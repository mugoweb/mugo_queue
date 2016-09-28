<?php
$tpl = eZTemplate::factory();

$mugoQueue = MugoQueueFactory::factory( 'MugoQueueEz' );

$entries = $mugoQueue->getTaskTypeIdsWithCounts();

$tasksData = array();
foreach( $entries as $entry )
{
	$task = MugoTask::factory( $entry[ 'task_type_id' ] );

	if( $task )
	{
		$tasksData[ $task->getName() ] = array(
			'name' => $task->getName(),
			'count' => $entry[ 'total' ],
		);
	}
}

// Add other tasks
foreach( MugoTask::getAllTaskTypes() as $name )
{
	if( !isset( $tasksData[ $name ] ) )
	{
		$tasksData[ $name ] = array(
			'name' => $name,
			'count' => 0,
		);
	}
}

ksort( $tasksData );

$tpl->setVariable( 'tasks_data', $tasksData );

$Result[ 'content' ] = $tpl->fetch( 'design:modules/mugo_queue/home.tpl' );
$Result[ 'left_menu' ]  = 'design:mugo_system_tools/left_menu.tpl';
$Result[ 'path' ] = array(
	array( 'url' => '/mugo_queue/home', 'text' => 'Mugo Queue Home' ),
);
