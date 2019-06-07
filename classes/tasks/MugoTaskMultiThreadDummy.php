<?php

class MugoTaskMultiThreadDummy extends MugoTaskMultiThread
{
	public function __construct(){}
		
	public function create( $parameters )
	{
		$return = array();
		
		for( $i = 0; $i < 1000; $i++ )
		{
			$return[] = $i;
		}
		return $return;
	}
	
	public function execute( $task_id, $parameters = null )
	{
		// Dummy execution
		return true;
	}
}

?>