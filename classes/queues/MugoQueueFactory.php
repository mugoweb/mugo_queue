<?php

class MugoQueueFactory
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
			$return = new MugoQueueEz();
		}
		
		return $return;
	}
}

?>