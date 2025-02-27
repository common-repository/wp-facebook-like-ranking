=== WordPress Social Like Ranking ===
Contributors: mankinjp
Donate link: https://www.paypal.me/taishikato0903/2usd
Tags: plugin, facebook, like, ranking, popular
Requires at least: 3.1
Requires PHP: 5.6
Tested up to: 5.2
Stable tag: 2.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With this plugin, you can use a your posts' ranking rated by the number of Facebook like.

== Description ==

= Features =

* **Making a ranking rated by the number of Facebook like**. You can make and use a ranking rated by the number of Facebook like width this plugin. It's good for visitors.

* **Setting in detail**. You can decide how often it will update the raning information and how many posts it will check when it updates the information.

* **Using as a widget**. You can put on this as a widget.

* **Category filter**. You can create rankings which have only specific category posts.

== Installation ==

Check out here
[wp-fb-like-ranking/README.md at master · taishikato/wp-fb-like-ranking](https://github.com/taishikato/wp-fb-like-ranking/blob/master/README.md)

・more about a function

`get_like_ranking (int $post_number = 5, bool $post_count = true, array $thumbnail_size = null, int $category_id = null, $shorten_words = null)`

ex1)

`get_like_ranking (10, false, array(20, 20))`

It shows 10 posts and 20px × 20px thumbnail picture without expressing like count.

ex2)

`get_like_ranking (10, false, null, 1)`

It shows 10 posts of a category which has id 1.

== Frequently asked questions ==

= I need help with your plugin! What should I do? =

 If you're having problems with the plugin, my suggestion would be try disabling all other plugins.

== Screenshots ==

1. A example of using WordPress Social Like Ranking.
2. WordPress Social Like Ranking Stats panel.


== Changelog ==
= 2.0.4 =
* Fix bug
= 2.0.3 =
* Fix bug
= 2.0.2 =
* Fix for the trademark violation (facebook)
= 2.0.1 =
* bug fix
= 2.0.0 =
* change the Facebook API
= 1.4.0 =
* fix bug
= 1.3.0 =
* Implementing Widget
= 1.2.0 =
* Multilingualization (Japanese, English)
= 1.13 =
* Tiny update
= 1.121 =
* Tiny updates
= 1.12 =
* Adding title properties to elements.
= 1.11 =
* Just add a title property
= 1.1 =
* Add a category filter.

= 1.05 =
* Able to recreate the ranking

= 1.04 =
* Change how to create ranking

= 1.03 =
* Fix bug

= 1.02 =
* Make thumbnail image clickable

= 1.01 =
* Fix bug

= 1.0 =
* Public release

== Upgrade notice ==

You better use Wordpress 3.1 at least.
