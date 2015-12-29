<?php

class MugoQueueFactory
{
	static public function factory( $className = null )
	{
		$return = null;
		
		if( $className && class_exists( $className ) )
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
