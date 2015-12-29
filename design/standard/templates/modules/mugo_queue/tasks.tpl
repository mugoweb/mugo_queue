{ezcss_require( 'mugo_queue.css' )}

{*
	INPUT:
	    $task_data      array of task attributes
		$tasks          list of ids
		$task_type_id   task identifier
*}

<h1>Tasks {$task_data.name|wash()}</h1>

<fieldset>
	<label style="display: inline">Queue Identifier:</label>
	<span>{$task_data.queue_identifier|wash()}</span>
</fieldset>

<h2>Add entry</h2>
<input type="text" id="taskid" value="" />
<input type="hidden" id="tasktypeid" value="{$task_data.queue_identifier|wash()}" />
<input type="submit" id="add" value="Submit" />

<br />
<br />

<table class="list">
	<thead>
		<tr>
			<th>Value</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		{if $tasks}
			{foreach $tasks as $task}
				<tr data-id="{$task.id|wash()}">
					<td>
						{$task.id|wash()}
					</td>
					<td >
						{* <a href={concat( '/mugo_queue/execute?task_type_id=', $task_type_id, '&task_id=', $task.id )}>Execute</a> *}
						<button disabled>View Context</button>
						<button class="remove">Remove</button>
						<button class="execute">Execute</button>
					</td>
				</tr>
			{/foreach}
		{else}
			<tr>
				<td colspan="2">Empty list</td>
			</tr>
		{/if}
	</tbody>
</table>

{include uri='design:includes/navigator.tpl' item_count=$tasks_count limit=$limit}

<script>
{literal}
$(function()
{
	var baseUrlAdd = {/literal}{'/mugo_queue/add'|ezurl()}{literal};
	var baseUrlRemove = {/literal}{'/mugo_queue/remove'|ezurl()}{literal};
	var baseUrlExecute = {/literal}{'/mugo_queue/execute'|ezurl()}{literal};

	$('#add').click(function ()
	{
		var taskId = $( '#taskid').val();
		var taskTypeId = $( '#tasktypeid').val();
		if( taskId && taskTypeId )
		{
			$.ajax(
			{
				method: 'POST',
				url: baseUrlAdd,
				data:
				{
					task_id : taskId,
					task_type_id : taskTypeId,
				},
			})
			.done( function( response )
			{
				response = $.trim( response );

				if( response )
				{
					alert( response );
				}
				else
				{
					location.reload();
				}
			});
		}
	});

	$( '.execute, .remove' ).click(function ()
	{
		var row = $(this).closest( 'tr' );
		var taskId = row.attr( 'data-id' );
		var taskTypeId = $( '#tasktypeid').val();

		var url = '';

		if( $(this).hasClass( 'remove' ) )
		{
			url = baseUrlRemove;
		}
		else
		{
			url = baseUrlExecute;
		}

		if( taskId && taskTypeId )
		{
			$.ajax(
			{
				method: 'POST',
				url: url,
				data:
				{
					task_id : taskId,
					task_type_id : taskTypeId,
				},
			})
			.done( function( response )
			{
				response = $.trim( response );

				if( response )
				{
					alert( response );
				}
				else
				{
					row
						.css( 'background-color', 'lightgreen')
						.delay( 300 )
						.slideUp( 300 );
				}
			});
		}
	});
});
{/literal}
</script>