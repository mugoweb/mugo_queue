<?php

class MugoRemoveArchivedVersions extends MugoTask
{

	protected $versionStatuses = array(
		eZContentObjectVersion::STATUS_DRAFT,
		eZContentObjectVersion::STATUS_PENDING,
		eZContentObjectVersion::STATUS_REJECTED,
		eZContentObjectVersion::STATUS_ARCHIVED,
		eZContentObjectVersion::STATUS_INTERNAL_DRAFT,
	);

	public function create( $parameters )
	{
		$return = array();		

		$fetchParameters = array(
			'status' => array( $this->versionStatuses ),
		);
				
		// Age Filter
		if( isset( $parameters[ 'age' ] ) )
		{
			$cutOff = time() - $parameters[ 'age' ];
			$fetchParameters[ 'modified' ] = array( '<', $cutOff );
		}

		$versions = eZPersistentObject::fetchObjectList(
			eZContentObjectVersion::definition(),
			null,
			$fetchParameters,
			null,
			null,
			false
		);
		
		if( !empty( $versions ) )
		{
			foreach( $versions as $row )
			{
				$return[] = $row[ 'id' ];
			}
		}
		
		return $return;
	}
	
	public function execute( $task_id, $parameters )
	{
		$success = false;
		
		if( (int) $task_id )
		{
			$version = eZContentObjectVersion::fetch( $task_id );
 
			if( $version instanceof eZContentObjectVersion)
			{
				$version->removeThis();
				
				// method removeThis always returns NULL...
				$success = true;
			}
			else
			{
				// version doesn't exist anymore?
				$success = true;
			}
		}
		
		return $success;
	}
}
