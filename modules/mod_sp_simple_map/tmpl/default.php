<?php
/*------------------------------------------------------------------------
# mod_sp_simple_map - Google Map module for Joomla by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2011 JoomShaper.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if ($lat && $lng) { ?>
	<div id="sp_simple_map<?php echo $uniqid ?>">
		<script type="text/javascript">
		  var myLatlng  = new google.maps.LatLng(<?php echo $lat ?>,<?php echo $lng ?>);
		  function initialize() {
			var mapOptions = {
			  zoom: <?php echo $zoom ?>,
			  center: myLatlng,
			  mapTypeId: google.maps.MapTypeId.<?php echo $map_type ?>
			};
			var map = new google.maps.Map(document.getElementById('sp_simple_map_canvas'), mapOptions);
			var marker = new google.maps.Marker({position:myLatlng, map:map});	
		  }
		  google.maps.event.addDomListener(window, 'load', initialize);
		</script>
		<div id="sp_simple_map_canvas"></div>
	</div>
<?php } else { ?>
	<p>Please provide the Latitudes and Longitudes value.</p>
<?php }
