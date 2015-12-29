<?php

class MugoIndexContent extends MugoSearchIndex
{
	protected $queueIdentifier = 'index_object';

	public function execute( $task_id, $parameters )
	{
		$success = false;
		
		$object = eZContentObject::fetch( $task_id );

		if( $object )
		{
			if( $object->attribute( 'status' ) == eZContentObject::STATUS_PUBLISHED )
			{
				$success = $this->engine->addObject( $object, false );
				eZContentCacheManager::clearContentCacheIfNeeded( $task_id );

				// CSM specific
				CSMFunctionCollection::clearViewCache( $object );
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
