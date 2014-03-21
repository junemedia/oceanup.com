<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null;
/**
 * Plugin Name: OCEANUP Legacy URL Redirector
 * Plugin URI:  http://www.quadshot.com/
 * Description: Handles Legacy URLs for OCEANUP old site to new site migration
 * Version:     1.0
 * Author:      Quadshot
 * Author URI:  http://www.quadshot.com/
 * License:     GPL
 */

class qsou_legacy_url_redirector {
	public static function pre_init() {
		add_action('wp', array(__CLASS__, 'maybe_redirect'), 1, 1);
		add_action('wp', array(__CLASS__, 'maybe_redirect_404'), 2, 1);
	}

	public static function maybe_redirect($wp) {
		global $wp_query;
		if ($wp_query->is_404) {
			$uri = $_SERVER['REQUEST_URI'];
			$puri = parse_url($uri);
			$name = trim($puri['path'], '/');
		//if (isset($wp->query_vars['pagename']) && !empty($wp->query_vars['pagename'])) {
			//$name = $wp->query_vars['pagename'];
			global $wpdb;
			$q = $wpdb->prepare('select object_id, urltype from '.$wpdb->prefix.'qsou_legacy_urls where olduri = %s', $name);
			$row = $wpdb->get_row($q);
			if (is_object($row) && !is_wp_error($row)) {
				$id = $row->object_id;
				$type = $row->urltype;
				if (is_numeric($id) && !empty($id)) {
					switch($type) {
						case 'post_tag':
							$term = get_term($id, 'post_tag');
							$permalink = get_term_link($term, $term->taxonomy);
						break;

						default:
							$id = self::maybe_adjust_id($id);
							$permalink = get_permalink($id);
						break;
					}

					if (is_string($permalink) && !empty($permalink)) {
						wp_safe_redirect($permalink, 301);
						exit;
					}
				}
			}
		}
	}

	public static function maybe_redirect_404($wp) {
		global $wp_query;

		if ($wp_query->is_404) {
			$name = isset($wp->query_vars['name']) ? $wp->query_vars['name'] : '';
			if (!empty($name)) {
				global $wpdb;
				$q = $wpdb->prepare('select id from '.$wpdb->posts.' where post_name = %s', $name);
				$id = $wpdb->get_var($q);
				if ($id) {
					if (preg_match('#.*\.html#', $name)) {
						query_posts(array('p' => $id));
						$wp->handle_404();
						$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
					} else {
						$permalink = get_permalink($id);
						wp_safe_redirect($permalink, 301);
						exit;
					}
				}
			}
		}
	}

	public static function maybe_adjust_id($id) {
		global $wpdb;

		$q = $wpdb->prepare('select post_id from '.$wpdb->postmeta.' where meta_key = %s and meta_value = %s', '_legacy_nid', $id);
		$new_id = $wpdb->get_var($q);
		if (!empty($new_id) && is_numeric($new_id)) $id = $new_id;

		return $id;
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	qsou_legacy_url_redirector::pre_init();
}
