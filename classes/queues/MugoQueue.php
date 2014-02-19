 <?php

/*
 * Wrapper around a queue solution
 */
class MugoQueue
{
	
	/**
	 * Enter description here ...
	 * 
	 * TODO: missing limit parameter handling
	 * 
	 * @param String $task_type_id
	 * @param array $task_ids
	 * @param unknown_type $limit
	 */
	public function add_tasks( $task_type_id, $task_ids, $limit )
	{
	}

	
	//TODO: put it back to just return a list of task ids
	public function get_tasks( $task_type_id = null, $limit = false )
	{
	}
	
	//TODO support to remove only one instance of duplicate task ids
	public function remove_tasks( $task_type_id, $object_ids = null )
	{
	}
	
	public function get_tasks_count( $task_type_id = null )
	{
	}
	
	public function get_random_tasks()
	{
	}
	
}

?>