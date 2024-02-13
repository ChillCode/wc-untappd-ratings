# Untappd Ratings for WooCommerce

### Prerequisites

* [WordPress](https://wordpress.org)
* [WooCommerce](https://github.com/woocommerce/woocommerce)
* [Untappd API Key](https://untappd.com/api/dashboard)

## Show Untappd Ratings on product page instead of WooCommerce one's

![Beer ID](https://user-images.githubusercontent.com/9787055/211172256-d9a54599-0788-41fb-84bc-f84e1d713e53.png)

![Ratings](https://user-images.githubusercontent.com/9787055/211172305-d12fcbfb-c612-494c-afd9-9566579c5c28.png)

To find the Untappd Beer ID, just search the beer on Untappd and get the ID from the url:

[https://untappd.com/b/toppling-goliath-brewing-co-kentucky-brunch-brand-stout-2016-silver-wax/<b>1905472</b>](https://untappd.com/b/toppling-goliath-brewing-co-kentucky-brunch-brand-stout-2016-silver-wax/1905472)

## Add a Google Map Untappd Feed to your site

To use Google maps it's required to enbale Google Javascript Maps API

![Map](https://user-images.githubusercontent.com/9787055/211171591-c5817264-606e-481e-a12f-d569915e8b5d.png)

Easy way to add a map using a shortcode:

[wc_untappd_map api_key="GOOGLE_API_KEY" brewery_id="73836" center_map="yes" height="500" max_checkins="300" zoom="4"]

###### Google Javascript Maps API key.
### Default ""

api_key=""

### Brewery ID to show on map
### Default "0"

brewery_id="0"

### Center map
### Default "yes"

center_map="yes"

### Google map height.
### Default "500"

height="500"

### Untapp at home default coordinates.
### Default "34.2346598,-77.9482096"

lat_lng="34.2346598,-77.9482096"

### Map div class.
### Default ""

map_class=""

### Map div ID.
### Default ""

map_id=""

### Map div style.
### Default ""

map_style=""

### Show an interactive or static map.
### To use static map it's required to enable staticmap API.
### Default "interactive"

map_type="interactive"

### Use Untapp icon to mark checkins on the map.
### Default "true"

map_use_icon="true"

### Use your own icon url.
### Default ""

map_use_url_icon=""

### Checkins to show on the map.
### Max checkins to show are 300.
### Default "25"

max_checkins="25"

### Map zoom.
### Default "4"

zoom="4"

## Add Ratings to Structured Data

![Structured Data](https://user-images.githubusercontent.com/9787055/211171958-bb889589-d6c8-4747-bf15-45202e1166c4.png)

### Configuration

To configure the plugin just go to WooCommerce ->Settings Untappd Tab

![Settings](https://user-images.githubusercontent.com/9787055/211172102-4ccb1fcb-7342-4aca-97cc-40a9d7bd50dd.png)

