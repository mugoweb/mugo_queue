<?php

class MugoTaskDummy extends MugoTask
{
	public function __construct(){}
		
	public function create( $parameters )
	{
		return array( 1 );
	}
	
	public function execute( $task_id, $parameters = null )
	{
		// Dummy execution
		return true;
	}
}

?>