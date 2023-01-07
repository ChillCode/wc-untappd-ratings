/*!
 * WooCommerce Untappd
 * https://github.com/ChillCode/wc-untappd-ratings
 *
 *
 * Copyright (C) 2022 ChillCode
 * Released under the General Public License v3.0
 * https://www.gnu.org/licenses/gpl-3.0.html
 *
 */
(function ( $ ) {

    $.fn.UntappdMap = function( options ) {

        var settings = $.extend({
            zoom: "14",
            custom_style: "",
			height: "300",
			width: "640",
			api_key: '',
			center_map: 'no',
			center_lat: '0',
			center_lng: '0',
			map_use_icon: false,
			map_use_url_icon: '',
			map_type: 'interactive'
        }, options );

		init = function ( selector, settings ) {

			var myLatLng = new google.maps.LatLng( settings.center_lat, settings.center_lng );
			var mapOptions = {
				zoom: settings.zoom,
				center: myLatLng,
				scrollwheel: false,
				scaleControl:true,
				zoomControl: true,
				zoomControlOptions: { style: google.maps.ZoomControlStyle.SMALL, position: google.maps.ControlPosition.RIGHT_CENTER	},
				streetViewControl: false,
				mapTypeControl: true
			};
	
			var mapElement = document.getElementById( selector.id );

			if ( mapElement )
			{
				var map = new google.maps.Map( mapElement, mapOptions );
		
				if ( settings.custom_style != '' ) {
					
					var style_array = [];

					style_array = JSON.parse( atob( settings.custom_style ) );
					
					var customMapType = new google.maps.StyledMapType( style_array, {
						name: 'Custom Style'
					});
	
					var customMapTypeId = 'custom_style';
					map.mapTypes.set( customMapTypeId, customMapType );
					map.setMapTypeId( customMapTypeId );
				}

				if (settings.center_map === 'yes') {
					var centerLatLng = new google.maps.LatLng( settings.center_lat, settings.center_lng );
					map.setCenter( centerLatLng );
				}

				load_markers(map, settings.map_use_icon, settings.map_use_url_icon);
			}
		};

		load_markers = function (map, map_use_icon, map_use_url_icon) {

			if (!ajax_untappd_config.ajax_url) {
				return false;
			}

			var request = $.ajax(
				{
					url: ajax_untappd_config.ajax_url,
					dataType: 'json',
					type: 'GET',
					data: {
						action:	'wc_untappd_map_feed',
						wc_untappd_map_nonce: ajax_untappd_config.wc_untappd_map_nonce
					},
				}
			);
	
			request.done(function(data)
				{
					if (data)
					{
						for (var property in data)
						{
							if (data.hasOwnProperty(property))
							{
								add_map_marker(data[property]);
							}
						}
					}
				}
			);
	
			request.fail(function(jqXHR, textStatus)
				{
					console.log("Request failed: " + textStatus);
				}
			);
	
			getHtmlInfoWindow = function (untappd_checkin)
			{
				location_data = '';

				if (untappd_checkin.location)
				{
					untappd_checkin.location = "(" + untappd_checkin.location + ")";
	
					location_data = $("<a>").attr({target: '_blank', href: 'https://www.google.com/maps/search/?api=1&query=' + untappd_checkin.location}).text(untappd_checkin.location);
				}
	
				rating_score = '';
	
				if (untappd_checkin.rating_score)
				{
					rating_score = $("<div>").append(
						$("<h5>").text('Rating:')
					).append(
						$("<p>").html($("<b>").text(untappd_checkin.rating_score + '/5'))
					);
				}
	
				checkin_comment = checkin_comment_translated = '';
	
				if (untappd_checkin.comment)
				{
					checkin_comment = $("<div>").append(
						$("<h5>").text(ajax_untappd_config.languages[1] + ':'))
					.append(
						$("<p>").attr({class: 'untappd_comment'}).html($("<b>").text(untappd_checkin.comment))
					);
	
					checkin_comment_translated = $("<div>").attr({id: 'untappd_comment_translated_hide'}).append(
						$("<h5>").text(ajax_untappd_config.languages[2] + ':')
					).append(
						$("<p>").attr({class: 'untappd_comment_translated'}).html($("<b>").text(untappd_checkin.comment + '/5'))
					);
				}
	
				data_venue = '';
	
				if (untappd_checkin.venue_name)
				{
					if (untappd_checkin.foursquare_url && untappd_checkin.venue_name != 'Untappd at Home')
					{
						data_venue = $("<a>").attr({style:"text-decoration:underline", target: '_blank', href: untappd_checkin.foursquare_url}).text(untappd_checkin.venue_name);
					}
					else
					{
						data_venue = $("<b>").text(untappd_checkin.venue_name);
					}
				}
	
				product_link = '';
	
				if (untappd_checkin.permalink && untappd_checkin.product_id)
				{
					product_link = $("<a>").attr({style:'display:block;width:100%;', target: '_blank', href: untappd_checkin.permalink}).text(ajax_untappd_config.languages[5]);
	
					product_link =  $("<div>").append(
						$("<p>").attr({class: 'product woocommerce add_to_cart_inline', style: 'font-size:1em;border:1px solid #ccc;padding:6px;text-align: center;text-transform: uppercase;margin-top: 10px;'}).html(product_link)
					);
				}
	
				var html_data = $("<div>").attr({id: 'infowindow-content'}).append(
					$("<div>").append(
						$("<h5>").text(untappd_checkin.beer_name))
				).append(
					$("<div>").attr({style: 'text-align:center;', id:"checkin_desc"}).append(
						$("<img>").attr({style: 'width:80px;height:80px;padding:5px;', alt: untappd_checkin.beer_name, src: untappd_checkin.beer_label})
				).append(
					$("<div>").append($("<b>").html(untappd_checkin.user_name + ' '))
							.append($("<b>").html(function(i, h){return location_data;}))
							.append(' ' + ajax_untappd_config.languages[3] + ' ')
							.append($("<b>").html(untappd_checkin.beer_name))
							.append(' ' + ajax_untappd_config.languages[4] + ' ')
							.append(data_venue)
							.append(' ' + ajax_untappd_config.languages[0] + ' ' + untappd_checkin.checkin_date)
						)
						.append(product_link)
						.append(rating_score)
						.append(checkin_comment)
						.append(checkin_comment_translated)
				);
	
				return html_data;
			};
	
			var infowindows = [];
	
			function add_map_marker(untappd_marker)
			{
				var myLatLng = {}, title = '';
				var checkins = [];
	
				for (var property in untappd_marker)
				{
					if (untappd_marker.hasOwnProperty(property))
					{
						if (!untappd_marker[property].lat || !untappd_marker[property].lng)
						{
							return false;
						}
	
						myLatLng = {lat: untappd_marker[property].lat, lng: untappd_marker[property].lng};
	
						title = untappd_marker[property].beer_name;
	
						checkins.push({
							'data' : untappd_marker[property],
							'html' : getHtmlInfoWindow(untappd_marker[property])
						});
					}
				}

				var untappd_icon;
	
				if (map_use_icon)
				{
					untappd_icon = {
						path: 'm24.145 3.2668c-4.9298 9.8904-5.2263 9.421-5.4302 10.7l-0.32123 2.0263c-0.11747 0.74134-0.40772 1.4518-0.84633 2.0634l-9.1986 12.837c-0.4695 0.65483-1.2602 1.0008-2.0633 0.90194-2.4896-0.30888-4.8062-1.9892-5.8873-4.2317-0.35213-0.72897-0.27799-1.5938 0.1915-2.2487l9.1986-12.843c0.43861-0.61161 1.0131-1.112 1.6803-1.4641l1.8101-0.95752c1.1429-0.60543 0.59925-0.73515 8.3769-8.5808 0.06193-0.29653 0.06193-0.45096 0.22231-0.49422 0.18544-0.043224 0.40774-0.061934 0.38919-0.28415l-0.02534-0.2842c-0.01267-0.11747 0.08029-0.22232 0.19782-0.22232 0.278-0.004737 0.81546 0.074013 1.5815 0.61778 0.75985 0.54981 1.0131 1.0379 1.0934 1.3035 0.03718 0.1112-0.03718 0.22864-0.14829 0.25946l-0.27802 0.067974c-0.20993 0.05554-0.15435 0.27181-0.14198 0.45714 0.0047 0.17306-0.14199 0.22232-0.40154 0.37684zm-10.576-0.83398c0.20994 0.05554 0.15436 0.27181 0.14198 0.45714-0.01267 0.16675 0.1296 0.21626 0.39538 0.37067 0.48804 0.98225 0.94518 1.8842 1.3714 2.7182 0.04322 0.08029 0.14198 0.092724 0.20387 0.03079 0.69189-0.74134 1.5197-1.6186 2.502-2.6317 0.08029-0.086448 0.08645-0.21626 0.0047-0.30271-0.49416-0.50658-1.0193-1.044-1.5814-1.6124-0.061934-0.29036-0.061934-0.45098-0.22231-0.49423-0.18544-0.0495-0.40774-0.061934-0.38919-0.28416 0.017644-0.20389 0.086447-0.5004-0.17305-0.50657-0.27801-0.0047368-0.81546 0.067974-1.5815 0.61778-0.75985 0.54981-1.0131 1.0378-1.0934 1.3035-0.08645 0.25946 0.22232 0.28416 0.42009 0.3336zm24.087 22.876-9.1924-12.843c-0.81546-1.1429-1.6433-1.4456-3.4842-2.4154-0.69191-0.36451-0.87724-0.67338-1.8842-1.7854-0.06193-0.067974-0.17912-0.05554-0.22232 0.03079-2.8603 5.4858-2.9097 5.1151-3.0271 5.8565-0.10516 0.66099-0.08029 1.2355 0.01764 1.8409 0.11747 0.74134 0.40772 1.4518 0.84634 2.0634l9.1986 12.843c0.46953 0.65483 1.2479 1.0008 2.0448 0.9081 2.4896-0.30268 4.8186-1.9769 5.912-4.2379 0.3336-0.73515 0.26564-1.6-0.20995-2.261z',
						fillColor: '#2ad2c5',
						fillOpacity: 1,
						strokeWeight: 0.5,
						strokeColor: '#000000',
						size: new google.maps.Size(38, 32),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(19, 0)
					};
				}
				else if (map_use_url_icon)
				{
					untappd_icon = {url: map_use_url_icon};
				}
	
				var marker = new google.maps.Marker(
					{
						position: myLatLng,
						map: map,
						title: title,
						icon: untappd_icon
					}
				);
	
				var infowindow = new google.maps.InfoWindow({content: '<div id="infowindow-container" style="overflow:auto;"></div><div id="infowindow-pagination-container"></div>', maxWidth: 380});
	
				google.maps.event.addListener(infowindow, 'domready', (function()
					{
						if (checkins.length > 1)
						{
							$('#infowindow-pagination-container').pagination(
								{
									dataSource: checkins,
									pageSize: 1,
									autoHidePrevious: false,
									autoHideNext: false,
									showPageNumbers: false,
									showNavigator: true,
									showGoButton: true,
									showGoInput: true,
									callback: function(data, pagination)
									{
										$('#infowindow-container').html(data[0].html);
									}
								}
							);
						}
						else
						{
							$('#infowindow-container').html($(checkins[0].html));
						}
					}
				));
	
				google.maps.event.addListener(marker, 'click', (function(marker)
					{
						return function()
						{
							for (var i = 0, len = infowindows.length; i < len; i++)
							{
								infowindows[i].close();
							}
	
							infowindows.push(infowindow);
	
							infowindow.open(map, marker);
						};
					}
				)(marker));
			}
		};

		init_static = function ( selector, settings ) {

			if (!ajax_untappd_config.ajax_url) {
				return false;
			}

			var request = $.ajax(
				{
					url: ajax_untappd_config.ajax_url,
					dataType: 'json',
					type: 'GET',
					data: {
						action:	'wc_untappd_map_feed',
						wc_untappd_map_nonce: ajax_untappd_config.wc_untappd_map_nonce
					},
				}
			);
	
			request.done(function(data)
				{
					if (data)
					{
						if ( parseInt( settings.height ) <= 0 ) settings.height = 400;
			
						var container = jQuery( '#' + selector.id ).parent();
						
						var markerStr = '';
						var centerLatLng = [];
			
						if ( settings.center_map === 'yes' ) {
							centerLatLng = [settings.center_lat, settings.center_lng];
						} else {
							centerLatLng = [0, 0];
						}

						var markers = '&markers=size:small%7Ccolor:0x2ad2c5%7C';

						if (settings.map_use_url_icon) {
							markers = '&markers=icon:' + encodeURIComponent(settings.map_use_url_icon) + '%7C';
						}

						for (var property in data)
						{
							if (data.hasOwnProperty(property))
							{
								for (var property_in in data[property])
								{
									if (data[property].hasOwnProperty(property_in))
									{
										if (!data[property][property_in].lat || !data[property][property_in].lng)
										{
											return false;
										}

										markers = markers + data[property][property_in].lat + ',' +  data[property][property_in].lng + '%7C';

										break;
									}
								}
							}
						}

						var img_src = '<img src="https://maps.googleapis.com/maps/api/staticmap?center=' + centerLatLng.toString() + '&language=' + ajax_untappd_config.map_current_lang + '&zoom=' + settings.zoom + markerStr + '&size=' + settings.width + 'x' + settings.height + markers + '&scale=1' + settings.custom_style + '&key=' + settings.api_key + '">';
						container.find('.untappd_map').append( img_src );
					}
				}
			);
	
			request.fail(function(jqXHR, textStatus)
				{
					console.log("Request failed: " + textStatus);
				}
			);
		};

		this.each(function() {
			if (settings.map_type === 'static') {
				init_static(this, settings);
			} else {
				init(this, settings);
			}
		});
    };
 }( jQuery ));
