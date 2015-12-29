{*
   $item_count
*}

{if is_unset( $limit )}
	{def $limit = 100}
{/if}
{if is_unset( $offset )}
	{def $offset = first_set( ezhttp( 'offset', 'GET' ), 0 )}
{/if}
{if is_unset( $item_count )}
	{def $item_count = 100}
{/if}

{def $last_page = ceil( $item_count|div( $limit ) )}
{def $current_page = sum( 1, ceil( $offset|div( $limit ) ) )}

{def $pages = fetch( 'mugo_queue', 'get_pagination_steps', hash(
		'current_page', $current_page,
		'page_count', $last_page
))}

{if $last_page|gt(1)}
	<ul class="navigator">
		{def $prev_index = 1}
		{foreach $pages as $index}

			{if sum( $prev_index, 1 )|lt( $index )}
				<li>…</li>
			{/if}

			{if eq( $index, $current_page )}
				<li class="current-index"><input type="text" value="{$index}"/></li>
			{else}
				<li><a href="#">{$index}</a></li>
			{/if}
			{set $prev_index = $index}
		{/foreach}
	</ul>
{/if}

<br>
<br>

<script>
{literal}
$(function() {

	var limit = {/literal}{$limit}{literal};
	$('.navigator a').click(function (e)
	{
		var newOffset = ( parseInt( $(this).text() ) -1 ) * limit;
		location.href = updateUrlParameter(location.href, 'offset', newOffset );
	});

	function updateUrlParameter(uri, key, value)
	{
		// remove the hash part before operating on the uri
		var i = uri.indexOf('#');
		var hash = i === -1 ? '' : uri.substr(i);
		uri = i === -1 ? uri : uri.substr(0, i);

		var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		var separator = uri.indexOf('?') !== -1 ? "&" : "?";
		if (uri.match(re))
		{
			uri = uri.replace(re, '$1' + key + "=" + value + '$2');
		}
		else
		{
			uri = uri + separator + key + "=" + value;
		}
		return uri + hash;  // finally append the hash as well
	};
});

{/literal}
</script>
