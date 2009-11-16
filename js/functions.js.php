<?php
header("Content-type: application/x-javascript");
import_request_variables('g', 'sm_');
?>

var default_lat = <?php echo urldecode($sm_default_lat); ?>;
var default_lng = <?php echo urldecode($sm_default_lng); ?>;
var default_radius = <?php echo urldecode($sm_default_radius); ?>;
var zoom_level = <?php echo urldecode($sm_zoom_level); ?>;
var map_width = "<?php echo urldecode($sm_map_width); ?>";
var map_height = "<?php echo urldecode($sm_map_height); ?>";
var special_text = "<?php echo urldecode($sm_special_text); ?>";
var units = "<?php echo urldecode($sm_units); ?>";
var limit = "<?php echo urldecode($sm_results_limit); ?>";
var plugin_url = "<?php echo urldecode($sm_plugin_url); ?>";
<?php
if ($sm_autoload == 'some' || $sm_autoload == 'all')
	$autozoom = $sm_zoom_level;
else
	$autozoom = 'false';
?>
var autozoom = <?php echo $autozoom; ?>;
var default_domain = "<?php echo urldecode($sm_default_domain); ?>";
var address_format = "<?php echo urldecode($sm_address_format); ?>";
var visit_website_text = "<?php echo urldecode($sm_visit_website_text); ?>";
var get_directions_text = "<?php echo urldecode($sm_get_directions_text); ?>";
var location_tab_text = "<?php echo urldecode($sm_location_tab_text); ?>";
var description_tab_text = "<?php echo urldecode($sm_description_tab_text); ?>";
var phone_text = "<?php echo urldecode($sm_phone_text); ?>";
var fax_text = "<?php echo urldecode($sm_fax_text); ?>";
var tags_text = "<?php echo urldecode($sm_tags_text); ?>";
var noresults_text = "<?php echo urldecode($sm_noresults_text); ?>";

var map;
var geocoder;

function codeAddress() {
	geocoder = new GClientGeocoder();
	var d_address = document.getElementById("default_address").value;
	//alert(address);
		 geocoder.getLatLng(d_address, function(latlng) {
			document.getElementById("default_lat").value = latlng.lat();
			document.getElementById("default_lng").value = latlng.lng();
		 });
}

function codeNewAddress() {
	if (document.getElementById("store_lat").value != '' && document.getElementById("store_lng").value != '') {
		document.new_location_form.submit();
	}
	else {
		geocoder = new GClientGeocoder();
		var address = '';
		var street = document.getElementById("store_address").value;
		var city = document.getElementById("store_city").value;
		var state = document.getElementById("store_state").value;
		var country = document.getElementById("store_country").value;
		
		if (street) { address += street + ', '; }
		if (city) { address += city + ', '; }
		if (state) { address += state + ', '; }
		address += country;
	
		 geocoder.getLatLng(address, function(latlng) {
			document.getElementById("store_lat").value = latlng.lat();
			document.getElementById("store_lng").value = latlng.lng();
			document.new_location_form.submit();
		 });
	}
}

function codeChangedAddress() {
	geocoder = new GClientGeocoder();
	var address = '';
	var street = document.getElementById("store_address").value;
	var city = document.getElementById("store_city").value;
	var state = document.getElementById("store_state").value;
	var country = document.getElementById("store_country").value;
	
	if (street) { address += street + ', '; }
	if (city) { address += city + ', '; }
	if (state) { address += state + ', '; }
	address += country;

	geocoder.getLatLng(address, function(latlng) {
		document.getElementById("store_lat").value = latlng.lat();
		document.getElementById("store_lng").value = latlng.lng();
	});
}

function searchLocations(categories) {
 var address = document.getElementById('addressInput').value;
 address = address.replace(/&/gi, " ");
 geocoder.getLatLng(address, function(latlng) {
   if (!latlng) {
     latlng = new GLatLng(150,100);
     searchLocationsNear(latlng, address, "search", "unlock", categories);
   } else {
     searchLocationsNear(latlng, address, "search", "unlock", categories);
   }
 });
}

function searchLocationsNear(center, homeAddress, source, mapLock, categories) {
	if (document.getElementById('radiusSelect')) {
		if (units == 'mi') {
		  	var radius = parseInt(document.getElementById('radiusSelect').value);
		}
		else if (units == 'km') {
		  	var radius = parseInt(document.getElementById('radiusSelect').value) / 1.609344;
		}
	}
	else {
		if (units == 'mi') {
		  	var radius = parseInt(default_radius);
		}
		else if (units == 'km') {
		  	var radius = parseInt(default_radius) / 1.609344;
		}
	}
 
	if (source == 'auto_all') {
		var searchUrl = plugin_url + 'actions/create-xml.php?lat=' + center.lat() + '&lng=' + center.lng() + '&radius=infinite&namequery=' + homeAddress + '&limit=0&categories=' + categories;
	}
	else {
		var searchUrl = plugin_url + 'actions/create-xml.php?lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius + '&namequery=' + homeAddress + '&limit=' + limit + '&categories=' + categories;
	}
	GDownloadUrl(searchUrl, function(data) {
		var xml = GXml.parse(data);
		var markers = xml.documentElement.getElementsByTagName('marker');
		map.clearOverlays();
		
		var results = document.getElementById('results');
		results.innerHTML = '';
		if (markers.length == 0) {
			results.innerHTML = '<h3>' + noresults_text + '</h3>';
			map.setCenter(new GLatLng(default_lat,default_lng), zoom_level);
			return;
		}
		
		var bounds = new GLatLngBounds();
		for (var i = 0; i < markers.length; i++) {
			var name = markers[i].getAttribute('name');
			var address = markers[i].getAttribute('address');
			var address2 = markers[i].getAttribute('address2');
			var city = markers[i].getAttribute('city');
			var state = markers[i].getAttribute('state');
			var zip = markers[i].getAttribute('zip');
			var country = markers[i].getAttribute('country');
			var distance = parseFloat(markers[i].getAttribute('distance'));
			var point = new GLatLng(parseFloat(markers[i].getAttribute('lat')), parseFloat(markers[i].getAttribute('lng')));
			var url = markers[i].getAttribute('url');
			var phone = markers[i].getAttribute('phone');
			var fax = markers[i].getAttribute('fax');
			var special = markers[i].getAttribute('special');
			var category = markers[i].getAttribute('category');
			var tags = markers[i].getAttribute('tags');
			if (markers[i].firstChild) {
				var description = markers[i].firstChild.nodeValue;
			}
			else {
				var description = '';
			}
			
			var marker = createMarker(point, name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, special, category, tags, description);
			map.addOverlay(marker);
			var sidebarEntry = createSidebarEntry(marker, name, address, address2, city, state, zip, country, distance, homeAddress, phone, fax, url, special, category, tags, description);
			results.appendChild(sidebarEntry);
			bounds.extend(point);
		}
		if (source == "search") {
			map.setCenter(bounds.getCenter(), (map.getBoundsZoomLevel(bounds) - 1));
		}
		else if (mapLock == "unlock") {
			map.setCenter(bounds.getCenter(), autozoom);
		}
	});
}

function stringFilter(s) {
	filteredValues = "emnpxt%";     // Characters stripped out
	var i;
	var returnString = "";
	for (i = 0; i < s.length; i++) {  // Search through string and append to unfiltered values to returnString.
		var c = s.charAt(i);
		if (filteredValues.indexOf(c) == -1) returnString += c;
	}
	return returnString;
}

function createMarker(point, name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, special, category, tags, description) {
	var marker = new GMarker(point);
	
	var mapwidth = Number(stringFilter(map_width));
	var mapheight = Number(stringFilter(map_height));
	
	var maxbubblewidth = Math.round(mapwidth / 1.5);
	var maxbubbleheight = Math.round(mapheight / 2.2);
	
	var fontsize = 12;
	var lineheight = 12;
	
	var titleheight = 3 + Math.floor((name.length + category.length) * fontsize / (maxbubblewidth * 1.5));
	//var titleheight = 2;
	var addressheight = 2;
	if (address2 != '') {
		addressheight += 1;
	}
	if (phone != '' || fax != '') {
		addressheight += 1;
		if (phone != '') {
			addressheight += 1;
		}
		if (fax != '') {
			addressheight += 1;
		}
	}
	var tagsheight = 3;
	var linksheight = 2;
	var totalheight = (titleheight + addressheight + tagsheight + linksheight + 1) * fontsize;
		
	if (totalheight > maxbubbleheight) {
		totalheight = maxbubbleheight;
	}
	
	var html = '	<div class="markertext" style="height: ' + totalheight + 'px; overflow-y: auto; overflow-x: hidden;">';
	html += '		<h3 style="margin-top: 0; padding-top: 0; border-top: none;">' + name + '<br /><span class="bubble_category">' + category + '</span></h3>';
	html += '		<p>' + address;
					if (address2 != '') {
	html += '			<br />' + address2;
					}
					
					if (address_format == 'town, province postalcode') {
	html += '		<br />' + city + ', ' + state + ' ' + zip + '</p>';
					}
					else if (address_format == 'town province postalcode') {
	html += '		<br />' + city + ' ' + state + ' ' + zip + '</p>';
					}
					else if (address_format == 'town-province postalcode') {
	html += '		<br />' + city + '-' + state + ' ' + zip + '</p>';
					}
					else if (address_format == 'postalcode town-province') {
	html += '		<br />' + zip + ' ' + city + '-' + state + '</p>';
					}
					else if (address_format == 'postalcode town, province') {
	html += '		<br />' + zip + ' ' + city + ', ' + state + '</p>';
					}
					else if (address_format == 'postalcode town') {
	html += '		<br />' + zip + ' ' + city + '</p>';
					}
					else if (address_format == 'town postalcode') {
	html += '		<br />' + city + ' ' + zip + '</p>';
					}
					
					if (phone != '') {
	html += '			<p>' + phone_text + ': ' + phone;
						if (fax != '') {
	html += '				<br />' + fax_text + ': ' + fax;
						}
	html += '			</p>';
					}
					else if (fax != '') {
	html += '			<p>' + fax_text + ': ' + fax + '</p>';
					}
					if (tags != '') {
	html += '			<p class="bubble_tags">' + tags_text + ': ' + tags + '</p>';
					}
					var dir_address = address + ',' + city;
					if (state) { dir_address += ',' + state; }
					if (zip) { dir_address += ',' + zip; }
					if (country) { dir_address += ',' + country; }
	html += '		<p class="bubble_links"><a href="http://google' + default_domain + '/maps?q=' + homeAddress + ' to ' + dir_address + '" target="_blank">' + get_directions_text + '</a>';
					if (url != '') {
	html += '			&nbsp;|&nbsp;<a href="' + url + '" title="' + name + '" target="_blank">' + visit_website_text + '</a>';
					}
	html += '		</p>';
	html += '	</div>';
	
	if (description != '') {
		var numlines = Math.ceil(description.length / 40);
		var newlines = description.split('<br />').length - 1;
		var totalheight2 = 0;
		
		if (description.indexOf('<img') == -1) {
			totalheight2 = (numlines + newlines + 1) * fontsize;
		}
		else {
			var numberindex = description.indexOf('height=') + 8;
			var numberend = description.indexOf('"', numberindex);
			var imageheight = Number(description.substring(numberindex, numberend));
			
			totalheight2 = ((numlines + newlines - 2) * fontsize) + imageheight;
		}
		
		if (totalheight2 > maxbubbleheight) {
			totalheight2 = maxbubbleheight;
		}
		
		var html2 = '	<div class="markertext" style="height: ' + totalheight2 + 'px; overflow-y: auto; overflow-x: hidden;">' + description + '</div>';
		
		GEvent.addListener(marker, 'click', function() {
			marker.openInfoWindowTabsHtml([new GInfoWindowTab(location_tab_text, html), new GInfoWindowTab(description_tab_text, html2)], {maxWidth: maxbubblewidth});
			window.location = '#map_top';
		});
	}

	else {
		GEvent.addListener(marker, 'click', function() {
			marker.openInfoWindowHtml(html, {maxWidth: maxbubblewidth});
			window.location = '#map_top';
		});
	}
	return marker;
}

function createSidebarEntry(marker, name, address, address2, city, state, zip, country, distance, homeAddress, phone, fax, url, special, category, tags, description) {
  var div = document.createElement('div');
  
  // Beginning of result
  var html = '<div class="result">';
  
  // Flagged special
  if (special == 1 && special_text != '') {
  	html += '<div class="special">' + special_text + '</div>';
  }
  
  // Name & distance
  html += '<div class="result_name">';
  html += '<h3 style="margin-top: 0; padding-top: 0; border-top: none;">' + name;
  if (distance.toFixed(1) != 'NaN') {
  	if (units == 'mi') {
	  	html+= ' <small>' + distance.toFixed(1) + ' miles</small>';
	}
  	else if (units == 'km') {
	  	html+= ' <small>' + (distance * 1.609344).toFixed(1) + ' km</small>';
	}
  }
  html += '</h3></div>';
  
  // Address
  html += '<div class="result_address"><address>' + address;
  if (address2 != '') {
  	html += '<br />' + address2;
  }
  
	if (address_format == 'town, province postalcode') {
		html += '<br />' + city + ', ' + state + ' ' + zip + '</address></div>';
	}
	else if (address_format == 'town province postalcode') {
		html += '<br />' + city + ' ' + state + ' ' + zip + '</address></div>';
	}
	else if (address_format == 'town-province postalcode') {
		html += '<br />' + city + '-' + state + ' ' + zip + '</address></div>';
	}
	else if (address_format == 'postalcode town-province') {
		html += '<br />' + zip + ' ' + city + '-' + state + '</address></div>';
	}
	else if (address_format == 'postalcode town, province') {
		html += '<br />' + zip + ' ' + city + ', ' + state + '</address></div>';
	}
	else if (address_format == 'postalcode town') {
		html += '<br />' + zip + ' ' + city + '</address></div>';
	}
	else if (address_format == 'town postalcode') {
		html += '<br />' + city + ' ' + zip + '</address></div>';
	}
  
  // Phone & fax numbers
  html += '<div class="result_phone">';
  if (phone != '') {
  	html += phone_text + ': ' + phone;
  }
  if (fax != '') {
  	html += '<br />' + fax_text + ': ' + fax;
  }
  html += '</div>';
  
  // Links section
  html += '<div class="result_links">';
  
  // Visit Website link
  html += '<div>';
  if (url != 'http://' && url != '') {
  	html += '<a href="' + url + '" title="' + name + '" target="_blank">' + visit_website_text + '</a>';
  }
  html += '</div>';
  
  // Get Directions link
  if (distance.toFixed(1) != 'NaN') {
					var dir_address = address + ',' + city;
					if (state) { dir_address += ',' + state; }
					if (zip) { dir_address += ',' + zip; }
					if (country) { dir_address += ',' + country; }
	  html += '<a href="http://google' + default_domain + '/maps?q=' + homeAddress + ' to ' + dir_address + '" target="_blank">' + get_directions_text + '</a>';
  }
  html += '</div>';
  
  html += '<div style="clear: both;"></div>';
  
  // End of result
  html += '</div>';
  
  div.innerHTML = html;
  div.style.cursor = 'pointer'; 
  div.style.margin = 0;
  GEvent.addDomListener(div, 'click', function() {
    GEvent.trigger(marker, 'click');
  });
  GEvent.addDomListener(div, 'mouseover', function() {
    //div.style.backgroundColor = '#eee';
  });
  GEvent.addDomListener(div, 'mouseout', function() {
    //div.style.backgroundColor = '#fff';
  });
  return div;
}