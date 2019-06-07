<?php

class MugoPurgeTrash extends MugoTask
{

	public function create( $parameters )
	{
		$return = array();		

		$fetchParameters = array( 
		                          'AsObject' => false,
		                          'SortBy'   => array( 'name', true ),
		                          'AttributeFilter' => false,
		                          'Limitation' => array()
		                        );
				
		if( isset( $parameters[ 'limit' ] ) )
		{
			$fetchParameters[ 'Limit' ] = $parameters[ 'limit' ];
		}
		
		// Age Filter
		$currentTime = time();
		$ageFilter = 0;
		if( isset( $parameters[ 'age' ] ) )
		{
			$ageFilter = $parameters[ 'age' ];
		}
		
		$trashList = eZContentObjectTrashNode::trashList( $fetchParameters, false );

		if( !empty( $trashList ) )
		{
			foreach( $trashList as $trashRow )
			{
				if( $trashRow[ 'modified' ] + $ageFilter < $currentTime )
				{
					$return[] = $trashRow[ 'id' ];
				}
			}
		}
		
		return $return;
	}
	
	public function execute( $task_id, $parameters = null )
	{
		$success = false;
		
		if( (int) $task_id )
		{
			$trashObject = eZContentObject::fetch( $task_id );
			
			if( $trashObject instanceof eZContentObject )
			{
				$status = $trashObject->attribute( 'status' );
				
				// Double check if object is still in Trash
				if( $status == 2 )
				{
					$trashObject->purge();
				}
				else
				{
					$this->log( 'Object not in trash anymore ( ' . $task_id . ' ). Dropping it.' );
					$success = true;
				}
				// method purge always returns NULL...
				$success = true;
			}
			else
			{
				//was removed already
				$this->log( 'Could not find content object ( ' . $task_id . ' ). Dropping it.' );
				$success = true;
			}		
		}
		
		return $success;
	}
}
