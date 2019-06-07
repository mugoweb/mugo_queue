<?php

class MugoIndexMovedNode extends MugoTask
{
	protected $queueIdentifier = 'index_moved_node';

	public function execute( $task_id, $parameters = null )
	{
		$success = false;

		$eZObject = eZContentObject::fetch( (int) $task_id );

		if( $eZObject )
		{
			if( $eZObject->attribute( 'status' ) == eZContentObject::STATUS_PUBLISHED )
			{
				$parentNode = $eZObject->attribute( 'main_node' );

				$newTaskIds = array( $parentNode->attribute( 'contentobject_id' ) );

				$fetchParams = array(
					'parent_node_id' => $parentNode->attribute( 'node_id' ),
					'as_object' => false,
					'limitation' => array(),
				);

				$result = eZFunctionHandler::execute( 'content', 'tree', $fetchParams );

				if( !empty( $result ) )
				{
					foreach( $result as $entry )
					{
						$newTaskIds[] = $entry[ 'id' ];
					}
				}

				$mugoQueue = MugoQueueFactory::factory();

				$success = $mugoQueue->add_tasks( 'index_object', $newTaskIds );
			}
			else
			{
				// Object moved to trash?
				$success = true;
			}
		}
		else
		{
			// Object removed?
			$success = true;
		}

		return $success;
	}
}
