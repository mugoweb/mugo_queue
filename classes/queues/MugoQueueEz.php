 <?php

class MugoQueueEz extends MugoQueue
{
	protected $batchSize = 100;

	public function add_tasks( $task_type_id, $task_ids )
	{
		$db = eZDB::instance();
		$db->begin();

		// Batch handling of INSERTs for best performance
		$sql_inserts = array();
		foreach( $task_ids as $index => $id )
		{
			if( $id !== '' )
			{
				$sql_inserts[] = '( "'. $db->escapeString( $task_type_id ) .'", '. time() . ', "'. $db->escapeString( $id ) . '")';
			}
			
			if( !( count( $sql_inserts ) % $this->batchSize ) )
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

		$db->commit();
	}

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
			$sql_where .= 'action = "'. $db->escapeString( $task_type_id ) .'" AND ';
		}
		$sql_where .= '1 ';
		
		$sql .= $sql_where . $sql_limit;

		$result = $db->arrayQuery( $sql );
		
		if( !empty( $result ) )
		{
			//TODO: put it back to just return a list of task ids
			$return = $result;
		}
		
		return $return;
	}
	
	public function remove_tasks( $task_type_id = null, $taskIds = null )
	{
		$db = eZDB::instance();
		$db->begin();

		$sql_where = 'WHERE ';
		if( $task_type_id )
		{
			$sql_where .= 'action = "'. $db->escapeString( $task_type_id ) .'" AND ';

			if( !empty( $taskIds ) )
			{
				foreach( $taskIds as $index => $taskId )
				{
					if( is_string( $taskId ) )
					{
						$taskIds[ $index ] = '"'. $db->escapeString( $taskId ) .'"';
					}
				}

				$sql_where .= 'param IN ('. implode( ',', $taskIds ) . ') AND ';
			}
		}

		$sql_where .= '1 ';
		
		$sql = 'DELETE FROM ezpending_actions ' . $sql_where;
		//echo $sql;

		$db->query( $sql );
		$db->commit();
	}
	
	public function get_tasks_count( $task_type_id = null )
	{
		$return = false;
		
		$db = eZDB::instance();
				
		$sql = 'SELECT count(*) AS total FROM ezpending_actions ';

		$sql_where = 'WHERE ';
		if( $task_type_id )
		{
			$sql_where .= 'action = "'. $db->escapeString( $task_type_id ) .'" AND ';
		}
		$sql_where .= '1 ';

		$sql .= $sql_where;

		$result = $db->arrayQuery( $sql );

		if( !empty( $result ) )
		{
			$return = $result[0][ 'total' ];
		}
		
		return $return;
	}
	
	public function get_random_tasks()
	{
		$return = array();
		
		$db = eZDB::instance();
		
		$total = $this->get_tasks_count();
		$rand_offset = floor( $total * ( rand(0, 999) / 1000 ) );
		
		$sql = 'SELECT SUBSTR( action, 12 ) AS type, param AS id FROM ezpending_actions LIMIT '. $rand_offset . ', 1';
		
		//echo $sql;
		
		$result = $db->arrayQuery( $sql );
		
		if( !empty( $result ) )
		{
			$return = $result;
		}
		
		return $return;
	}
	
}
