<?php
/*
Plugin Name: WP Socail Like Ranking
Plugin URI: http://wordpress.org/extend/plugins/wp-social-like-ranking/
Description: you can use a your posts' ranking rated by the number of Facebook like./facebookのいいね数に応じた、ブログ記事のランキングを生成します
Author: Taishi Kato
Version: 2.0.4
Text Domain: wp-social-like-ranking
Domain Path: /languages/
Author URI: http://taishikato.com/
*/

ini_set('allow_url_fopen', 1);
require_once('widget.php');

$wplrank = new WpLikeRanking();

class WpLikeRanking
{
    public function __construct()
    {
        if (function_exists('register_deactivation_hook')) {
            // When This Plugin Become Invalid
            register_deactivation_hook(__FILE__, array(&$this, 'deleteLikecountMeta'));
        }

        add_action('transition_post_status', array(&$this, 'addUserblogMeta'), 10, 3);

        //set Hook
        if (!wp_next_scheduled('wp_fb_like_ranking_event')) {
            $WpFbLikeRankingUpdateFrequency = get_option('wp_fb_like_ranking_frequency');
            wp_schedule_event(time(), $WpFbLikeRankingUpdateFrequency, 'wp_fb_like_ranking_event');
        }
        // add action
        add_action('wp_fb_like_ranking_event', array(&$this, 'updateFbLike'));
    }

    private function addUserblogMeta($new_status, $old_status, $post)
    {
        if ($new_status == 'publish' && $old_status != 'publish') {
            global $post;
            add_post_meta($post->ID, 'wp_fb_like_count', 0, true);
        }
    }

    private function deleteLikecountMeta()
    {
        // プラグインを無効にしたときの処理を書く
        // Search All Of The Posts
        $lastposts = get_posts('numberposts=-1');
        foreach ($lastposts as $post) {
            setup_postdata($post);
            // get the ID
            $postId = $post->ID;
            // Delete Meta Data
            delete_post_meta($postId, 'wp_fb_like_count');
        }
    }

    private function updateFbLike()
    {
        $WpFbLikeRankingUpdatePostNumber = get_option('wp_fb_like_ranking_updatePostNumber');
        if ($WpFbLikeRankingUpdatePostNumber == 'all') {
            $lastposts = get_posts('numberposts=-1');
        } else {
            $lastposts = get_posts('numberposts=' . $WpFbLikeRankingUpdatePostNumber . '&orderby=post_date&order=DESC');
        }
        foreach ($lastposts as $post) {
            setup_postdata($post);
            // get the ID
            $postId = $post->ID;
            // get the permalink
            $permalink = get_permalink($postId);
            // get the number of like
            $likeNumber = getLikeCount($permalink);
            $likeNumber = (int) $likeNumber;
            $preLikeNumber = get_post_meta($postId, 'wp_fb_like_count', true);

            if ($preLikeNumber != $likeNumber) {
                update_post_meta($postId, 'wp_fb_like_count', $likeNumber, $preLikeNumber);
            }
        }
    }
}

load_plugin_textdomain('wp-facebook-like-ranking', false, dirname(plugin_basename(__FILE__)).'/languages/');

add_action('admin_menu', 'wp_fb_like_ranking_admin_menu');
function wp_fb_like_ranking_admin_menu()
{
    add_options_page(
        'WP Facebook Like Ranking',
        'WP Facebook Like Ranking',
        8,
        __FILE__,
        'wp_fb_like_ranking_edit_setting'
    );
}

function set_likecount_meta()
{
    // プラグインを有効にしたときの処理
    // Set the options
    update_option('wp_fb_like_ranking_frequency', 'hourly');
    update_option('wp_fb_like_ranking_updatePostNumber', 'all');
    // Search All Of The Posts
    $lastposts = get_posts('numberposts=-1');
    foreach ($lastposts as $post) {
        setup_postdata($post);
        // get the ID
        $postId = $post->ID;
        // get the permalink
        $permalink = get_permalink($postId);
        // Get like count via FB API
        $likeNumber = getLikeCount($permalink);
        $likeNumber = (int) $likeNumber;
        $meta_values = get_post_meta($postId, 'wp_fb_like_count', true);
        if ($meta_values != '') {
            if ($meta_values != $likeNumber) {
                update_post_meta($postId, 'wp_fb_like_count', $likeNumber, $meta_values);
            }
        } else {
            // Add Meta Data
            add_post_meta($postId, 'wp_fb_like_count', $likeNumber, true);
        }
    }
}

function getLikeCount($permalink)
{
    $url = 'https://graph.facebook.com/v3.1/?id=' . urlencode($permalink) .
            '&fields=engagement&access_token=' .
            get_option('wp_fb_like_ranking_fbAppToken');
    $json = file_get_contents($url);
    $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
    $arr = json_decode($json, true);
    $likeNumber = $arr['engagement']['reaction_count'];
    $likeNumber = (int) $likeNumber;
    return $likeNumber;
}

function styleCss()
{
    $style_url = plugins_url('wp-social-like-ranlking.css', __FILE__);
    wp_enqueue_style('wp-social-like-ranlking', $style_url, false, '1');
}
add_action('wp_print_styles', 'styleCss');


// 管理画面設定
function wp_fb_like_ranking_edit_setting()
{
    if (isset($_POST['wp_fb_like_ranking_fbAppToken'])) {
        update_option('wp_fb_like_ranking_fbAppToken', $_POST['wp_fb_like_ranking_fbAppToken']);
    }
    if (isset($_POST['create'])) {
        // Set default value
        set_likecount_meta();
    }
    if (isset($_POST['wp_fb_like_ranking_frequency'])) {
        update_option('wp_fb_like_ranking_frequency', $_POST['wp_fb_like_ranking_frequency']);
    }
    if (isset($_POST['wp_fb_like_ranking_updatePostNumber'])) {
        update_option('wp_fb_like_ranking_updatePostNumber', $_POST['wp_fb_like_ranking_updatePostNumber']);
    }
    $WpFbLikeRankingFbAppToken = get_option('wp_fb_like_ranking_fbAppToken');
    $WpFbLikeRankingFrequency = get_option('wp_fb_like_ranking_frequency');
    $WpFbLikeRankingUpdatePostNumber = get_option('wp_fb_like_ranking_updatePostNumber');
    include 'setting.html.php';
}

function get_like_ranking(
    $number = 5,
    $like_count = true,
    $thumbnail_size = null,
    $category_id = null,
    $shorten_words = null,
    $custom_post_name = null
) {
    $number = esc_html($number);
    if (!empty($category_id)) {
        if (!empty($custom_post_name)) {
            $rank = get_posts(
                'meta_key=wp_fb_like_count&numberposts=' .
                $number .
                '&orderby=meta_value_num&category=' .
                $category_id .
                '&post_type=' .
                $custom_post_name
            );
        } else {
            $rank = get_posts(
                'meta_key=wp_fb_like_count&numberposts=' .
                $number .
                '&orderby=meta_value_num&category=' .
                $category_id
            );
        }
    } else {
        if (!empty($custom_post_name)) {
            $rank = get_posts(
                'meta_key=wp_fb_like_count&numberposts=' .
                $number .
                '&orderby=meta_value_num&post_type=' .
                $custom_post_name
            );
        } else {
            $rank = get_posts('meta_key=wp_fb_like_count&numberposts=' . $number . '&orderby=meta_value_num');
        }
    }
    echo '<ul class="wp-fb-like-ranking clearfix">';
    $i = 0;
    foreach ($rank as $post) {
        $likeNumberToPost = get_post_meta($post->ID, 'wp_fb_like_count', true);
        if ($likeNumberToPost != 0) {
            $i++;
            $permalinkUrl = get_permalink($post->ID);
            $post_title = $post->post_title;
            if (isset($shorten_words)) {
                $post_title = mb_substr($post_title, 0, $shorten_words) . '...';
            }
            if ($like_count == true) {
                if ($thumbnail_size == null) {
                    echo '<li><a href="' . $permalinkUrl . '" title="' . $post->post_title . '">' .
                    esc_html($post_title) .
                    '</a> <span class="wp-fb-like-ranking-count">' .
                    $likeNumberToPost .
                    '</span></li>';
                } else {
                    echo '<li><a href="' . $permalinkUrl . '" title="' . $post->post_title . '">' .
                    get_the_post_thumbnail($post->ID, $thumbnail_size, array('class' => 'wp-fb-like-ranking-thumb')) .
                    esc_html($post_title) .
                    '</a> <span class="wp-fb-like-ranking-count">' . $likeNumberToPost . '</span></li>';
                }
            } else {
                if ($thumbnail_size == null) {
                    echo '<li><a href="' . $permalinkUrl .  '" title="' . $post->post_title . '">' .
                    esc_html($post_title) .
                    '</a></li>';
                } else {
                    echo '<li><a href="' . $permalinkUrl . '" title="' . $post->post_title . '">' .
                    get_the_post_thumbnail($post->ID, $thumbnail_size, array('class' => 'wp-fb-like-ranking-thumb')) .
                    esc_html($post_title) . '</a></li>';
                }
            }
        }
    }
    if ($i == 0) {
        _e('No post liked.', 'wp-facebook-like-ranking');
    }
    echo '</ul>';
    wp_reset_query();
}
