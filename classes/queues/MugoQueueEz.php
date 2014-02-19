 <?php

/*
 * Wrapper around a queue solution
 */
class MugoQueueEz extends MugoQueue
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
		$db = eZDB::instance();
		
		// Limit handling
		$limit = (int)$limit;
		if( $limit > 0 && $limit < count( $task_ids ) )
		{
			$task_ids = array_slice( $task_ids, 0, $limit );
		}
		
		// Batch handling of INSERTs for best performance
		$sql_inserts = array();
		foreach( $task_ids as $index => $id )
		{
			if( $id !== '' )
			{
				$sql_inserts[] = '( "mugo-queue-'. $task_type_id .'", '. time() . ', '. $db->escapeString( $id ) . ')';
			}
			
			if( ( count( $sql_inserts ) % 1000 ) == 999 )
			{
				$sql = 'INSERT INTO ezpending_actions ( action, created, param ) VALUES ' . implode( ',',  $sql_inserts );
				$db->query( $sql );
				
				$sql_inserts = array();
			}
		}
		
		if( !empty( $sql_inserts ) )
		{
			$sql = 'INSERT INTO ezpending_actions ( action, created, param ) VALUES ' . implode( ',',  $sql_inserts );
			
			$db->query( $sql );
		}
	}

	
	//TODO: put it back to just return a list of task ids
	public function get_tasks( $task_type_id = null, $limit = false )
	{
		$return = array();
		
		$db = eZDB::instance();
		
		$sql = 'SELECT action AS type, param AS id FROM ezpending_actions ';
		
		$sql_limit = '';
		if( (int) $limit > 0 )
		{
			$sql_limit = 'LIMIT ' . $limit . ' ';
		}
		
		$sql_where = 'WHERE ';
		if( $task_type_id )
		{
			$sql_where .= 'action = "mugo-queue-' . $task_type_id . '" AND ';
		}
		else
		{
			$sql_where .= 'action LIKE "mugo-queue-%" AND ';
		}
		$sql_where .= '1 ';
		
		$sql .= $sql_where . $sql_limit;
		
		$result = $db->arrayQuery( $sql );
		
		if( !empty( $result ) )
		{
			$return = $result;
		}
		
		return $return;
	}
	
	//TODO support to remove only one instance of duplicate task ids
	public function remove_tasks( $task_type_id, $object_ids = null )
	{
		$db = eZDB::instance();

		$sql_where = 'WHERE ';
		if( $task_type_id )
		{
			$sql_where .= 'action = "mugo-queue-' . $task_type_id . '" AND ';

			if( !empty( $object_ids ) )
			{
				$paramInSQL = $db->generateSQLInStatement( $object_ids, 'param' );
				$sql_where .= $paramInSQL . ' AND ';
			}
		}

		$sql_where .= '1 ';
		
		$sql = 'DELETE FROM ezpending_actions ' . $sql_where;
		//echo $sql;
				
		$db->query( $sql );
	}
	
	public function get_tasks_count( $task_type_id = null )
	{
		$return = false;
		
		$db = eZDB::instance();
				
		$sql = 'SELECT count(*) AS total FROM ezpending_actions ';

		$sql_where = 'WHERE ';
		if( $task_type_id )
		{
			$sql_where .= 'action = "mugo-queue-' . $task_type_id . '" AND ';
		}
		else
		{
			$sql_where .= 'action LIKE "mugo-queue-%" AND ';
		}
		$sql_where .= '1 ';
		
		$result = $db->arrayQuery( $sql );
		
		if( !empty( $result ) )
		{
			$return = $result[0][ 'total'];
		}
		
		return $return;
	}
	
	public function get_random_tasks()
	{
		$return = array();
		
		$db = eZDB::instance();
		
		$total = self::get_tasks_count();
		$rand_offset = floor( $total * ( rand(0, 999) / 1000 ) );
		
		$sql = 'SELECT SUBSTR( action, 12 ) AS type, param AS id FROM ezpending_actions WHERE action LIKE "mugo-queue-%" LIMIT '. $rand_offset . ', 1';
		
		//echo $sql;
		
		$result = $db->arrayQuery( $sql );
		
		if( !empty( $result ) )
		{
			$return = $result;
		}
		
		return $return;
	}
	
}

?>