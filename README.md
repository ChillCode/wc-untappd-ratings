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

| Attribute | Description | Default |
| ------------- | ------------- | ------------- |
| api_key | Google Javascript Maps API key. | "" |
| brewery_id | Brewery ID to show on map. | "0" |
| center_map | Center map. | "no" |
| height | Google map height. | "500" |
| lat_lng | Untappd at home default coordinates. | "34.2346598,-77.9482096" |
| map_class | Map div class. | "" |
| map_id | Map div ID. | "" |
| map_style | Map div style. | "" |
| map_type | Show an "interactive" or "static" map. To use "static" map it's required to enable staticmap API. | "interactive" |
| map_use_icon | Use Untappd icon to mark checkins on the map. | "true" |
| map_use_url_icon | Use your own icon url. | "" |
| max_checkins | Checkins to show on the map. Max checkins to show are 300. | "25" |
| zoom | Map zoom. | "4" |

## Add Ratings to Structured Data

![Structured Data](https://user-images.githubusercontent.com/9787055/211171958-bb889589-d6c8-4747-bf15-45202e1166c4.png)

### Configuration

To configure the plugin just go to WooCommerce ->Settings Untappd Tab

![Settings](https://user-images.githubusercontent.com/9787055/211172102-4ccb1fcb-7342-4aca-97cc-40a9d7bd50dd.png)

