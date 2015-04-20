

/**
 * Start the refresh of all sites.
 * @param  array  settings  The AJAX buttons settings.
 */
function refresh_all_sites_start( settings )
{
	jQuery('#ajax-status').html('AJAX refresh started.');
	jQuery('.apl-ajax-button').prop('disabled', true);
	jQuery('#tt-sites-list').html('');
}


/**
 * Done with the refresh of all sites.
 * @param  array  settings  The AJAX buttons settings.
 */
function refresh_all_sites_end( settings )
{
	jQuery('#ajax-status').html('Done refreshing sites.');
	jQuery('.apl-ajax-button').prop('disabled', false);
//	window.location.replace(window.location.href);
}


/**
 * Start contacting the server via AJAX for refresh sites list.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 */
function refresh_all_sites_loop_start( fi, settings )
{
	jQuery('#ajax-progress').html('Contacting server for Site list.');
}


/**
 * Finished contacting the server via AJAX for refresh sites list.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  bool   success   True if the AJAX call was successful, otherwise false.
 * @param  array  data      The returned data on success, otherwise error information.
 */
function refresh_all_sites_loop_end( fi, settings, success, data )
{
	jQuery('#ajax-progress').html('Received Site list.');
}


/**
 * Start cycling through the sites list returned via AJAX.
 * @param  array  ajax  The AJAX settings returned from the server.
 */
function refresh_site_start( ajax )
{
	jQuery('#ajax-status').html('Performing AJAX refresh.');
// 	jQuery('table.orghub-sites .site_title').addClass('processing');
}


/**
 * Finished cycling through the sites list returned via AJAX.
 * @param  array  ajax  The AJAX settings returned from the server.
 */
function refresh_site_end( ajax )
{
	jQuery('#ajax-status').html('AJAX refresh done.');
// 	jQuery('table.orghub-sites .site_title').removeClass('processing');
}


/**
 * Start contacting the server via AJAX to refresh one site.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  int    ai        The current ajax items count.
 * @param  array  ajax      The AJAX settings returned from the server.
 */
function refresh_site_loop_start( fi, settings, ai, ajax )
{
	jQuery('#ajax-progress').html('Refreshing blog '+(ai+1)+' of '+(ajax.items.length));
}


/**
 * Finished contacting the server via AJAX to refresh one site.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  int    ai        The current ajax items count.
 * @param  array  ajax      The AJAX settings returned from the server.
 * @param  bool   success   True if the AJAX call was successful, otherwise false.
 * @param  array  data      The returned data on success, otherwise error information.
 */
function refresh_site_loop_end( fi, settings, ai, ajax, success, data )
{
	if( !success || !data.success )
	{
		jQuery('#tt-sites-list').append( data.message+'<br/><br/>' );
		return;
	}
	
	var dajax = data.ajax;
	
	var html = '';
	
	for( var key in dajax.site )
	{
		html += key+' => '+dajax.site[key]+' <br/>';
	}
	html += '<br/><br/>';
	
	jQuery('#tt-sites-list').append(html);
}






/**
 * Start the analyze of all sites.
 * @param  array  settings  The AJAX buttons settings.
 */
function analyze_sites_start( settings )
{
	jQuery('#ajax-status').html('AJAX analysis started.');
	jQuery('.apl-ajax-button').prop('disabled', true);
	jQuery('#tt-sites-list').html('');
}


/**
 * Done with the analyze of all sites.
 * @param  array  settings  The AJAX buttons settings.
 */
function analyze_sites_end( settings )
{
	jQuery('#ajax-status').html('Done analyzing sites.');
	jQuery('.apl-ajax-button').prop('disabled', false);
//	window.location.replace(window.location.href);
}


/**
 * Start contacting the server via AJAX for analyze sites list.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 */
function analyze_sites_loop_start( fi, settings )
{
	jQuery('#ajax-progress').html('Contacting server for site analysis.');
}


/**
 * Finished contacting the server via AJAX for analyze sites list.
 * @param  int    fi        The current form count.
 * @param  array  settings  The AJAX buttons settings.
 * @param  bool   success   True if the AJAX call was successful, otherwise false.
 * @param  array  data      The returned data on success, otherwise error information.
 */
function analyze_sites_loop_end( fi, settings, success, data )
{
	jQuery('#ajax-progress').html('Finished analyzing sites.');
	
	if( !success || !data.success )
	{
		jQuery('#tt-sites-list').html( data.message+'<br/><br/>' );
		return;
	}
	
	var dajax = data.ajax;
	
	for( var s in dajax.sites )
	{
		var html = '';
		
		html += '<div class="default_site_info">';
			html += '<div class="name">'+s+'</div>';
			html += '<div class="default_sites">';
		
		if( dajax.sites[s]['default_sites'].length == 0 )
		{
			html += 'No sites found.';
		}
		else
		{
			for( var ds in dajax.sites[s]['default_sites'] )
			{
				var site = dajax.sites[s]['default_sites'][ds];
				html += '<div class="site"><a href="'+site['url']+'>'+site['title']+'</a></div>';
			}
		}
		
			html += '</div>';
		html += '</div>';
		html += '<br/><br/>';
		
		jQuery('#tt-sites-list').append(html);	
	}
}


function convert_site_start( settings )
{
	//alert('start');
}

function convert_site_end( settings )
{
	//alert('end');
}

function convert_site_loop_start( fi, settings )
{
}

function convert_site_loop_end( fi, settings, success, data )
{
	alert( data.message );
}

