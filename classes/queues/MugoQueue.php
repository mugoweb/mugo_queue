 <?php

/*
 * Wrapper around a queue solution
 */
class MugoQueue
{
	/**
	 *
	 * @param string $task_type_id
	 * @param array $task_ids
	 * @return bool
	 */
	public function add_tasks( $task_type_id, $task_ids )
	{
		return true;
	}

	/**
	 * @param null $task_type_id
	 * @param bool $limit
	 * @return array
	 */
	public function get_tasks( $task_type_id = null, $limit = false )
	{
		return array();
	}

	/**
	 * Either removes all task IDs for a given task type ID. Or only
	 * specific task IDs for a given task type ID.
	 *
	 * TODO: support to remove only one instance of duplicate task ids
	 *
	 * @param string|null $taskTypeId
	 * @param array|null $taskIds
	 * @return boolean
	 */
	public function remove_tasks( $taskTypeId = null, $taskIds = null )
	{
		return true;
	}

	/**
	 * @param string|null $task_type_id
	 * @return int
	 */
	public function get_tasks_count( $task_type_id = null )
	{
		return 0;
	}

	/**
	 *
	 * @return boolean|string
	 */
	public function get_random_tasks()
	{
		return false;
	}
	
}
