<?php
class MugoTaskMultiThread extends MugoTask
{
	protected $pid;
	protected $ppid;
	
	protected $task_ids;
	protected $parameters;
	protected $batch_size = 50;
	
	private $script;
	
	function fork()
	{
		$pid = pcntl_fork();

		if ( $pid == -1 )
		{
			throw new Exception ( 'fork error' );
		}
		elseif ( $pid )
		{
			# we are in parent class
			
			// reset DB connection
			$db = eZDB::instance();
			$db->IsConnected = false;
			$db = null;
			eZDB::setInstance( $db );
			$db = eZDB::instance();
			
			$this->pid = $pid;
			return $pid;
		}
		else
		{
			# we are the child process
			
			// reset DB connection
			$db = eZDB::instance();
			$db->IsConnected = false;
			$db = null;
			eZDB::setInstance( $db );
			$db = eZDB::instance();
			
			// set script env
			$this->script = eZScript::instance( array( 'debug-message' => '',
			                                             'use-session' => true,
			                                             'use-modules' => true,
			                                             'use-extensions' => true ) );
			
			$this->script->startup();			
			$this->script->initialize();
			
			if( !empty( $this->task_ids) )
			{
				foreach( $this->task_ids as $task_id )
				{
					$this->execute( $task_id, $this->parameters );
				}
			}

			$this->script->shutdown();
			exit(0);
		}
	}
	
	public function init( $task_ids, $parameters )
	{
		$this->task_ids   = $task_ids;
		$this->parameters = $parameters;
	}
	
	public function get_task_ids()
	{
		return $this->task_ids;
	}
	
	public function get_batch_size()
	{
		return $this->batch_size;
	}
	
	public function post_thread_execute() {}
}
