<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access
/**
 * Plugin Name: Quadshot Image Sitemap
 * Plugin URI:  http://quadshot.com/
 * Description: Provides a configurable image sitemap, which is highly scalable, and can be submitted to search engines, such as Google.
 * Version:     1.0.0
 * Author:      Quadshot Software LLC
 * Author URI:  http://quadshot.com/
 * License: OpenTickets Software License Agreement
 * License URI: http://opentickets.com/opentickets-software-license-agreement
 * Copyright 2013 Quadshot Software, LLC. All Rights Reserved.
 */

class QS_image_sitemap {
	protected static $version = '1.0.0';

	protected static $cache_path = '';
	protected static $cache_writable = false;
	protected static $tempname = '';

	protected static $plugin_url = '';
	protected static $plugin_dir = '';
	protected static $me = '';

	protected static $defaults = array(
		'uri' => '/image-sitemap.xml',
		'cache-time' => '+1 day',
		'no-parent-images' => 0,
		'all-image-sizes' => 0,
	);
	protected static $settings = array();

	protected static $xml_cache__cache = array();

	public static function pre_init() {
		global $wpdb;
		$wpdb->qsism_xml = $wpdb->prefix.'qsism_xml';
    add_filter('qs-upgrader-table-descriptions', array(__CLASS__, 'setup_db_tables'), 10); 

		self::_load_cache_path();

		self::$plugin_url = plugin_dir_url(__FILE__);
		self::$plugin_dir = plugin_dir_path(__FILE__);
		self::$me = plugin_basename(__FILE__);
		
		self::_load_settings();

		add_action('query_vars', array(__CLASS__, 'query_vars'), 10);
		add_filter('rewrite_rules_array', array(__CLASS__, 'rewrite_rules'), PHP_INT_MAX, 1);
		add_action('init', array(__CLASS__, 'register_assets'), 10);
		add_action('parse_request', array(__CLASS__, 'intercept_requests'), PHP_INT_MAX, 1);

		add_action('wp_ajax_qsism-ajax', array(__CLASS__, 'handle_ajax'), 10);
		add_filter('qsism-ajax/queue', array(__CLASS__, 'aj_queue'), 10, 3);
		add_filter('qsism-ajax/run-item', array(__CLASS__, 'aj_run_item'), 10, 3);

		add_action('wp_update_attachment_metadata', array(__CLASS__, 'update_attachment_xml'), 10, 2);

		if (is_admin()) {
			add_action('admin_init', array(__CLASS__, 'load_admin_assets'), 10);
			add_action('admin_menu', array(__CLASS__, 'admin_menu'), 100);

			if (isset($_GET['settings-updated']) && $_GET['settings-updated'] = 'true') {
				add_action('admin_footer-options-permalink.php', function() { global $wp_rewrite; __log($wp_rewrite->rules); });
			}

			if ($_GET['test'] == '1') die(self::_generate());
		}

		require_once self::$plugin_dir.'inc/db-upgrade.php';
	}

	public static function query_vars($vars) {
		$vars[] = 'qsism';
		$vars[] = 'qsism-page';
		$vars[] = 'qsism-gzip';
		return array_unique($vars);
	}

	public static function rewrite_rules($rules) {
		$uri = ltrim(self::$settings['uri'], '/');
		$uri = explode('.', $uri);
		if (count($uri) > 1) {
			$ext = array_pop($uri);
			$uri = implode('.', $uri);
			$uri = '^'.preg_quote($uri).'(-(\d+?))?.'.$ext.'(\.(gz))?$';

		} else {
			$uri = '^'.preg_quote(array_pop($uri)).'(-(\d+))?(\.(gz))?$';
		}
		$new_rules = array(
			$uri => 'index.php?qsism=1&qsism-page=$matches[2]&qsism-gzip=$matches[4]',
		);
		return $new_rules + $rules;
	}

	public static function register_assets() {
		wp_register_style('qsism-admin', self::$plugin_url.'assets/css/admin/primary.css', array(), self::$version);
		wp_register_style('qsism-settings', self::$plugin_url.'assets/css/admin/settings.css', array(), self::$version);
		wp_register_script('qsism-settings', self::$plugin_url.'assets/js/admin/settings.js', array('jquery'), self::$version);
	}

	public static function load_admin_assets() {
		wp_enqueue_style('qsism-admin');
	}

	public static function intercept_requests(&$wp) {
		if (!isset($wp->query_vars['qsism']) || empty($wp->query_vars['qsism'])) return;

		// if we have nt generated a sitemap at all...
		$pages = self::_get_page_list();
		if (empty($pages)) {
			// if the user is logged in, direct them to the login page, to generate the sitemap
			if (is_user_logged_in() && current_user_can('manage_options')) {
				$url = add_query_arg(array('page' => 'qsism-settings', 'msg' => 'redirected'), admin_url('options-general.php'));
				wp_safe_redirect($url);
				exit;
			} else {
				return;
			}
		}

		$page = isset($wp->query_vars['qsism-page']) ? (int)$wp->query_vars['qsism-page'] : 0;
		$gzip = isset($wp->query_vars['qsism-gzip']) ? !!$wp->query_vars['qsism-gzip'] : false;
		self::_serve($page, $gzip);
	}

	protected static function _filename($page, $gzip=false, $keyonly=false) {
		$key = md5(site_url().NONCE_SALT);
		return $keyonly ? $key : $key.'-'.$page.'.xml'.($gzip ? '.gz' : '');
	}

	protected static function _past_expiration($filename) {
		$ftime = file_exists($filename) ? filemtime($filename) : time();
		$target_time = strtotime(self::$settings['cache-time'], $ftime);
		return time() > $target_time;
	}

	protected static function _would_exist($filename) {
		$list_file = self::_filename(0, false);
		if (!file_exists(self::$cache_path.$filename)) return false;
		$xml = simplexml_load_file(self::$cache_path.$filename);
		foreach ($xml->sitemap as $sitemap) {
			if (preg_match('#.*'.preg_quote($list_file, '#').'$#', (string)$sitemap->loc)) {
				return true;
			}
		}
		return false;
	}

	protected static function _serve($page, $gzip) {
		$filename = self::_filename($page, $gzip);

		if (file_exists(self::$cache_path.$filename) && !self::_past_expiration($filename)) {
			header('Content-Type: application/xml');
			$file = fopen(self::$cache_path.$filename, 'rb');
			fpassthru($file);
			exit;
		} else if ($page != 0 && self::_would_exist($filename)) {
			self::_generate_single_file($page, 0, 0);
			if (file_exists(self::$cache_path.$filename) && !self::_past_expiration($filename)) {
				header('Content-Type: application/xml');
				$file = fopen(self::$cache_path.$filename, 'rb');
				fpassthru($file);
				exit;
			}
		} else if ($page == 0) {
			self::_generate();
			if (file_exists(self::$cache_path.$filename) && !self::_past_expiration($filename)) {
				header('Content-Type: application/xml');
				$file = fopen(self::$cache_path.$filename, 'rb');
				fpassthru($file);
				exit;
			}
		}
	}

	protected static function _loop_limit() {
    static $max = false;
    if ($max === false) {
      $raw = ini_get('memory_limit');
      preg_match_all('#^(\d+)(\s*)?$#', $raw, $matches, PREG_SET_ORDER);
      if (isset($matches[0])) {
        $max = $matches[0][1];
        $unit = $matches[0][2];
        switch (strtolower($unit)) {
          case 'k': $max *= 1024; break;
          case 'm': $max *= 1048576; break;
          case 'g': $max *= 1073741824; break;
        }   
      } else {
        $max = 32 * 1048576;
      }   
    }   
		
		$mbs = $max / 1048576;

		$amt = 0;
		if ($mbs > 250) $amt = 15000;
		else if ($mbs > 125) $amt = 7500;
		else if ($mbs > 50) $amt = 3000;
		else $amt = 2000;

		return $amt;
	}

	protected static function _load_xml_cache($img_ids, $append=false) {
		global $wpdb;
		if (!$append) self::$xml_cache__cache = array();
		if (empty($ids)) return;

		$data = $wpdb->get_row('select post_id, xml_cache, xml_extra_cache, extra_count from '.$wpdb->qsism_xml.' where post_id in ('.implode(',', $img_ids).')');
		foreach ($data as $item) self::$xml_cache__cache[$item->post_id.''] = array($item->xml_cache, $item->xml_extra_cache, $item->extra_count);
	}

	protected static function _get_xml_cache($post_id) {
		if (isset(self::$xml_cache__cache[$post_id.''])) return self::$xml_cache__cahce[$post_id.''];
		global $wpdb;

		$data = $wpdb->get_row($wpdb->prepare('select xml_cache, xml_extra_cache, extra_count from '.$wpdb->qsism_xml.' where post_id = %d', $post_id));
		return is_object($data) && isset($data->xml_cache, $data->xml_extra_cache, $data->extra_count) ? array($data->xml_cache, $data->xml_extra_cache, $data->extra_count) : false;
	}

	protected static function _set_xml_cache($post_id, $xml, $xml_extra='', $extra_count=0) {
		global $wpdb;
		$wpdb->query($wpdb->prepare('replace into '.$wpdb->qsism_xml.' (post_id, xml_cache, xml_extra_cache, extra_count) values (%d, %s, %s, %d)', $post_id, $xml, $xml_extra, $extra_count));
		self::$xml_cache__cache[$post_id.''] = array($xml, $xml_extra, $extra_count);
	}

	public static function update_attachment_xml($meta, $att_id) {
		$att = get_post($att_id);
		if (substr($att->post_mime_type, 0, 6) == 'image/') {
			$att->meta = $meta;
			self::_image_xml($att, true);
		}
	}

	protected static function _image_xml($img, $force=false) {
		if (!$force && ( $cache = self::_get_xml_cache($img->ID) ) !== false) {
			return $cache;
		}

		static $uploads_url = false;
		if ($uploads_url === false) {
			$u = wp_upload_dir();
			$uploads_url = trailingslashit($u['baseurl']);
		}

		$img->meta = isset($img->meta) && is_array($img->meta) ? $img->meta : get_post_meta($img->ID, '_wp_attachment_metadata', true);
		$out = $sizes = '';
		if (!is_array($img->meta)) return array($out, $sizes);

		$img_url = $uploads_url.$img->meta['file'];
		$base_url = dirname($img_url).'/';

		$out .= '<image:image>';
		$out .= '<image:loc>'.apply_filters('the_title', $img_url).'</image:loc>';
		$caption = '';
		if (isset($img->meta['image_meta'], $img->meta['image_meta']['caption']) && !empty($img->meta['image_meta']['caption']))
			$out .= ($caption = '<image:caption><![CDATA['.strip_tags(apply_filters('the_content',
				str_replace(array(' ', "\n", "\r", "\t", "\0", "\x0B"), ' ', $img->meta['image_meta']['caption']))).']]></image:caption>');
		$out .= '<image:title>'.apply_filters('the_title', $img->post_title).'</image:title>';
		$out .= '</image:image>';

		if (isset($img->meta['sizes']) && is_array($img->meta['sizes'])) foreach ($img->meta['sizes'] as $size => $args) {
			$sizes .= '<image:image>';
			$sizes .= '<image:loc>'.apply_filters('the_title', $base_url.$args['file']).'</image:loc>';
			$sizes .= $caption;
			$sizes .= '<image:title>'.apply_filters('the_title', $img->post_title.' '.$args['width'].'x'.$args['height']).'</image:title>';
			$sizes .= '</image:image>';
		}

		self::_set_xml_cache($img->ID, $out, $sizes, count($img->meta['sizes']));

		return array($out, $sizes);
	}

	protected static function _base_filename($filename) {
		$base_filename = basename($filename);
		$base_filename = explode('.', $base_filename);
		if (count($base_filename) > 1)
			array_pop($base_filename);
		return implode('.', $base_filename);
	}

	protected static function _temp_filename($force=false) {
		if ($force) return self::$tempname = $force;
		if (!empty(self::$tempname)) return self::$tempname;
		self::$tempname = self::_filename(uniqid('temp'));
		return self::$tempname;
	}

	protected static function _get_page_list() {
		$page_list = get_option('_qsism_page_list', array());
		return is_array($page_list) ? $page_list : array();
	}

	protected static function _update_page_list($list=array(), $force=false) {
		$page_list = $force ? array() : self::_get_page_list();
		$page_list = array_merge($page_list, $list);
		update_option('_qsism_page_list', $page_list);
	}

	protected static function _generate_single_file($page, $min_id=0, $max_id=0, $force=false) {
		global $wpdb;
		static $setup = false, $uri = '', $uri_format = '', $post_types = array(), $per_loop = 0, $max_count = 0, $max_size = 0, $thresh_size = 0, $xml_top = '',
			$urlsets = '', $urlsets_end = '', $u = array(), $uploads_url = '';

		if ($setup === false) {
			$uri = ltrim(self::$settings['uri'], '/');
			$uri = explode('.', $uri);
			$ext = count($uri) > 1 ? '.'.array_pop($uri) : '';
			$uri = implode('.', $uri);
			$url_format = site_url().$uri.'%s'.$ext;

			$post_types = get_post_types(array('public' => true), 'names');
			if ($pos = array_search('attachment', $post_types)) unset($post_types[$pos]);
			$post_types = array_values($post_types);

			$per_loop = self::_loop_limit();

			$max_count = 50000;
			$max_size = 52428800;
			$thresh_size = 0.95 * $max_size;

			$xml_top = '<?xml version="1.0" encoding="UTF-8"?'.'>'."\n".'<?xml-stylesheet type="text/xsl" href="'.esc_attr(self::$plugin_url.'assets/css/xslt.php').'" ?>'."\n";
			$urlsets = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';
			$urlsets_end = '</urlset>';
			$setup = true;

			$u = wp_upload_dir();
			$uploads_url = $u['baseurl'];
		}

		$icount = $count = 0;
		$size = 0;
		$offset = 0;
		$last_id = $first_id = 0;

		$page_list = self::_get_page_list();

		$filename = self::_filename($page);
		$base_filename = self::_base_filename($filename);
		if (!$force && isset($page_list[$base_filename]) &&
				file_exists(self::$cache_path.$filename) &&
				is_readable(self::$cache_path.$filename) &&
				filesize(self::$cache_path.$filename) > strlen($xml_top) + 2 + strlen($urlsets) + strlen($urlsets_end)
			)
			return array($page_list[$base_filename]['min_id'], $page_list[$base_filename]['max_id'], 0, $pag_list[$base_filename]['icount']); // already generated and cached with an appropriate timer

		if (!$force && isset($page_list[$base_filename])) {
			$min_id = $page_list[$base_filename]['min_id'];
			$max_id = $page_list[$base_filename]['max_id'];
		}

		if (empty($min_id) && empty($max_id) && $page > 1) {
			$last_page = self::_base_filename(self::_filename($page-1));
			if ($last_page && isset($page_list[$last_page])) {
				$min_id = $page_list[$last_page]['max_id'] + 1;
			}
		}

		$q = self::$settings['no-parent-images']
			? 'select id, post_type, post_title from '.$wpdb->posts.' where '
					.'('
						.'(post_type in("'.implode('","', $post_types).'") and post_status = %s) or '
						.'(post_type = %s and post_parent = 0 and post_mime_type like %s) '
					.')'
					.(is_numeric($min_id) && $min_id > 0 ? ' and id >= '.$min_id : '')
					.(is_numeric($max_id) && $max_id > 0 ? ' and id <= '.$max_id : '')
				.' order by id asc limit %d offset %d'
			: 'select id from '.$wpdb->posts.' where post_type in("'.implode('","', $post_types).'") and post_status = %s order by post_date asc, id asc limit %d offset %d';
		$with_children_q = 'select post_parent from '.$wpdb->posts.' where post_parent in (%%POST_PARENT%%) and post_mime_type like %s group by post_parent having count(id) > 0';
		$child_images = 'select id, post_title from '.$wpdb->posts.' where post_type = %s and post_mime_type like %s and post_parent = %d order by post_date asc, id asc';
		$child_image_ids = 'select id from '.$wpdb->posts.' where post_type = %s and post_mime_type like %s and post_parent in (%%POST_PARENT%%) order by post_date asc, id asc';

		$file = fopen(self::$cache_path.$filename, 'w+');
		$size += ($olen = strlen($xml_top.$urlsets));
		fwrite($file, $xml_top.$urlsets, $olen);

		$tempname = self::_temp_filename();
		if (file_exists(self::$cache_path.$tempname) && filesize(self::$cache_path.$tempname)) {
			$tempfile = fopen(self::$cache_path.$tempname, 'r+');
			while ($data = fread($tempfile, 1024)) {
				$size += ($datalen = strlen($data));
				fwrite($file, $data, $datalen);
			}
			fclose($tempfile);
		}
		$tempfile = fopen(self::$cache_path.$tempname, 'w+');

		//echo "$size : $thresh_size ; $count + $per_loop < $max_count ;\n";
		
		while ($size < $thresh_size && $count + $per_loop < $max_count && ($posts = $wpdb->get_results(self::$settings['no-parent-images']
				? $wpdb->prepare($q, 'publish', 'attachment', 'image/%', $per_loop, $offset)
				: $wpdb->prepare($q, 'publish', $per_loop, $offset)
		))) {
			self::_memory_check();
			$offset += $per_loop;

			$img_ids = $post_ids = array();
			foreach ($posts as $post)
				if ($post->post_type == 'attachment')
					$img_ids[] = $post->id.'';
				else
					$post_ids[] = $post->id.'';

			$post_ids = $wpdb->get_col($wpdb->prepare(str_replace('%%POST_PARENT%%', implode(',', $post_ids), $with_children_q), 'image/%'));
			
			if (empty($post_ids) && empty($img_ids)) continue;

			$all_img_ids = array_merge($img_ids, $wpdb->get_col($wpdb->prepare(str_replace('%%POST_PARENT%%', implode(',', $post_ids), $child_image_ids), 'attachment', 'image/%')));
			self::_load_xml_cache($all_img_ids);

			$img_ids = array_flip($img_ids);
			$post_ids = array_flip($post_ids);

			while ($parent = array_shift($posts)) {
				if ($first_id === 0) $first_id = $parent->id;
				/*
				if ($count == $max_count || $size > $thresh_size) {
					fwrite($file, $urlsets_end, strlen($urlsets_end));
					fclose($file);
					$page += 1;
					$filename = self::_filename($page);
					$file = fopen(self::$cache_path.$filename, 'w+');
					$size = 0;
					$count = 0;
					$size += ($olen = strlen($xml_top.$urlsets));
					fwrite($file, $xml_top.$urlsets, $olen);
				}
				*/

				if ($parent->post_type == 'attachment' && isset($img_ids[$parent->id.''])) {
					$count += 1;
					$icount += 1;

					$parent->ID = $parent->id;
					@list($ixml, $ixml_all) = self::_image_xml($parent);

					$out = '<url><loc>'.get_permalink($parent->id).'</loc>';
					$out .= $ixml.(self::$settings['all-image-sizes'] ? $ixml_all : '');
					$out .= '</url>';
					$size += ($olen = strlen($out));
					fwrite($tempfile, $out, $olen);
				} else if ($parent->post_type != 'attachment' && isset($post_ids[$parent->id.''])) {
					$count += 1;

					$out = '<url><loc>'.get_permalink($parent->id).'</loc>';
					$size += ($olen = strlen($out));
					fwrite($tempfile, $out, $olen);
					$images = $wpdb->get_results($wpdb->prepare($child_images, 'attachment', 'image/%', $parent->id));

					foreach ($images as $image) {
						$icount += 1;
						$image->ID = $image->id;
						@list($ixml, $ixml_all) = self::_image_xml($image);
						$out = $ixml.(self::$settings['all-image-sizes'] ? $ixml_all : '');
						$size += ($olen = strlen($out));
						fwrite($tempfile, $out, $olen);
					}

					$out = '</url>';
					$size += ($olen = strlen($out));
					fwrite($tempfile, $out, $olen);
				}

				$last_id = $parent->id;

				fclose($tempfile);
				clearstatcache(true, self::$cache_path.$tempname);
				if ($size < $thresh_size) {
					$tempfile = fopen(self::$cache_path.$tempname, 'r+');
					while ($data = fread($tempfile, 1024)) {
						$datalen = strlen($data);
						fwrite($file, $data, $datalen);
					}
					$tempfile = fopen(self::$cache_path.$tempname, 'w+');
				} else {
					break 2;
				}
			}
		}

		fwrite($file, $urlsets_end, strlen($urlsets_end));
		fclose($file);

		if ($count > 0) {
			$page_list[$base_filename] = array('min_id' => $first_id, 'max_id' => $last_id, 'icount' => $icount);
			self::_update_page_list($page_list);
		}

		return array($first_id, $last_id, $count, $icount);
	}

	protected function _get_highest_page() {
		$regex = '#^'.self::_base_filename(self::_filename('(\d+)')).'$#si';

		$page_list = self::_get_page_list();
		$max = 0;

		foreach ($page_list as $page_name => $settings) {
			$max = max($max, (int)preg_replace($regex, '$1', $page_name));
		}

		return $max;
	}

	protected static function _number_from_filename($filename, $is_nice = false) {
		static $setup = false, $filename_regex = '', $nicename_regex = '';
		if ($setup === false) {
			$filename_regex = '#^'.self::_filename('(\d+)').'$#si';
			$nicename_regex = '#^'.self::_base_filename(self::_filename('(\d+)')).'$#si';
		}

		$number = preg_replace( $is_nice ? $nicename_regex : $filename_regex, '$1', $filename);
		return $number == $filename ? 0 : $number;
	}

	protected static function _filename_to_url($filename, $is_nice = false, $is_number=false) {
		static $setup = false, $uri = '', $uri_format = '';
		if ($setup === false) {
			$uri = ltrim(self::$settings['uri'], '/');
			$uri = explode('.', $uri);
			$ext = count($uri) > 1 ? '.'.array_pop($uri) : '';
			$uri = implode('.', $uri);
			$url_format = trailingslashit(site_url()).$uri.'%s'.$ext;
		}

		if ($is_number) {
			$number = $filename;
		} else {
			$number = self::_number_from_filename($filename, $is_nice);
		}

		return $number > 0 ? sprintf($url_format, '-'.$number) : sprintf($url_format, '');
	}

	protected static function _generate($force=false) {
		if ($force)
			self::_update_page_list(array(), true);
		$highest_page = !$force ? self::_get_highest_page() : 0;
		self::$tempname = '';

		$page = $first_id = $last_id = 0;
		do {
			$page += 1;
			list($first_id, $last_id, $count) = self::_generate_single_file($page, $last_id+1, 0, $force || ($page == $highest_page));
		} while ($page < $highest_page || (int)$count > 0);

		// delete last file made in the above loop, because it is empty
		if ($page > 1) {
			$fname = self::$cache_path.self::_filename($page);
			if (file_exists($fname))
				unlink($fname);
		}

		// delete the temp file so that we dont junk up the directory
		$tempname = self::_temp_filename();
		if (file_exists(self::$cache_path.$tempname))
			unlink(self::$cache_path.$tempname);

		self::_generate_index();
	}

	protected static function _generate_index() {
		$page_list = self::_get_page_list();

		$filename = self::_filename(0);
		$file = fopen(self::$cache_path.$filename, 'w+');
		$xml_top = '<?xml version="1.0" encoding="UTF-8"?'.'>'."\n".'<?xml-stylesheet type="text/xsl" href="'.esc_attr(self::$plugin_url.'assets/css/xslt.php').'" ?>'."\n";
		$out = $xml_top.'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		fwrite($file, $out, strlen($out));
		if (is_array($page_list) && count($page_list)) foreach ($page_list as $nicename => $args) {
			$ftime = file_exists(self::$cache_path.$nicename.'.xml') ? filemtime(self::$cache_path.$nicename.'.xml') : time();
			$out = '<sitemap><loc>'.self::_filename_to_url($nicename, true).'</loc><lastmod>'.date(DATE_ATOM, $ftime).'</lastmod></sitemap>';
			fwrite($file, $out, strlen($out));
		}
		$out = '</sitemapindex>';
		fwrite($file, $out, strlen($out));
		fclose($file);
	}

  protected static function _memory_check($force=false) {
    global $wpdb;
    static $max = false;
    if ($max === false) {
      $raw = ini_get('memory_limit');
      preg_match_all('#^(\d+)(\s*)?$#', $raw, $matches, PREG_SET_ORDER);
      if (isset($matches[0])) {
        $max = $matches[0][1];
        $unit = $matches[0][2];
        switch (strtolower($unit)) {
          case 'k': $max *= 1024; break;
          case 'm': $max *= 1048576; break;
          case 'g': $max *= 1073741824; break;
        }   
      } else {
        $max = 32 * 1048576;
      }   
    }   
    $usage = memory_get_usage();
    if ($usage > $max * 0.8 || $force) {
      wp_cache_flush();
      $wpdb->queries = array();
    }   
  }

	public static function admin_menu() {
		$hook = add_options_page(
			'Quadshot Image Sitemap Settings',
			'<span class="qs-brand">[QS]</span> Image Sitemap',
			'manage_options',
			'qsism-settings',
			array(__CLASS__, 'ap_settings')
		);

		add_action('load-'.$hook, array(__CLASS__, 'ap_head_settings'), 10);
	}

	public static function handle_ajax() {
		if (!is_admin()) return;
		if (!current_user_can('manage_options')) return;
		//if (!isset($_POST['n']) || !wp_verify_nonce($_POST['n'], 'qsism-ajax-request')) return;

		$resp = array(
			'r' => array(),
			'e' => array(),
			'm' => array(),
			's' => 0,
		);
		$sa = isset($_POST['sa']) ? $_POST['sa'] : false;

		if (!has_action('qsism-ajax/'.$sa)) {
			$resp['e'][] = 'Invalid Request.';
		} else {
			$resp = apply_filters('qsism-ajax/'.$sa, $resp, $_POST, $sa);
		}

		header('Content-Type: application/json');
		echo @json_encode($resp);
		exit;
	}

	protected static function _truncate_xml_cache() {
		global $wpdb;
		$wpdb->query('truncate table '.$wpdb->qsism_xml);
	}

	public static function aj_queue($response, $post, $sa) {
		global $wpdb;

		if ($post['scratch'] > 0) self::_truncate_xml_cache();

		$icount = $wpdb->get_var($wpdb->prepare(
			'select count(id) from '.$wpdb->posts.' as p left join '.$wpdb->qsism_xml.' as x on x.post_id = p.id where post_type = %s and post_mime_type like %s and x.post_id is null',
			'attachment',
			'image/%'
		));

		$max_per = self::_loop_limit();
		$min_runs = ceil(($icount+1) / $max_per);
		//$min_runs = max($min_runs, $icount < 200 ? 1 : 200);
		$per_perc = 100 / $min_runs;

		$per = ceil($icount / $min_runs);
		$offset = 0;

		for ($i=1; $i<=$min_runs; $i++) {
			$response['r'][] = array(
				'run' => array(
					'do' => 'images',
					'off' => $offset,
					'per' => $per,
					'perc' => round($i * $per_perc, 2),
				),
			);
			$offset += $per;
		}

		// full image count for time estimation
		$icount = $wpdb->get_var($wpdb->prepare('select count(id) from '.$wpdb->posts.' where post_type = %s and post_mime_type like %s', 'attachment', 'image/%'));

		$sitemaps = array('do' => 'sitemaps', 'page' => 1, 'all' => 1);
		if (isset($post['page']) && $post['page'] > 0) {
			$sitemaps['page'] = $post['page'];
			$sitemaps['all'] = 0;
		}
		if (isset($post['force']) && $post['force'] > 0) $sitemaps['force'] = 1;

		$response['r'][] = array(
			'before' => array(
				'msg' => 'Creating Sitemaps, one at a time',
				'duration' => 30 * ceil($icount / 20000),
			),
			'run' => $sitemaps,
		);

		$response['s'] = 1;

		return $response;
	}

	public static function aj_run_item($response, $post, $sa) {
		switch ($post['do']) {
			case 'images':
				global $wpdb;

				$images = $wpdb->get_results($wpdb->prepare(
					'select id, post_title from '.$wpdb->posts.' as p left join '.$wpdb->qsism_xml.' as x on x.post_id = p.id '
							.'where post_type = %s and post_mime_type like %s and x.post_id is null order by id asc limit %d offset %d',
					'attachment',
					'image/%',
					$post['per'],
					$post['off']
				));
				foreach ($images as $image) {
					$image->ID = $image->id;
					$res = self::_image_xml($image);
				}
				
				$response['s'] = 1;
				$response['r'] = array(
					'perc' => $post['perc'],
					'msg' => 'Preparations are ('.$post['perc'].'%) Complete',
				);
			break;

			case 'sitemaps':
				$ts = microtime(true);
				global $wpdb;
				
				$force = isset($post['force']) && $post['force'];

				if ( (!isset($post['all']) || !$post['all']) && isset($post['page']) && $post['page'] > 0 ) {
					$filename = self::_filename($post['page']);
					$base_filename = self::_base_filename($filename);
					$page_list = self::_get_page_list();

					$min_id = $max_id = 0;
					if (!$force && isset($page_list[$base_filename])) {
						$min_id = $page_list[$base_filename]['min_id'];
						$max_id = $page_list[$base_filename]['max_id'];
					}

					if ($min_id > 0 && $max_id > 0) {
						$icount = $wpdb->get_var($wpdb->prepare('select count(id) from '.$wpdb->posts.' where id between %d and %d', $min_id, $max_id));
					} else {
						$icount = $wpdb->get_var($wpdb->prepare('select count(id) from '.$wpdb->posts.' where post_type = %s and post_mime_type like %s', 'attachment', 'image/%'));
					}

					$est_time = 30 * ceil($icount / 15000);
					ini_set('max_execution_time', $est_time);

					self::_generate_single_file($post['page'], $min_id, $max_id, true);

					// delete the temp file so that we dont junk up the directory
					$tempname = self::_temp_filename();
					if (file_exists(self::$cache_path.$tempname))
						unlink(self::$cache_path.$tempname);

					$response['s'] = 1;
					$response['r'] = array(
						'perc' => 100,
						'msg' => 'Completed XML generation in ['.(microtime(true) - $ts).'s] for page ['.$post['page'].'].',
					);
				} else {
					if (isset($post['tempname']))
						self::_temp_filename($post['tempname']);

					$icount = $wpdb->get_var($wpdb->prepare('select count(id) from '.$wpdb->posts.' where post_type = %s and post_mime_type like %s', 'attachment', 'image/%'));

					$est_time = 30 * ceil($icount / 15000);
					ini_set('max_execution_time', $est_time);

					if (isset($post['all'], $post['page'])) {
						if ($post['page'] == 1 && $force) self::_update_page_list(array(), true);
						$highest_page = !$force ? self::_get_highest_page() : 0;
						$prev = isset($post['prev']) ? $post['prev'] : 0;

						list($first_id, $last_id, $count, $ic) = self::_generate_single_file($post['page'], 0, 0, $force);
						self::_memory_check();
						$total_ic = $prev + $ic;

						if ($ic > 0) {
							$response['s'] = 1;
							$response['r'] = array(
								'perc' => ($total_ic / $icount) * 100,
								'msg' => 'Completed XML generation in ['.(microtime(true) - $ts).'s] for page ['.$post['page'].']...',
								'action' => array(
									'do' => 'queue',
									'items' => array(
										array('action' => array('do' => 'wait', 'duration' => 500)),
										array(
											'before' => array(
												'msg' => 'Mapped ['.$ic.'] more images. Creating next sitemap.',
												'duration' => 30 * ceil($ic / 20000),
												'start_perc' => ($total_ic / $icount) * 100,
												'end_perc' => (($total_ic + $ic) / $icount) * 100,
											),
											'run' => array(
												'do' => 'sitemaps',
												'all' => $post['all'],
												'page' => $post['page'] + 1,
												'tempname' => self::$tempname,
												'prev' => $total_ic,
												'force' => $force,
											),
										),
									),
								),
							);
						} else {
							// delete last file made in the above loop, because it is empty
							if ($post['page'] > 1) {
								$fname = self::$cache_path.self::_filename($post['page']);
								if (file_exists($fname))
									unlink($fname);
							}

							// delete the temp file so that we dont junk up the directory
							$tempname = self::_temp_filename();
							if (file_exists(self::$cache_path.$tempname))
								unlink(self::$cache_path.$tempname);

							self::_generate_index();
							self::_memory_check();
							$siurl = self::_filename_to_url(0, false, true);

							$response['s'] = 1;
							$response['r'] = array(
								'perc' => 100,
								'msg' => 'Completed sitemap creation. The sitemap index is viewable at <a href="'.esc_attr($siurl).'" target="_blank">'.$siurl.'</a>.',
							);
						}
					} else {
						self::_generate($force);
						self::_memory_check();

						$response['s'] = 1;
						$response['r'] = array(
							'perc' => 100,
							'msg' => 'Completed XML generation in ['.(microtime(true) - $ts).'s].',
						);
					}
				}
			break;
		}

		return $response;
	}

	public static function ap_head_settings() {
		wp_enqueue_style('qsism-settings');
		wp_enqueue_script('qsism-settings');
		wp_localize_script('qsism-settings', '_qsism_settings', array(
			'n' => wp_create_nonce('qsism-ajax-request'),
			'act' => 'qsism-ajax',
		));

		if (empty($_POST)) return;
		if (!current_user_can('manage_options')) return;
		if (!isset($_POST['qsism-submit']) || !wp_verify_nonce($_POST['qsism-submit'], 'qsism/settings/save')) return;

		if (isset($_POST['qsism']) && is_array($_POST['qsism'])) {
			self::_save_settings($_POST['qsism']);
			flush_rewrite_rules();
			wp_safe_redirect(add_query_arg(array('updated' => 1)));
			exit;
		}
	}

	public static function ap_settings() {
		$settings = self::$settings;
		$writable = self::$cache_writable;
		?>
			<div class="wrap">
				<h2>Quadshot Image Sitemap Settings</h2>

				<div class="inner">
					<form action="<?php echo remove_query_arg(array('msg', 'updated')) ?>" method="post" class="qsism-form">

						<?php
							if (isset($_GET['msg'])): switch ($_GET['msg']) {
								case 'redirected':
									?>
										<div class="warning">
											You have been redirected here because you attempted to access your sitemap,
											which you have not yet genereated.
											Since you are a logged in user, you were directed here, so that you can generated it.
											Not to worry though.
											Users without an admin account, would not be directed to this page, and instead get a 404 currently.
										</div>
									<?php
								break;
							}
							endif;
						?>

						<div class="section">
							<h3 class="section-heading">Locations</h3>
							<div class="fields">
								<div class="field">
									<label for="qsism-url">URI to host Image Sitemap at</label>
									<table cellspacing="0" class="field-with-pre">
										<tbody>
											<tr>
												<td width="1%"><?php echo esc_html(rtrim(site_url(), '/\\')) ?></td>
												<td><input type="text" class="widefat" id="qsism-url" name="qsism[uri]" value="<?php echo esc_attr($settings['uri']) ?>" /></td>
											</tr>
										</tbody>
									</table>
									<div class="helper">The URI where you want to make your image sitemap available. ex: <code>/image-sitemap.xml</code></div>
								</div>

								<div class="field">
									<label for="qsism-cache-time">Cache Expiration Time</label>
									<?php if (!$writable) :?>
										<div class="warning">
											<code><?php echo esc_html(self::$cache_path) ?></code> <strong>is not</strong> currently writable.
											This directory is used to store a cached version of your image sitemap.
											We need this writable to improve Image Sitemap delivery performance.
										</div>
									<?php else: ?>
										<div class="confirmation">
											<code><?php echo esc_html(self::$cache_path) ?></code> <strong>is</strong> currently writable.
											This is where we store the cached version of your Image Sitemap.
											Good.
										</div>
									<?php endif; ?>
									<input type="text" class="widefat" id="qsism-cache-time" name="qsism[cache-time]" value="<?php echo esc_attr($settings['cache-time']) ?>" />
									<div class="helper">
										Description of how long to keep the Image Sitemap cached, before it is regenerated.
										Exs: <code>+1 day</code>, <code>+4 hours</code>, <code>+2 hours 30 minutes 10 seconds</code>.
										For full documentation of the accepted formats, checkout 
										<a href="http://us2.php.net/manual/en/function.strtotime.php" title="PHP strtotime() function">strtotime()</a> and 
										<a href="http://us2.php.net/manual/en/datetime.formats.relative.php" title="PHP Relative Time Documentation">Relative Time</a>.
									</div>
								</div>
							</div>
						</div>

						<div class="section">
							<h3 class="section-heading">Content</h3>

							<div class="helper">
								By default, this plugin creates a list of all fullsized versions, of all images, attached to posts.
								This is a list of the other options that are available, which add to that base list.
							</div>

							<div class="fields">
								<div class="field">
									<label for="qsism-no-parent-images">Add Unattached Images</label>
									<input type="hidden" name="qsism[no-parent-images]" value="0" />
									<input type="checkbox" id="qsism-no-parent-images" name="qsism[no-parent-images]" value="1" <?php checked((int)$settings['no-parent-images'], 1) ?> />
									Yes, add any images that are not directly attached to a specific post.
									<div class="helper">
										Sometimes images become un associated to a specific post/page/post_type.
										Any time you direclty upload to your media library, this happens.
										It can also happen because of a site migration from an old system.
										Either way, it does happen.
										With this checked, you will add those images to the list, and Google will know where to find them.
									</div>
								</div>

								<div class="field">
									<label for="qsism-all-image-sizes">Add All Image Sizes</label>
									<input type="hidden" name="qsism[all-image-sizes]" value="0" />
									<input type="checkbox" id="qsism-all-image-sizes" name="qsism[all-image-sizes]" value="1" <?php checked((int)$settings['all-image-sizes'], 1) ?> />
									Yes, add an entry for every different size of every image, on my site.
									<div class="helper">
										By default, only the 'fullsize' image url is added to the sitemap.
										With this checked, an entry is added for each individual size available on an image.
									</div>
								</div>
							</div>
						</div>

						<div class="section">
							<h3 class="section-heading">Generate</h3>
							
							<div class="fields">
								<div class="field">
									<label>Existing Sitemaps</label>

									<?php $page_list = self::_get_page_list(); ?>
									<?php if (is_array($page_list) && count($page_list)): ?>
										<div class="helper" id="sm-regen-all">
											It is almost always <u>best</u> to 'REGEN ALL', even though it takes significantly longer.
											This is because of the limitations on size and content of the image sitemaps that Google has set as standard.
											You do have the option however, to regen one, which has limited use-case viability.
										</div>

										<p><a class="action-link" href="#sm-regen-all" rel="regen-all">REGEN ALL</a></p>
										
										<ul class="sitemap-list">
											<?php foreach ($page_list as $base_filename => $ids): ?>
												<?php
													$page = self::_number_from_filename($base_filename, true);
													$url = self::_filename_to_url($page, false, true);
													$name = basename($url);
												?>
												<li id="sm-page-<?php echo $page ?>">
													<span class="sm-name"><?php echo $name ?></span>
													<span class="sm-actions">
														<a class="action-link" href="#sm-page-<?php echo $page ?>" page="<?php echo $page ?>" rel="regen-one">regen</a>
														<a class="action-link" href="<?php echo $url ?>" page="<?php echo $page ?>" rel="view">view</a>
													</span>
												</li>
											<?php endforeach; ?>
										</ul>

										<p><a class="action-link" href="<?php echo esc_attr(self::_filename_to_url(0, false, true)) ?>" rel="sm-index" target="_blank">View Sitemap Index</a></p>
									<?php else: ?>
										<p>There are currently no cached sitemaps. Click generate to create them.</p>
									<?php endif; ?>
								</div>

								<div class="field hidden" rel="genout">
									<label>Generation Progress</label>
									<div class="progress-ui" rel="progress">
										<div class="progress-text" rel="text"></div>
										<div class="progress-bar" rel="bar"></div>
									</div>
									<pre class="gen-log" rel="genlog"></pre>
								</div>
							</div>
						</div>

						<div class="form-actions" rel="form-actions">
							<?php wp_nonce_field('qsism/settings/save', 'qsism-submit') ?>
							<input type="submit" class="button-primary" value="Save Settings" />

							<input type="button" class="button" value="Generate Now" rel="genbtn" />

							<span class="side-wrap-cb">
								<input type="checkbox" class="from-scratch" rel="scratch" value="1" /> Generate from scratch?
							</span>
						</div>

					</form>
				</div>
			</div>
		<?php
	}

	protected static function _load_cache_path() {
		$u = wp_upload_dir();
		$uniq = substr(md5(site_url()), 4, 10);
		self::$cache_path = $full_path = trailingslashit($u['basedir']).'qsism-'.$uniq.'/';

		if (!file_exists($full_path) || !is_dir($full_path))
			@mkdir($full_path, 0777, true);

		self::$cache_writable = file_exists($full_path) && is_dir($full_path) && is_dir($full_path);
	}

	protected static function _load_settings($base='') {
		$settings = wp_parse_args($base, self::$defaults);
		$settings = wp_parse_args(get_option('_qsism_settings', $settings), $settings);
		return self::$settings = $settings;
	}

	protected static function _save_settings($settings='') {
		$settings = wp_parse_args($settings, self::$defaults);
		update_option('_qsism_settings', $settings);
		return self::$settings = $settings;
	}

  public static function setup_db_tables($tables) {
    global $wpdb;
    $tables[$wpdb->qsism_xml] = array(
      'version' => '0.1.1',
      'fields' => array(
				'post_id' => array('type' => 'bigint(20) unsigned'),
				'xml_cache' => array('type' => 'text'),
				'xml_extra_cache' => array('type' => 'text'),
				'extra_count' => array('type' => 'int(10)', 'default' => 0),
				'added_when' => array('type' => 'timestamp', 'default' => 'CURRENT_TIMESTAMP'),
      ),   
      'keys' => array(
        'PRIMARY KEY  (post_id)',
      )    
    );   

    return $tables;
	}
}

if (defined('ABSPATH') && function_exists('add_action')) QS_image_sitemap::pre_init();
