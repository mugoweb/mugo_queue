<?php

class MugoTaskControllerFactory
{
	static public function factory( $className )
	{
		$return = null;
		
		if( class_exists( $className ) )
		{
			$return = new $className;
		}
		else
		{
			$return = new MugoTaskController();
		}
		
		return $return;
	}
}

?>