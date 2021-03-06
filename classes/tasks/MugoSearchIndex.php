<?php

class MugoSearchIndex extends MugoTaskMultiThread
{
	protected $batch_size = 500;

	protected $engine;
	
	public function __construct()
	{
		$this->engine = new eZSolr();
	}
	
	public function create( $parameters )
	{
		$return = array();
		
		$limit = $parameters[ 'limit' ] ? $parameters[ 'limit' ] : false;

		$node_rows = eZFunctionHandler::execute( 'content', 'tree', array(
			'parent_node_id'     => 1,
			'as_object'          => false,
			'limitation'         => array(),
			'ignore_visibility'  => true,
			'main_node_only'     => true,
			'limit'              => $limit
		) );
				
		if( !empty( $node_rows ) )
		{
			foreach( $node_rows as $row )
			{
				$return[] = $row[ 'id' ];
			}
		}

		return $return;
	}
	
	public function execute( $task_id, $parameters = null )
	{
		$success = false;
		
		$object = eZContentObject::fetch( $task_id );
		
		if( $object )
		{
			$success = $this->engine->addObject( $object, false );
		}
		
		return $success;
	}
	
	public function post_thread_execute()
	{
		$this->engine->commit();
	}
}
