<?php
if ( !class_exists( 'SM_Locations' ) ) {
	class SM_Locations {
		
		function sm_locations(){
			// Register my locations on init hook
			add_action( 'init', array( &$this, 'register_locations' ) );
			add_action( 'init', array( &$this, 'register_location_categories' ) );
			add_action( 'init', array( &$this, 'register_location_tags' ) );
			
			// Queue my JS
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_location_drag_drop_js' ) );
			add_action( 'init', array( &$this, 'location_drag_drop_js' ) );
			
			// Save post meta
			add_action( 'save_post', array( &$this, 'save_post_meta' ) );
		}
		
		// Register locations post type
		function register_locations(){
			
			$args = array(	
				'public' => true,
				'publicly_queryable' => false,
				'show_ui' => true,
				'capability_type' => 'post',
				'hierarchical' => true,
				'rewrite' => false,
				'query_var' => 'sm-location',
				'register_meta_box_cb' => array( &$this, 'location_meta_cb' ),
				'supports' => array(),
				'labels' => array( 
					'name' => 'Locations',
					'singular_name' => 'Location',
					'add_new_item' => 'Add New Location',
					'edit_item' => 'Edit Location',
					'new_item' => 'New Location',
					'view_item' => 'View Locations',
					'search_items' => 'Search Locations',
					'not_found' => 'No Locations found',
					'not_found_in_trash' => 'No Locations found in trash',
				)
			);
			
			// Register it
			register_post_type( 'sm-location', $args );
		}
		
		// Register custom taxonomy for location categories
		function register_location_categories(){
			$args = array( 
				'labels' => array(
					'name' => 'Location Categories',
					'singular_name' => 'Location Category',
					'search_items' => 'Search Categories',
					'popular_items' => 'Popular Categories',
					'all_items' => 'All Categories',
					'parent_item' => 'Parent Category',
					'parent_item_colon' => 'Parent Category:',
					'edit_item' => 'Edit Category',
					'update_item' => 'Update Category',
					'add_new_item' => 'Add New Category',
					'new_item_name' => 'New Category Name',
					'separate_items_with_commas' => 'Separate categories with commas',
					'add_or_remove_items' => 'Add or remove categories',
					'choose_from_most_used' => 'Choose from the most used categories'
				),
				'hierarchical' => true,
				'rewrite' => false,
				'show_tagcloud' => false
			);
			
			register_taxonomy( 'sm-category', 'sm-location', $args );
		}
		
		// Register custom taxonomy for location tags
		function register_location_tags(){
			$args = array( 
				'labels' => array(
					'name' => 'Location Tags',
					'singular_name' => 'Location Tag',
					'search_items' => 'Search Tags',
					'popular_items' => 'Popular Tags',
					'all_items' => 'All Tags',
					'parent_item' => 'Parent Tag',
					'parent_item_colon' => 'Parent Tag:',
					'edit_item' => 'Edit Tag',
					'update_item' => 'Update Tag',
					'add_new_item' => 'Add New Tag',
					'new_item_name' => 'New Tag Name',
					'separate_items_with_commas' => 'Separate tags with commas',
					'add_or_remove_items' => 'Add or remove tags',
					'choose_from_most_used' => 'Choose from the most used tags'
				),
				'hierarchical' => false,
				'rewrite' => false,
				'show_tagcloud' => false
			);
			
			register_taxonomy( 'sm-tag', 'sm-location', $args );
		}
		
		// Add call back for meta box
		function location_meta_cb(){
			add_meta_box( 'sm-location-premium-support', __( 'Premium Support', 'SimpleMap' ), array( &$this, 'premium_support' ), 'sm-location', 'side', 'high' );
			add_meta_box( 'sm-geo-location', __( 'Geographic Location', 'SimpleMap' ), array( &$this, 'geo_location' ), 'sm-location', 'normal' );
			add_meta_box( 'sm-additional-information', __( 'Additional Information', 'SimpleMap' ), array( &$this, 'additional_information' ), 'sm-location', 'normal' );
			add_meta_box( 'sm-location-drag-drop', __( 'Drag and Drop Location', 'SimpleMap' ), array( &$this, 'location_drag_drop' ), 'sm-location', 'side' );
		}
		
		// Add premium support box
		function premium_support() {
			
			global $current_screen, $simplemap_ps, $current_user;
			
			get_current_user();
			$status_key = md5( 'ft_premium_support_' . $simplemap_ps->product_id . '_' . sanitize_title_with_dashes( $simplemap_ps->site_url )  . '_' . sanitize_title_with_dashes( $simplemap_ps->server_url ) ) ;
			$sso_key = md5( 'ft_premium_sso_' . $current_user->id . '_' . $simplemap_ps->product_id . '_' . sanitize_title_with_dashes( $simplemap_ps->site_url )  . '_' . sanitize_title_with_dashes( $simplemap_ps->server_url ) );
			
			// Set status from transient if not set via global
			if ( '' == $simplemap_ps->ps_status && '' != get_transient( $status_key ) )
				$simplemap_ps->ps_status = get_transient( $status_key );
				
			// Set sso key from transient if not set via global
			if ( '' == $simplemap_ps->sso_status && '' != get_transient( $sso_key ) )
				$simplemap_ps->sso_status = get_transient( $sso_key );
				

			//echo "<pre>";print_r( $simplemap_ps );echo "</pre>";
			if ( ! url_has_ftps_for_item( $simplemap_ps ) ) :
				
				?>
				<p>
					<?php _e( 'By signing up for SimpleMap premium support, you help to ensure future enhancements to this excellent project as well as the following benefits:', 'SimpleMap' ); ?>
				</p>
			
				<ul style='margin-left:25px;list-style-type:disc'>
					<li><?php _e( 'Around the clock access to our extensive knowledge base and support forum from within your WordPress dashboard', 'SimpleMap' ); ?></li>
					<li><?php _e( 'Professional and timely response times to all your questions from the SimpleMap team', 'SimpleMap' ); ?></li>
					<li><?php _e( 'A 10% discount for any custom functionality you request from the SimpleMap developers', 'SimpleMap' ); ?></li>
					<li><?php _e( 'A 6-12 month advance access to new features integrated into the auto upgrade functionality of WordPress', 'SimpleMap' ); ?></li>
				</ul>
											
				<ul style='margin-left:25px;list-style-type:none'>
					<li><a href='<?php echo get_ftps_paypal_button( $simplemap_ps ); ?>'><?php _e( 'Signup Now', 'SimpleMap' ); ?></a></li>
					<li><a target='_blank' href='<?php echo get_ftps_learn_more_link( $simplemap_ps ); ?>'><?php _e( 'Learn More', 'SimpleMap' ); ?></a></li>
				</ul>
				
				<?php
				
			else :
				
				?>
				<p class='howto aligncenter'><?php printf( __( 'Your premium support license for %s is valid until %s', 'SimpleMap' ), get_ftps_site( $simplemap_ps ), date( "F d, Y", get_ftps_exp_date( $simplemap_ps ) ) ); ?></p>
				<ul style='margin-left:25px;list-style-type:disc'>
					<li><a href="#" id="simplemap-pss"><?php _e( 'Launch Premium Support widget', 'SimpleMap' ); ?></a></li>
					<li><a target='_blank' href="http://support.simplemap-plugin.com?sso=<?php echo get_ftps_sso_key( $simplemap_ps ); ?>"><?php _e( 'Visit Premium Support web site', 'SimpleMap' ); ?></a></li>
				<script type="text/javascript" charset="utf-8">
				  Tender = {
				    hideToggle: true,
				    sso: "<?php echo get_ftps_sso_key( $simplemap_ps ); ?>",
				    widgetToggles: [document.getElementById('simplemap-pss')]
				  }
				</script>
				<script src="https://simplemap.tenderapp.com/tender_widget.js" type="text/javascript"></script>
				<?php

			endif;
		}
		
		// Geographic Location Information
		function geo_location( $post ){
			global $simple_map, $hook_suffix;
			$options = get_option( 'SimpleMap_options' );
			
			// Location data
			$location_address 	= get_post_meta( $post->ID, 'location_address', true ) ? get_post_meta( $post->ID, 'location_address', true ) : '';
			$location_address2 	= get_post_meta( $post->ID, 'location_address2', true ) ? get_post_meta( $post->ID, 'location_address2', true ) : '';
			$location_city 		= get_post_meta( $post->ID, 'location_city', true ) ? get_post_meta( $post->ID, 'location_city', true ) : '';
			$location_state 	= get_post_meta( $post->ID, 'location_state', true ) ? get_post_meta( $post->ID, 'location_state', true ) : $options['default_state'];
			$location_zip 		= get_post_meta( $post->ID, 'location_zip', true ) ? get_post_meta( $post->ID, 'location_zip', true ) : '';
			$location_country 	= get_post_meta( $post->ID, 'location_country', true ) ? get_post_meta( $post->ID, 'location_country', true ) : $options['default_country'];
			$location_lat 		= get_post_meta( $post->ID, 'location_lat', true ) ? get_post_meta( $post->ID, 'location_lat', true ) : '';
			$location_lng 		= get_post_meta( $post->ID, 'location_lng', true ) ? get_post_meta( $post->ID, 'location_lng', true ) : '';

			?>
				<p class="sub"><?php _e('You must enter either an address or a latitude/longitude. If you enter both, the address will not be geocoded and your latitude/longitude values will remain intact.', 'SimpleMap'); ?></p>
				<div class="table">
					<table class="form-table">

						<!-- Store Address -->
						<tr valign="top">
							<td width="150"><label for="location_address"><?php _e('Address', 'SimpleMap'); ?></label></td>
							<td><input type="text" name="location_address" id="location_address" size="30" value="<?php echo esc_attr( $location_address ); ?>" /><br />
							<input type="text" name="location_address2" size="30" value="<?php echo esc_attr( $location_address2 ); ?>" /></td>
						</tr>
						
						<!-- City / Town -->
						<tr valign="top">
							<td><label for="location_city"><?php _e('City/Town', 'SimpleMap'); ?></label></td>
							<td><input type="text" name="location_city" id="location_city" value="<?php echo esc_attr( $location_city ); ?>" size="30" /></td>
						</tr>

						<!-- State / Providence -->
						<tr valign="top">
							<td><label for="location_state"><?php _e('State/Province', 'SimpleMap'); ?></label></td>
							<td><input type="text" name="location_state" id="location_state" value="<?php echo esc_attr( $location_state ); ?>" size="30" /></td>
						</tr>

						<!-- Zip / Postal Code -->
						<tr valign="top">
							<td><label for="location_zip"><?php _e('Zip/Postal Code', 'SimpleMap'); ?></label></td>
							<td><input type="text" name="location_zip" id="location_zip" value="<?php echo esc_attr( $location_zip ); ?>" size="30" maxlength="20" /></td>
						</tr>

						<!-- Country -->
						<tr valign="top">
							<td><label for="location_country"><?php _e('Country', 'SimpleMap'); ?></label></td>
							<td>
								<select name="location_country" id="location_country">
									<?php
									foreach ( $simple_map->get_country_options() as $key => $value ) {
										echo '<option value="' . $key . '" ' . selected( $location_country, $key, false ) . '>' . $value . '</option>'."\n";
									}
									?>
								</select>
							</td>
						</tr>

						<!-- Lat / Lng -->						 
						<tr valign="top">
							<td><label for="location_lat"><?php _e('Latitude/Longitude', 'SimpleMap'); ?></label></td>
							<td><input type="text" name="location_lat" id="location_lat" size="14" value="<?php echo esc_attr( $location_lat ); ?>" />
							<input type="text" name="location_lng" id="location_lng" size="14" value="<?php echo esc_attr( $location_lng ); ?>" /> <span id='latlng_updated' class='updated' style='display:none;color:#666666;font-style:italic;font-size:11px;'>Lat / Lng updated. Update address too? <a href='#' onclick="dragDropUpdateAddress();jQuery('#latlng_updated').hide();jQuery('#latlng_dontforget').fadeIn();return false;" >yes</a> | <a href='#' onclick="jQuery('#latlng_updated').hide();jQuery('#latlng_dontforget').fadeIn();return false;">no</a></span> <span id='latlng_dontforget' class='error' style='display:none;color:#666666;font-style:italic;font-size:11px;'>Changes aren't saved until you update or publish</span></td>
						</tr>
					
					</table>				
				</div> <!-- table -->
				<div class="clear"></div>
			<?php
		}

		// Additional Information
		function additional_information( $post ){
			$options = get_option( 'SimpleMap_options' );

			$location_phone 	= get_post_meta( $post->ID, 'location_phone', true ) ? get_post_meta( $post->ID, 'location_phone', true ) : '';
			$location_fax 		= get_post_meta( $post->ID, 'location_fax', true ) ? get_post_meta( $post->ID, 'location_fax', true ) : '';
			$location_url 		= get_post_meta( $post->ID, 'location_url', true ) ? get_post_meta( $post->ID, 'location_url', true ) : '';
			$location_email		= get_post_meta( $post->ID, 'location_email', true ) ? get_post_meta( $post->ID, 'location_email', true ) : '';
			$location_special 	= get_post_meta( $post->ID, 'location_special', true ) ? get_post_meta( $post->ID, 'location_special', true ) : '';
			?>
			<div class="table">
			
			<table class="form-table">
				 
				<!-- Phone -->
				<tr valign="top">
					<td width="150"><label for="location_phone"><?php _e('Phone', 'SimpleMap'); ?></label></td>
					<td><input type="text" id="location_phone" name="location_phone" size="30" maxlength="28" value="<?php echo esc_attr( $location_phone ); ?>" /></td>
				</tr>

				<!-- Email -->
				<tr valign="top">
					<td><label for="location_email"><?php _e('Email', 'SimpleMap'); ?></label></td>
					<td><input type="text" name="location_email" id="location_email" size="30" value="<?php echo esc_attr( $location_email ); ?>" />
				</tr>

				<!-- URL -->
				<tr valign="top">
					<td><label for="location_url"><?php _e('URL', 'SimpleMap'); ?></label></td>
					<td><input type="text" name="location_url" id="location_url" size="30" value="<?php echo esc_attr( $location_url ); ?>" />
					<br /><?php _e('Please include <strong>http://</strong>', 'SimpleMap'); ?></td>
				</tr>

				<!-- Fax -->
				<tr valign="top">
					<td><label for="location_fax"><?php _e('Fax', 'SimpleMap'); ?></label></td>
					<td><input type="text" id="location_fax" name="location_fax" size="30" maxlength="28" value="<?php echo esc_attr( $location_fax ); ?>" /></td>
				</tr>

				<!-- Store Special -->
				<?php if ( $options['special_text'] != '' ) { ?>
				<tr valign="top">
					<td><label for="location_special"><?php echo $options['special_text']; ?></label></td>
					<td><input type="checkbox" id="location_special" name="location_special" value="1" <?php checked( $location_special ); ?> /></td>
				</tr>
				<?php } ?>
			
			</table>
			
			</div> <!-- table -->
			
			<div class="clear"></div>
			
			<?php
		}
		
		// This function contains the little map with the marker
		function location_drag_drop( $post ) {
			global $simple_map;
			$options = get_option( 'SimpleMap_options' );
			
			// Location data
			$location_address 	= get_post_meta( $post->ID, 'location_address', true ) ? get_post_meta( $post->ID, 'location_address', true ) : '';
			$location_address2 	= get_post_meta( $post->ID, 'location_address2', true ) ? get_post_meta( $post->ID, 'location_address2', true ) : '';
			$location_city 		= get_post_meta( $post->ID, 'location_city', true ) ? get_post_meta( $post->ID, 'location_city', true ) : '';
			$location_state 	= get_post_meta( $post->ID, 'location_state', true ) ? get_post_meta( $post->ID, 'location_state', true ) : $options['default_state'];
			$location_zip 		= get_post_meta( $post->ID, 'location_zip', true ) ? get_post_meta( $post->ID, 'location_zip', true ) : '';
			$location_country 	= get_post_meta( $post->ID, 'location_country', true ) ? get_post_meta( $post->ID, 'location_country', true ) : $options['default_country'];
			$location_lat 		= get_post_meta( $post->ID, 'location_lat', true ) ? get_post_meta( $post->ID, 'location_lat', true ) : '';
			$location_lng 		= get_post_meta( $post->ID, 'location_lng', true ) ? get_post_meta( $post->ID, 'location_lng', true ) : '';
		
			?>
			<p class='howto'>Drag the marker to fine tune your location's placement on the map</p>
			<div id="dragdrop_map_canvas" style="width:267px;height:200px;"></div>
			<?php
		}
		
		// Enqueues the JS I need.
		function enqueue_location_drag_drop_js() {
			global $current_screen, $post;

			if ( !is_admin() || 'sm-location' != $current_screen->id )
				return;
				
			wp_enqueue_script( 'sm-drag-drop-loaction-js', get_option( 'siteurl' ) . '/?sm-drag-drop-loaction-js=' . $post->ID, array( 'jquery' ) );
			
		}
		
		// Javascript for add / edit location page
		function location_drag_drop_js() {
			global $current_screen;
			
			$options = get_option( 'SimpleMap_options' );
			
			if ( !isset( $_GET['sm-drag-drop-loaction-js'] ) )
				return;
			
			$postid = (int)	$_GET['sm-drag-drop-loaction-js'];
			
			$drag_drop_lat = get_post_meta( $postid, 'location_lat', true ) ? get_post_meta( $postid, 'location_lat', true ) : '40.730885';
			$drag_drop_lng = get_post_meta( $postid, 'location_lng', true ) ? get_post_meta( $postid, 'location_lng', true ) : '-73.997383';
			$drag_drop_zoom = ( $drag_drop_lat == '40.730885' ) ? 2 : 17;
			
			header( "Content-type: application/x-javascript" );	


			if ( '' == $options['api_key'] ) {
				die( "alert( '" . esc_js( __( "You will need to enter your API key in general options before your addresses will be coded properly.", 'SimpleMap' ) ) . "');" );
			}
			
			?>
			var map;
			var geocoder;
			var address;
			var marker;
			var newAddress
			
			function initialize_drag_drop() {
			  map = new GMap2( document.getElementById( "dragdrop_map_canvas" ) );
			  map.setCenter( new GLatLng( <?php echo esc_js( $drag_drop_lat ); ?>,<?php echo esc_js( $drag_drop_lng ); ?> ), <?php echo esc_js( $drag_drop_zoom ); ?> );
			  map.addControl( new GLargeMapControl3D() );
			  marker = new GMarker( map.getCenter(), {draggable: true, bouncy: true} );
			  map.addOverlay( marker );
			  GEvent.addListener(map, "click", dragDropGetAddress);
			  GEvent.addListener( marker, "dragend", dragDropGetAddressTest );
			  geocoder = new GClientGeocoder();
			}
			
			function dragDropGetAddressTest() {
				address = marker.getPoint();
			    geocoder.getLocations( address, dragDropShowAddress );
			}
			
			function dragDropGetAddress( overlay, latlng ) {
			  if (latlng != null) {
			    address = latlng;
			    geocoder.getLocations( latlng, dragDropShowAddress );
			  }
			}
			
			function dragDropShowAddress(response ) {
			  map.clearOverlays();
			  if (!response || response.Status.code != 200) {
			    alert("Status Code:" + response.Status.code);
			    map.addOverlay(marker);
			  } else {
			    place = response.Placemark[0];
			    point = new GLatLng(place.Point.coordinates[1],place.Point.coordinates[0]);
			    marker = new GMarker( point, {draggable: true, bouncy: true} );
			    map.addOverlay(marker);
			   	newAddress = place.address;
			 	dragDropUpdateFormFields( response, place );
			    GEvent.addListener( marker, "dragend", dragDropGetAddressTest );
			  }
			}
			
			jQuery(document).ready(function(){
				initialize_drag_drop();
			});
			
			function dragDropUpdateFormFields( response, place ) {
			  jQuery( "#location_lat" ).attr( 'value', place.Point.coordinates[1] );
			  jQuery( "#location_lng" ).attr( 'value', place.Point.coordinates[0] );
			  jQuery( "#latlng_dontforget" ).hide();
			  jQuery( "#latlng_updated" ).fadeIn();
			}
			
			function dragDropUpdateAddress() {
				
				if ( place.AddressDetails.Country.AdministrativeArea != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.Thoroughfare != null )
				    newStreet = place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.Thoroughfare.ThoroughfareName;
				else
					newStreet = '';
				
				if ( place.AddressDetails.Country.AdministrativeArea != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.LocalityName != null )
				    newCity = place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.LocalityName;
				else
					newCity = '';
				
				if ( place.AddressDetails.Country.AdministrativeArea != null && place.AddressDetails.Country.AdministrativeArea.AdministrativeAreaName != null )
				    newState = place.AddressDetails.Country.AdministrativeArea.AdministrativeAreaName;
				else
				    newState = '';
				    
				if ( place.AddressDetails.Country.AdministrativeArea != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.PostalCode != null && place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.PostalCode.PostalCodeNumber != null )
				    newZip = place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.PostalCode.PostalCodeNumber;
				else
				    newZip = '';
				
				if ( place.AddressDetails.Country != null && place.AddressDetails.Country.CountryNameCode != null )
				    newCountry = place.AddressDetails.Country.CountryNameCode;
				else
					newCountry = '';
				
				oldStreet = jQuery("#location_address").attr( 'value' );
				oldCity = jQuery("#location_address").attr( 'value' );
				oldState = jQuery("#location_address").attr( 'value' );
				oldZip = jQuery("#location_address").attr( 'value' );
				oldCountry = jQuery("#location_address").attr( 'value' );

				jQuery("#location_address").attr( 'value', newStreet );
				jQuery("#location_city").attr( 'value', newCity );
				jQuery("#location_state").attr( 'value', newState );
				jQuery("#location_zip").attr( 'value', newZip );
				jQuery("#location_country").val( newCountry );
			}
			<?php
			die();
		}
		
		// This function saves the geo data as well as the additional info
		function save_post_meta( $post ) {
			global $simple_map, $current_screen;
			
			// Bail if we're not editing a location
			if ( ! is_object( $current_screen ) || 'sm-location' != $current_screen->id || 'sm-location' != $current_screen->post_type )
				return;
			
			$options = get_option( 'SimpleMap_options' );
			$post_object = get_post( $post );
			
			$api_key = ( isset( $options['api_key'] ) && !empty( $options['api_key'] ) ) ? $options['api_key'] : '';
			
			// Grab old data
			$location_address 	= get_post_meta( $post, 'location_address', true ) ? get_post_meta( $post, 'location_address', true ) : ' ';
			$location_address2 	= get_post_meta( $post, 'location_address2', true ) ? get_post_meta( $post, 'location_address2', true ) : ' ';
			$location_city 		= get_post_meta( $post, 'location_city', true ) ? get_post_meta( $post, 'location_city', true ) : ' ';
			$location_state 	= get_post_meta( $post, 'location_state', true ) ? get_post_meta( $post, 'location_state', true ) : $options['default_state'];
			$location_zip 		= get_post_meta( $post, 'location_zip', true ) ? get_post_meta( $post, 'location_zip', true ) : ' ';
			$location_country 	= get_post_meta( $post, 'location_country', true ) ? get_post_meta( $post, 'location_country', true ) : $options['default_country'];
			$location_lat 		= get_post_meta( $post, 'location_lat', true ) ? get_post_meta( $post, 'location_lat', true ) : ' ';
			$location_lng 		= get_post_meta( $post, 'location_lng', true ) ? get_post_meta( $post, 'location_lng', true ) : ' ';
			$location_phone 	= get_post_meta( $post, 'location_phone', true ) ? get_post_meta( $post, 'location_phone', true ) : ' ';
			$location_fax 		= get_post_meta( $post, 'location_fax', true ) ? get_post_meta( $post, 'location_fax', true ) : ' ';
			$location_url 		= get_post_meta( $post, 'location_url', true ) ? get_post_meta( $post, 'location_url', true ) : ' ';
			$location_email 	= get_post_meta( $post, 'location_email', true ) ? get_post_meta( $post, 'location_email', true ) : ' ';
			$location_special 	= get_post_meta( $post, 'location_special', true ) ? get_post_meta( $post, 'location_special', true ) : ' ';
			// If adding new field that has a default (like state and country above), you must modify the update section below accordingly!
			
			// Grab new data
			$new_address 	= isset( $_POST['location_address'] ) ? $_POST['location_address'] : '';
			$new_address2 	= isset( $_POST['location_address2'] ) ? $_POST['location_address2'] : '';
			$new_city 		= isset( $_POST['location_city'] ) ? $_POST['location_city'] : '';
			$new_state 		= isset( $_POST['location_state'] ) ? $_POST['location_state'] : '';
			$new_zip 		= isset( $_POST['location_zip'] ) ? $_POST['location_zip'] : '';
			$new_country 	= isset( $_POST['location_country'] ) ? $_POST['location_country'] : '';
			$new_lat 		= isset( $_POST['location_lat'] ) ? $_POST['location_lat'] : '';
			$new_lng 		= isset( $_POST['location_lng'] ) ? $_POST['location_lng'] : '';
			$new_phone 		= isset( $_POST['location_phone'] ) ? $_POST['location_phone'] : '';
			$new_fax 		= isset( $_POST['location_fax'] ) ? $_POST['location_fax'] : '';
			$new_url 		= isset( $_POST['location_url'] ) ? $_POST['location_url'] : '';
			$new_email 		= isset( $_POST['location_email'] ) ? $_POST['location_email'] : '';
			$new_special 	= isset( $_POST['location_special'] ) ? $_POST['location_special'] : '';
			
			// Update
			if ( $location_address != $new_address ) update_post_meta( $post, 'location_address', $new_address );	
			if ( $location_address2 != $new_address2 ) update_post_meta( $post, 'location_address2', $new_address2 );	
			if ( $location_city != $new_city ) update_post_meta( $post, 'location_city', $new_city );	
			if ( $location_state != $new_state || ( $new_state == $options['default_state'] && ! get_post_meta( $post, 'location_state', true ) ) ) update_post_meta( $post, 'location_state', $new_state );	
			if ( $location_zip != $new_zip ) update_post_meta( $post, 'location_zip', $new_zip );	
			if ( $location_country != $new_country || ( $new_country == $options['default_country'] && ! get_post_meta( $post, 'location_country', true ) ) ) update_post_meta( $post, 'location_country', $new_country );	
			if ( $location_lat != $new_lat ) update_post_meta( $post, 'location_lat', $new_lat );	
			if ( $location_lng != $new_lng ) update_post_meta( $post, 'location_lng', $new_lng );	
			if ( $location_phone != $new_phone ) update_post_meta( $post, 'location_phone', $new_phone );	
			if ( $location_fax != $new_fax ) update_post_meta( $post, 'location_fax', $new_fax );	
			if ( $location_url != $new_url ) update_post_meta( $post, 'location_url', $new_url );	
			if ( $location_email != $new_email ) update_post_meta( $post, 'location_email', $new_email );	
			if ( $location_special != $new_special ) update_post_meta( $post, 'location_special', $new_special );	
			

			// Lets not geocode on auto-draft
			if ( 'auto-draft' == $post_object->post_status )
				return;
				
			/*
			 * Geocode here under the following conditions
			 * If address, city, state, or zip have changed
			 * If lat or lng is empty
			 */
			$updated_address = get_post_meta( $post, 'location_address', true );
			$updated_city = get_post_meta( $post, 'location_city', true );
			$updated_state = get_post_meta( $post, 'location_state', true );
			$updated_zip = get_post_meta( $post, 'location_zip', true );
			$updated_country = get_post_meta( $post, 'location_country', true );
			$updated_lat = get_post_meta( $post, 'location_lat', true );
			$updated_lng = get_post_meta( $post, 'location_lng', true );

			if ( $location_address != $updated_address || $location_city != $updated_city || $location_state != $updated_state || $location_zip != $updated_zip || $location_country != $updated_country || '' == $updated_lat || '' == $updated_lng && isset( $options['api_key'] ) ) {
				$geocode_result = $simple_map->geocode_location( $updated_address, $updated_city, $updated_state, $updated_zip, $updated_country, $api_key );
				
				if ( $geocode_result && isset( $geocode_result['status'] ) && $geocode_result['status'] == 200 ) {
					if ( isset( $geocode_result['lat'] ) && isset( $geocode_result['lng'] ) ) {
						update_post_meta( $post, 'location_lat', $geocode_result['lat'] );
						update_post_meta( $post, 'location_lng', $geocode_result['lng'] );
					}
				}			
			}
		}
		
		// All location data
		function get_location_data_types() {
			
			$types['name'] = __( 'Name', 'SimpleMap' );
            $types['address'] = __( 'Address', 'SimpleMap' );
            $types['address2'] = __( 'Address 2', 'SimpleMap' );
            $types['city'] = __( 'City', 'SimpleMap' );
            $types['state'] = __( 'State', 'SimpleMap' );
            $types['country'] = __( 'Country', 'SimpleMap' );
            $types['zip'] = __( 'Zip', 'SimpleMap' );
            $types['phone'] = __( 'Phone', 'SimpleMap' );
            $types['fax'] = __( 'Fax', 'SimpleMap' );
            $types['url'] = __( 'URL', 'SimpleMap' );
            $types['category'] = __( 'Categories', 'SimpleMap' );
            $types['tags'] = __( 'Tags', 'SimpleMap' );
            $types['description'] = __( 'Description', 'SimpleMap' );
            $types['special'] = __( 'Special', 'SimpleMap' );
            $types['lat'] = __( 'Lat', 'SimpleMap' );
            $types['lng'] = __( 'Lng', 'SimpleMap' );
            $types['email'] = __( 'Email', 'SimpleMap' );
            
            $types = apply_filters( 'sm-data-types', $types );
            
            return $types;
		}
	}
}
?>