{*
	INPUT:
		tasks_data    data points
*}
<h1>Overview all queues</h1>

<table class="list">
	<thead>
		<tr>
			<th>
				Name
			</th>
			<th>
				Count
			</th>
			<th>
				Action
			</th>
		</tr>
	</thead>
	<tbody>
		{if $tasks_data}
			{foreach $tasks_data as $name => $row sequence array( 'yui-dt-even', 'yui-dt-odd' ) as $row_class}
				<tr class="{$row_class}">
					<td>
						<a href={concat( '/mugo_queue/list?task_type_id=', $row[ 'name' ] )}>{$row[ 'name' ]|wash()}</a>
					</td>
					<td style="text-align: right;">
						{$row[ 'count' ]|wash()}
					</td>
					<td>
						<button data-task-type-id="{$name|wash()}" class="remove-all">Remove all</button>
					</td>
				</tr>
			{/foreach}
		{else}
			<tr>
				<td colspan="2">
					Nothing in queue
				</td>
			</tr>
		{/if}
	</tbody>
</table>

<script>
{literal}
$(function()
{
	var baseUrlRemove = {/literal}{'/mugo_queue/remove_all'|ezurl()}{literal};

	$( '.remove-all' ).click(function ()
	{
		var taskTypeId = $( this ).attr( 'data-task-type-id' );
		if( taskTypeId )
		{
			$.ajax(
			{
				method: 'POST',
				url: baseUrlRemove,
				data:
				{
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
});
{/literal}
</script>