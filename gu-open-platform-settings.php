<?php

/**
 * Plugin Name: Guardian News Feed
 * Plugin URI: http://www.guardian.co.uk/open-platform
 * Description: Publish articles and related links from the Guardian directly to your blog.
 * Author: Daniel Levitt for Guardian News and Media Ltd
 * Version: 1.2
 * Author URI: http://www.guardian.co.uk/open-platform
 */

/**
 *  ====== The Article Importer ======
 *  This enables you to publish Guardian articles directly to your blog.
 *
 *  Here are some of the things it does:
 *       - Browse and search for articles to publish
 *       - Post articles (or 'Save to Drafts') directly from your 'Posts' admin panel
 *       - Automatic check-in and replace to make sure you have published the most current version
 *         of the article
 *
 *  When publishing articles from the Guardian, please adhere to our publishing guidelines, and be
 *  aware of the Terms and Conditions.
 *
 *  Guidelines appear in the plug-in admin panel and the T&Cs are on our web site, but here are some reminders:
 *       - Changes. You mustn't remove or alter the text, links or images you get from us.
 *       - Key. If you don't have a key, get one here: http://www.guardian.co.uk/open-platform. It's required.
 *         If you do have one, please don't share it or use it anywhere else.
 *       - Deletions. Sometimes but very rarely we have to remove articles. When that happens, this plug-in
 *         will replace the Guardian content within your blog post with a message saying that the content is
 *         not available anymore.
 *
 *  ====== Related Articles ======
 *
 *  The Related Articles sidebar widget will find articles from the Guardian that might be related to your
 *  blog post.  It will then display a list of headlines in your sidebar.
 *
 *  The Related Articles sidebar widget does not require an access key.
 *
 *  ======= Notes =======
 *  This plug-in is designed to be used as is. We have several ways of working with partners if you want to
 *  do something different. Find out more here:
 *
 *  http://www.guardian.co.uk/open-platform
 *
 *  If you have ideas on how to improve the plug-in or other things we could do with WordPress, please join
 *  the conversation here:
 *
 *  http://groups.google.com/group/guardian-api-talk
 *
 *  Kind Regards,
 *  Daniel Levitt
 *  daniel.levitt@guardian.co.uk
 *  Guardian News & Media Ltd
 *
 */

include('gu-open-platform-article-importer.php');
include('gu-open-platform-related.php');
include("api". DIRECTORY_SEPARATOR ."gu-open-platform-api.php");

define ('GUARD_DIR', dirname(__FILE__));
define ('PREVIEW_KEY_MESSAGE', '<strong>A valid API key is required</strong>.<br>To get your access key <a href="%s" target="_blank">click here</a> and go through the registration process. You can then enter your key in the <a href="%s">plugin settings</a>.');
define ('PREVIEW_KEY_MESSAGE_SETTINGS', admin_url('options-general.php?page=the-guardian-news-feed/gu-open-platform-settings.php'));
define ('PREVIEW_KEY_MESSAGE_REGISTRATION', 'http://guardian.mashery.com/');
define ('PREVIEW_KEY_MESSAGE_UPDATE', '<br><strong>Note:</strong> If your old key has stopped working, it is because we have updated our API and you can <a href="%s" target="_blank">register for a new key here</a>.');

function Guardian_OpenPlatform_settings_page() {

    $api = new GuardianOpenPlatformAPI();

    if (isset($_POST ['Submit'])) {

        $newKey = trim(esc_attr($_POST [$api->guardian_api_keynameValue()]));

        // Save the posted value in the database
        update_option ( $api->guardian_api_keynameValue(), $newKey );
        update_option ( 'guardian_powered_image', trim(esc_attr($_POST ['guardian_powered_image'])) );
        ?>
    <?php
    }
    // Read in new or existing option value from database
    $str_api_key = get_option ( $api->guardian_api_keynameValue() );
    $logo = get_option ( 'guardian_powered_image' );

    $api = new GuardianOpenPlatformAPI($str_api_key);
    ?>
    <div class="wrap">
        <h2>The Guardian News Feed Configuration</h2>

        <p>If you have any questions, please have a look through the <a target="_blank" href="http://www.guardian.co.uk/open-platform/faq">FAQ</a> or post your question in the <a target="_blank" href="http://groups.google.com/group/guardian-api-talk">Google Group</a>.</p>

        <?php
        if (!empty($str_api_key)) {
            $status = $api->guardian_get_tier();
            if ($status) {
                echo sprintf( '<div class="updated"><p>%s</p></div>', 'You have a valid key.' );
            } else {
                echo sprintf( '<div class="error"><p><strong>%s</strong></p></div>', 'Your key is invalid, please <a target="_blank" href="http://guardian.mashery.com">register for a new one</a>.' );
            }
        } else {
            echo sprintf( '<div class="error"><p><strong>%s</strong></p></div>', 'You need a valid key to use this plugin, please <a target="_blank" href="http://guardian.mashery.com">register for one</a>.' );
        }
        ?>

        <form name="form1" method="post" action="">
            <table class="form-table">
                <tbody><tr>
                    <th valign="top" scope="row">Your API Key:</th>
                    <td>
                        <input type="text" size="30" value="<?php echo $str_api_key; ?>" name="<?php echo $api->guardian_api_keynameValue(); ?>">
                    </td>
                </tr>
                <tr>
                    <th valign="top" scope="row">Guardian Logo:</th>
                    <td>
                        <select size="1" name="guardian_powered_image">
                            <option <?php if (!$logo) {echo ' selected="selected"'; } ?> value="">Normal</option>
                            <option <?php if ($logo == 'BLACK') {echo ' selected="selected"'; } ?> value="BLACK">Black</option>
                            <option <?php if ($logo == 'WHITE') {echo ' selected="selected"'; } ?> value="WHITE">White</option>
                            <option <?php if ($logo == 'REV') {echo ' selected="selected"'; } ?> value="REV">Reverse</option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="Submit" value="<?php _e ( 'Update Options' ) ?>" /></p>
        </form>
        <hr />

        <h3>Guardian Logo</h3>
        <p>We have created a number of logos to match your theme.</p>

        <div class="guardian-logo-type" style="float:left; width:auto; padding:0 15px;">
            <p><strong>Normal</strong></p>
            <img style="border:1px dotted #464646" src="<?php echo plugin_dir_url(__FILE__) ?>/images/logo-normal.jpg">
        </div>

        <div class="guardian-logo-type" style="float:left; width:auto; padding:0 15px;">
            <p><strong>Black</strong></p>
            <img style="border:1px dotted #464646" src="<?php echo plugin_dir_url(__FILE__) ?>/images/logo-black.jpg">
        </div>

        <div class="guardian-logo-type" style="float:left; width:auto; padding:0 15px;">
            <p><strong>White</strong></p>
            <img style="border:1px dotted #464646" src="<?php echo plugin_dir_url(__FILE__) ?>/images/logo-white.jpg">
        </div>

        <div class="guardian-logo-type" style="float:left; width:auto; padding:0 15px;">
            <p><strong>Reverse</strong></p>
            <img style="border:1px dotted #464646" src="<?php echo plugin_dir_url(__FILE__) ?>/images/logo-reverse.jpg">
        </div>
    </div>

<?php
}

/**
 * Calls the Guardian API key, passing the object Wordpress needs to get_option
 *
 * @param $str_api_keyname		Should be defined in the master file.
 * @param $required				True or False, True means function will throw error is not present
 *
 */
function retrieve_api_key($str_api_keyname, $required = false) {
    $str_result = get_option ( $str_api_keyname );
    if ($required && empty($str_result) ) {
        $api_request_link = "http://guardian.mashery.com/";
        $error = new WP_Error('error', __("<div class=\"error\"><p><strong>You need a Guardian API Key to publish an article, <a href=\"{$api_request_link}\">please click here to request one.</a></p></strong></div>"));
        echo $error->get_error_message();
        return null;
    }
    if (!empty($str_result)) {
        $str_result = "&api-key=".$str_result;
    }
    return $str_result;
}

/**
 * This is the function that reloads the blog posts from the API
 *
 * This function should be scheduled for daily access
 */
function Guardian_ContentAPI_refresh_articles($update_article = true, $activate = false) {

    global $wpdb;

    $api = new GuardianOpenPlatformAPI();

    $str_api_key = get_option ( $api->guardian_api_keynameValue() );

    $api = new GuardianOpenPlatformAPI($str_api_key);

    $articles = $wpdb->get_results( "SELECT `post_id`, `meta_value` FROM $wpdb->postmeta WHERE meta_key = 'guardian_content_api_id'", ARRAY_A );

    if (!empty($articles)) {
        foreach ($articles as $article) {

            $arr_guard_article = array();
            $data = array();
            $find = array();
            $new_content = '';
            $tagarray = array();

            $post = get_post($article['post_id'], ARRAY_A);

            if ($post['post_status'] == 'publish') {
                $arr_guard_article = $api->guardian_api_item($article['meta_value']);
                sleep(1);
            }
            if ($activate) {

                // Get the tags
                $tags = $arr_guard_article ['tags'];
                $tagarray = array();
                foreach ($tags as $tag) {
                    $tag = trim($tag['webTitle']);
                    if (!empty($tag)) {
                        $tagarray[] = $tag;
                    }
                }
                $tagarray = implode(', ', $tagarray);

                if (empty($arr_guard_article ['fields'] ['body']) || $arr_guard_article ['fields'] ['body'] == '<!-- Redistribution rights for this field are unavailable -->') {
                    $new_content = "<p><strong>The content previously published here has been withdrawn.  We apologise for any inconvenience.</strong></p>";
                    $tagarray = array();
                } else {
                    // Article is fine and well
                    $new_content = guardian_article_build($arr_guard_article);
                }
                $replace = guardian_article_replace($post['post_content'],  $new_content);

                $data = array(
                    'ID' => $article['post_id'],
                    'post_title' => $arr_guard_article ['fields'] ['headline'],
                    'post_content' => $replace,
                    'tags_input' => $tagarray,
                    'post_author'=>$post['post_author']
                );
                wp_update_post($data);

                // Delete revisions
                $sql = "DELETE a,b,c FROM $wpdb->posts a LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id) LEFT JOIN $wpdb->postmeta c ON (a.ID = c.post_id) WHERE a.post_type = 'revision' AND a.post_parent = {$article['post_id']}";
                $wpdb->query($sql);


            } elseif (!empty($arr_guard_article) && $update_article) {

                if (empty($arr_guard_article ['fields'] ['body']) || $arr_guard_article ['fields'] ['body'] == '<!-- Redistribution rights for this field are unavailable -->') {
                    $new_content = "<p><strong>The content previously published here has been withdrawn.  We apologise for any inconvenience.</strong></p>";
                } else {
                    // Article is fine and well
                    $new_content = guardian_article_build($arr_guard_article);
                }
                $replace = guardian_article_replace($post['post_content'],  $new_content);

                $data = array(
                    'ID' => $article['post_id'],
                    'post_content' => $replace,
                    'post_author'=>$post['post_author']
                );
                wp_update_post($data);

                // Delete revisions
                $sql = "DELETE a,b,c FROM $wpdb->posts a LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id) LEFT JOIN $wpdb->postmeta c ON (a.ID = c.post_id) WHERE a.post_type = 'revision' AND a.post_parent = {$article['post_id']}";
                $wpdb->query($sql);

            } else {

                if ($post['post_status'] == 'publish') {
                    $tier_status = $api->guardian_get_tier();
                    $post['post_content'] = guardian_article_replace($post['post_content'],  "<p><strong>The content previously published here has been withdrawn.  We apologise for any inconvenience.</strong></p>");

                    if (!empty($tier_status)) {
                        $data = array(
                            'ID' => $article['post_id'],
                            'post_content' => $post['post_content'],
                            'post_author'=>$post['post_author']
                        );
                        wp_update_post($data);
                        // Delete revisions
                        $sql = "DELETE a,b,c FROM $wpdb->posts a LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id) LEFT JOIN $wpdb->postmeta c ON (a.ID = c.post_id) WHERE a.post_type = 'revision' AND a.post_parent = {$article['post_id']}";
                        $wpdb->query($sql);
                    }
                }
            }
        }
    }
}

/*
 * Function to replace the old content with the new.
 *
 * @param $content			Old Content from DB
 * @param $new_content		New content from the API
 */
function guardian_article_replace( $content, $new_content ) {
    return trim(preg_replace("/<!-- GUARDIAN WATERMARK -->.*?<!-- END GUARDIAN WATERMARK -->/s", "<!-- GUARDIAN WATERMARK -->{$new_content}<!-- END GUARDIAN WATERMARK -->", $content));
}

/*
 * Function to build the article from the API into a Wordpress Post.
 *
 * @param $arr_guard_article  Content from the API
 */
function guardian_article_build($arr_guard_article) {
    $new_content = array();
    $new_content[] = "<p><a href=\"{$arr_guard_article ['webUrl']}\">";
    $new_content[] = "<img class=\"alignright\" src=\"http://image.guardian.co.uk/sys-images/Guardian/Pix/pictures/2010/03/01/poweredbyguardian" . get_option('guardian_powered_image') . ".png\" alt=\"Powered by Guardian.co.uk\" width=\"140\" height=\"45\" />";
    $new_content[] = "This article titled \"{$arr_guard_article['fields']['headline']}\" was written by {$arr_guard_article['fields']['byline']}";
    $new_content[] = ", for {$arr_guard_article['fields']['publication']} on " . date("l jS F Y H.i e", strtotime($arr_guard_article['webPublicationDate']));
    $new_content[] = "</a></p>";

    if (!empty($arr_guard_article['mediaAssets'])) {
        foreach ($arr_guard_article['mediaAssets'] as $media) {
            if ($media['type'] == 'picture') {
                $new_content[] = "<img src=\"{$media['file']}\" class=\"aligncenter\" alt=\"{$media['fields']['caption']}\">";
            }
        }
    }
    $new_content[] = $arr_guard_article['fields']['body'];
    $new_content[] = "<p>guardian.co.uk &#169; Guardian News &amp; Media Limited 2010</p> <p>Published via the <a href=\"http://www.guardian.co.uk/open-platform/news-feed-wordpress-plugin\" target=\"_blank\" title=\"Guardian plugin page\">Guardian News Feed</a> <a href=\"http://wordpress.org/extend/plugins/the-guardian-news-feed/\" target=\"_blank\" title=\"Wordress plugin page\" >plugin</a> for WordPress.</p>";
    return join("", $new_content);
}

// JSON support
function guardian_id_got_json() {
    // WP 2.9+ handles everything for us
    if ( version_compare( get_bloginfo( 'version' ), '2.9', '>=' ) )
        return true;

    // Functions exists already, assume they're good to go
    if ( function_exists( 'json_encode' ) && function_exists( 'json_decode' ) )
        return true;

    // Load Services_JSON if we need it at this point
    if ( !class_exists( 'Services_JSON' ) )
        include_once( dirname( __FILE__ ) . '/class.json.php' );

    // This indicates that we need to define the functions.
    // Services_JSON *is* available one way or another at this point
    return false;
}

if ( !guardian_id_got_json() ) {
    function json_encode( $data ) {
        $json = new Services_JSON();
        return( $json->encode( $data ) );
    }

    function json_decode( $data ) {
        $json = new Services_JSON();
        return( $json->decode( $data ) );
    }
}


function Guardian_OpenPlatform_add_pages() {
    global $wpdb;
    if (function_exists ( "add_submenu_page" )) {
        add_submenu_page('options-general.php', __('Guardian News Feed'), __('Guardian News Feed'), 'manage_options', __FILE__, 'Guardian_OpenPlatform_settings_page');
    }
}
// Plugin admin menus
add_action ( "admin_menu", "Guardian_OpenPlatform_add_pages" );

/*
 * Code to enable the wordpress scheduling of refreshing the articles.
 */
register_activation_hook(__FILE__, 'activate_guardian_scheduling');
add_action('refresh_articles', 'Guardian_ContentAPI_refresh_articles');

/*
 * Activate the scheduling
 */
function activate_guardian_scheduling() {
    set_time_limit (0);
    Guardian_ContentAPI_refresh_articles(true, true);
    wp_schedule_event (time(), 'daily', 'refresh_articles');
}

/*
 * Code to deactivate plugin, remove the scheduler and remove the article contents. Plugin can be activated and contents will be restored.
 */
register_deactivation_hook(__FILE__, 'my_deactivation');

function my_deactivation() {
    set_time_limit  (0);
    Guardian_ContentAPI_refresh_articles(false);
    wp_clear_scheduled_hook('refresh_articles');
}

register_sidebar_widget ( __ ( 'The Guardian News Feed - Related Articles' ), 'widget_Guardian_Related' );

?>
