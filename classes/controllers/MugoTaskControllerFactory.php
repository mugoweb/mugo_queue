<?php

class MugoTaskControllerFactory
{
	/**
	 * @param string $className
	 * @param MugoQueue|null $mugoQueue
	 * @param MugoTask|null $mugoTask
	 * @return MugoTaskController|null
	 */
	static public function factory(
		$className,
		MugoQueue $mugoQueue = null,
		MugoTask $mugoTask = null
	)
	{
		$return = null;
		
		if( class_exists( $className ) )
		{
			$return = new $className( $mugoQueue, $mugoTask );
		}
		else
		{
			$return = new MugoTaskController( $mugoQueue, $mugoTask );
		}
		
		return $return;
	}
}
