<?php
namespace QS\APIs {
(__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access
require_once 'google.php';
use QS\helpers as h, QS\APIs\google as g, QS\APIs\GoogleException as ge;

class gplus extends g {
	protected $url_formats = array(
		'g+sharebox' => 'https://plus.google.com/::context::_/sharebox/post/?spam=::spam::&soc-app=5&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.07_p3&avw=sq%3A1&f.sid=::16digits::&_reqid=::rint::&rt=j',
		'g+delete' => 'https://plus.google.com/::context::_/stream/deleteactivity/?soc-app=5&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.07_p3&avw=sq%3A1&f.sid=::16digits::&_reqid=::rint::&rt=j',
		'g+final_img' => 'https://plus.google.com/photos/::username::/albums/::album_id::/::photo_id::',
		'g+link_info' => 'https://plus.google.com/_/sharebox/linkpreview/?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.09_p3&avw=pr%3Apr&f.sid=::16digits::&_reqid=::rint::&rt=j',
		//'g+page_list_url' => 'https://plus.google.com/_/dashboard/home?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.09_p3&avw=pr%3Apr&f.sid=::16digits::&_reqid=::rint::&rt=j',
		'g+community_list' => 
				'https://plus.google.com/::context::_/communities/getcommunities?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.07_p3&avw=pr%3Apr&f.sid=::16digits::&_reqid=::rint::&rt=j',
		'g+community_details' => 'https://plus.google.com/::context::_/communities/landing?soc-app=5&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.09_p3&avw=sq%3A3&f.sid=::16digits::&_reqid=::rint::&rt=j',
		'g+pages_list' => 'https://plus.google.com/_/pages/getidentities/?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.07_p3&avw=pr%3Apr&f.sid=::16digits::&_reqid=::rint::&rt=j',
		'g+permalink' => 'https://plus.google.com/::context::::url_segment::',
	);

	protected $_e = array(
		'10001' => 'Could not connect.',

		'10051' => 'Failed to login to Google Plus.',
		'10055' => 'There is a new Privacy Policy for google. You must login and accept it, before you can verify this account.',
		'10060' => 'The captcha is turned on for your account.',
		'10065' => 'Invalid username or password.',
		'10070' => 'We cannot autopost to your google account while you have 2-step account verification active on your Google account.',
		'10075' => 'Problem in step one of determining your profile id.',
		'10076' => 'Problem in step TWO of determining your profile id.',
		'10080' => 'Could not find the "static code". Maybe a redirect problem has occured. Contact support.',

		'10201' => 'When attempting to post your update to Google Plus, we encountered a problem.',
		'10202' => 'Could not determine the profile page to post to.',
		'10205' => 'We could not determine the new update\'s unique Google id.',

		'10300' => 'Failed to compile the pre-uplaod request for uploading the image.',
		'10301' => 'Received an invalid response from Google, when asking the actual upload url.',
		'10315' => 'The response has changed when asking Google for a file upload url.',
		'10331' => 'Could not find the image file supplied.',
		'10335' => 'The image supplied is not of a type that we can upload.',
		'10351' => 'Failed to upload the supplied image.',
		'10365' => 'The image upload was unsuccessful.',

		'10401' => 'We could not fetch the information that Google has on the permalink you supplied.',
		'10405' => 'Permalink information could not be fetched by google.',

		'10515' => 'You do not have access to post to your own profile. Something maybe wrong, so contact support please.',
		'10520' => 'You do not have permission to manage the page you requested to post to. Please contact the page admin to get access.',
		'10525' => 'You do not have permission to post to the community you requested. Please either join the community, or contact the community admin and request access.',

		'10601' => 'Could not fetch the list of the communities to which you have access to post on.',
		'10605' => 'The structure of the available communities list has changed. Contact support.',
		'10610' => 'This page does not manage any communities.',
		'10615' => 'The list structure of the community list has changed and we could not interpret it.',
		'10651' => 'The page you supplied is not a page we have information on.',

		'10701' => 'No details about the community could be fetched.',
		'10705' => 'The structure of the community category list has changed. Contact support.',
		'10710' => 'This community does not have any categories.',
		'10715' => 'The list structure of the community category list has changed and we could not interpret it.',
		'10751' => 'The communitiy you supplied is not one that we have information on.',

		'10801' => 'Could not fetch the your complete profile. Please contact support',
		'10805' => 'The structure of the complete profile response has changed.',
		'10810' => 'The list we received of pages you have access to, shows that you have access to none.',
		'10815' => 'We could not determine which pages you have access to, including your profile.',

		'10950' => 'Could not determine the page id based on the url.',
		'10999' => 'We encountered an unanticipated problem. Please contact support.',
	);

	protected static $file_type_map = array(
		'image/gif' => '.gif',
		'image/jpeg' => '.jpg',
		'image/png' => '.png',
	);

	public $bad_page_records = array();
	public $last_page_fetch = 0;
	public $pages = array();
	public $communities = array();
	public $streams = array();

	protected $community_to_page = array();
	protected $stream_to_community = array();
	protected $_recache_after = 172800;

	protected $settings = array(
		'login_url' => 'https://accounts.google.com/ServiceLogin?service=oz&continue=https://plus.google.com/?gpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3Dc7578f19&hl=en-US',
	);

	protected $origin = 'https://plus.google.com';

	protected $login_page_fields = array();
	protected $profile_id = '';
	protected $stream_id = '';
	protected $static_code = '';

	protected $_instance_keys = array('profile_id', 'stream_id', 'static_code', 'last_page_fetch', 'pages', 'communities', 'streams', 'community_to_page', 'stream_to_community');

	public function connect() {
		if ($this->connected) return true;

		$ssl = $this->_ssl_check($this->ssl_check_url);
		$login_url = $this->_url($this->settings['login_url'], $ssl, !$ssl);

		$resp = $this->_curl($login_url);
		$this->connected = $resp['http_code'] == 200;

		if ($this->connected) $this->_parse_login_page_fields($resp['response_body']);
		else throw new ge($this->_e['10001'], 10001, array('resp' => $resp));

		return $this->connected;
	}

	public function login($force=false) {
		if (!$force && $this->logged_in && $this->_login_cookies_exist()) return true;
		$this->connect();

		$fields = array_merge(
			$this->login_page_fields,
			array(
				'Email' => $this->settings['username'],
				'Passwd' => $this->settings['password'],
				'signIn' => 'Sign%20in',
			)
		);

		$this->referer = $this->settings['login_url'];
		list($_, $resp) = $this->_fajax($this->_url($this->settings['login_endpoint_url']), $fields, 10051, '', '', false);

		// DEBUG file_put_contents('.login', $resp['response_body']); // DEBUG

		if (strpos($resp['url'], 'NewPrivacyPolicy') !== false)
			throw new ge($this->_e['10055'], 10055, array('resp' => $resp));
		if (strpos($resp['response_body'], 'captcha-box') !== false)
			throw new ge($this->_e['10060'], 10060, array('resp' => $resp));
		if (strpos($resp['url'], 'ServiceLoginAuth') !== false)
			throw new ge($this->_e['10065'], 10065, array('resp' => $resp));
		if (strpos($resp['url'], 'SmsAuth') !== false)
			throw new ge($this->_e['10070'], 10070, array('resp' => $resp));

		$profile_id = $this->_grab_profile_id($resp['response_body']);
		$static_code = $this->_grab_static_code($resp['response_body']);

		$this->profile_id = $profile_id;
		$this->static_code = $static_code;

		return $this->logged_in = true;
	}

	public function reset() {
		// first clear all data we have
		foreach ($this->_instance_keys as $k)
			$this->{$k} = isset($this->{$k}) && is_array($this->{$k}) ? array() : '';
		$this->connected = $this->logged_in = false;
		$this->referer = '';
		$this->cookies = array();
	}

	protected function _where() {
		$args = array(
			'page_id' => $this->profile_id ? $this->profile_id : '',
			'community_id' => '',
			'stream_id' => '',
		);
		$where = $this->settings['post_to_page'];
		$endpoints = $this->getEndpoints();

		if (!empty($where) && is_string($where) && isset($endpoints[$where])) 
			$args = array_merge($args, $endpoints[$where]['args']);

		return $args;
	}

	public function verify() {
		$where = $this->_where();
		$this->reset();
		$this->login(true);

		$this->getAllAccess(true);

		if (!isset($this->pages[$this->profile_id]))
			throw new ge($this->_e['10515'], 10515, array('settings' => $this->settings, 'profile_id' => $this->profile_id, 'pages' => $this->pages));

		if ($page_id && !isset($this->pages[$where['page_id']]))
			throw new ge($this->_e['10520'], 10520, array('settings' => $this->settings, 'where' => $where, 'pages' => $this->pages));

		if ($stream_id && !isset($this->streams[$where['stream_id']]))
			throw new ge($this->_e['10525'], 10525, array('settings' => $this->settings, 'where' => $where, 'streams' => $this->streams));

		return true;
	}

	public function post($message, $fields='', $args='') {
		$this->login();
		if (is_string($fields)) parse_str($fields, $fields);
		if (is_string($args)) parse_str($args, $args);

		$fields = array_merge(array( 'url' => '', '_img_path' => '' ), (array)$fields);

		$args = array_merge(array( 'profile_id' => $this->profile_id ), $args);
		if (empty($args['profile_id']))
			throw new ge($this->_e['10202'], 10202, array('args' => $args, 'fields' => $fields, 'msg' => $message));

		$spam = rand(4, 52);
		$reqid = rand(12873657, 92864646);
		$rpl = array_merge(array(
			'big_code' => '',
			'static_code' => $this->static_code,
			'long_code' => '',
			'profile_or_community_txt' => '%5D%2C%5B%5B%5Bnull%2Cnull%2C1%5D%5D%2Cnull',
			'rstr' => h\_rs(11),
			'page_id' => '',
			'community_id' => '',
			'stream_id' => '',
			'spam' => $spam,
			'reqid' => $reqid,
			'profile_id' => $args['profile_id'],
			'message' => '',
			'image_info' => null,
		), $this->_where(), $args);

		// post to profile
		if (empty($rpl['page_id']) && !empty($args['profile_id'])) {
			$rpl['page_id'] = $rpl['profile_id'];
			$ref_url = 'https://plus.google.com/_/scs/apps-static/_/js/k=oz.home.en.JYkOx2--Oes.O' ;
			unset($this->cookies['GAPS'], $this->cookies['GALX'], $this->cookies['RMME'], $this->cookies['LSID']);
		}

		$this->referer = $ref_url;

		$errors = array();
		if (!empty($fields['_img_path'])) {
			try {
				$img_resp = $this->_uplaod_image($fields['_img_path'], $rpl['page_id']);
				$info = $img_resp->sessionStatus->additionalInfo->{'uploader_service.GoogleRupioAdditionalInfo'}->completionInfo->customerSpecificInfo;
				$rpl = array_merge($rpl, array(
					'album_id' => $info->albumid,
					'photo_id' => $info->photoid,
					'img_url' => str_ireplace(array('https:', 'http:', '//lh4.'), array('', '', '//lh3.'), $info->url),
					'title' => $info->title,
					'width' => $info->width,
					'height' => $info->height,
					'username' => $info->username,
				));
				$rpl['final_img_url'] = h\_f($rpl, $this->url_formats['g+final_img']);

				$rpl['image_info'] = array(
					array(344,339,338,336,335), // static values
					null, null, null,
					array(array('39387941' => array(true, false))), // static values
					null, null,
					array(
						'40655821' => array( // static value, prolly revision number. most likely to change &&&&&&&
							$rpl['final_img_url'], // calculated final resting place url of the image
							$rpl['img_url'], // received google internal content url
							$rpl['title'], // received image base name
							'', null, null, null, array(), null, null, array(), null, null, null, null, null, null, null,
							$rpl['width'].'', // image width, string format
							$rpl['height'].'', // image height, string format
							null, null, null, null, null, null,
							$rpl['username'], // page id of the page the image landed on
							null, null, null, null, null, null, null, null, null, null,
							$rpl['album_id'], // the google assigned album id for the album the image lives in
							$rpl['photo_id'], // google assigned image id, assigned after upload
							http_build_query(array('albumid' => $rpl['album_id'], 'photoid' => $rpl['photo_id'])), // param style album and image ids
							1, // not sure, but required
							array(), null, null, null, null, array(), null,  null, null, null, null, null, null, null, null, null, null, null, null, null, null, null,
							null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, array()
						),
					),
				);
			} catch(ge $e) {
				// report nothing, for now. store the error. just dont attach the image
				$errors[] = $e;
			}
		} else if (!empty($fields['url'])) {
			try {
				$link_info = $this->_link_info($fields['url']);
				$rpl['image_info'] = $link_info;
			} catch(ge $e) {
				// report nothing, for now. store the error, and dont attach the url
			}
		}

		$rpl['message'] = $this->_sane_message($message);
		$rpl['_not_stream'] = !$rpl['stream_id'];
		$rpl['_nof'] = $rpl['_not_stream'] ? false : null;

		$arr = array(
			$message,
			'oz:'.$rpl['page_id'].'.'.$rpl['rstr'].'.0',
			null, null, null, null,
			'[]',
			null, null, $rpl['_not_stream'], array(),
			$rpl['_nof'], null, null, array(), null, false,
			null, null, null, null, null, null, null, null, null, null, $rpl['_nof'], $rpl['_nof'], false,
			null, null, null, null,
			$rpl['image_info'],
			null,
			!$rpl['stream_id']
				? array() // to profile page or normal page
				: array( array($rpl['community_id'], $rpl['stream_id']) ), // to community page
			empty($rpl['community_id'])
				? array( array(array(null, null, 1)), null ) // for profile -or-
				: array( array(array(null, null, null, array($rpl['community_id']))) ), // for community pages
			null, null, 2, null, null, null,
			'!'.$rpl['long_code'],
			null, null, null, array(), array(array(true)), null, array()
		);

		// MAKE A getPage($page_id) FUNCTION
		$page = $this->pages[$rpl['page_id']];

		$rpl['context'] = $page['type'] == 1
			? ''
			: (!$rpl['stream_id'] ? '' : 'u/0/').'b/'.$page['id'].'/';

		$target_url = $this->_url(h\_f($rpl, $this->_pf('g+sharebox')));
		$params = 'f.req='.rawurlencode(@json_encode($arr)).'&at='.$rpl['static_code'].'&';

		list($obj, $raw) = $this->_fajax($target_url, $params, 10201);

		if (!$obj || !is_array($obj)
				|| !isset(
					$obj[0],
					$obj[0][1],
					$obj[0][1][1],
					$obj[0][1][1][0],
					$obj[0][1][1][0][0],
					$obj[0][1][1][0][0][8],
					$obj[0][1][1][0][0][21]
				)
				|| empty($obj[0][1][1][0][0][8])
				|| empty($obj[0][1][1][0][0][21])
			)
			throw new Exception($this->_e['10205'], 10205, array('obj' => $obj, 'resp' => $raw));

		// unique_id
		return $obj[0][1][1][0][0][8].':'.$page['id'].':'.$obj[0][1][1][0][0][21];
	}

	public function urlFromId($unique_id) {
		$out = '';
		@list($post_id, $page_id, $url_segment) = explode(':', $unique_id);
		$page = isset($this->pages[$page_id]) ? $this->pages[$page_id] : null;

		if ($page) {
			$out = $this->_url(h\_f(array(
				'context' => $page['type'] == 1 ? '' : 'b/'.$page['id'].'/',
				'url_segment' => $url_segment,
			), $this->_pf('g+permalink')));
		}

		return $out;
	}

	public function delete($unique_id) {
		$this->login();

		@list($post_id, $page_id, $url) = explode(':', $unique_id);
		$page = isset($this->pages[$page_id]) ? $this->pages[$page_id] : null;
		if (empty($page))
			throw new ge($this->_e['10299'], 10299, array('unique_id' => $unique_id, 'page_id' => $page_id, 'pages' => $this->pages));

		$url = $this->_url(h\_f(array(
			'context' => $page['type'] == 1 ? '' : 'b/'.$page['id'].'/',
		), $this->_pf('g+delete')));
		$params = 'itemId='.rawurlencode($post_id).'&at='.$this->static_code.'&';

		list($obj, $raw) = $this->_fajax($url, $params, 10601);

		return $obj;
	}

	public function getEndpoints($force=false) {
		$this->getAllAccess($force);

		$endpoints = array();

		foreach ($this->pages as $page_id => $page)
			$endpoints[$page_id] = array(
				'name' => $page['name'],
				'type' => $page['type'] == '1' ? 'profile' : 'page',
				'args' => array(
					'page_id' => $page_id,
				),
			);

		foreach ($this->streams as $stream_id => $stream) {
			// assume these are set for EVERY stream, since they are required for a stream to exist
			$comm = $this->communities[$stream['owner_id']];
			$page = $this->pages[$comm['owner_id']];
			$endpoints[$page['id'].':'.$comm['id'].':'.$stream_id] = array(
				'name' => $comm['name'].' - '.$stream['name'],
				'type' => 'stream',
				'args' => array(
					'page_id' => $page['id'],
					'community_id' => $comm['id'],
					'stream_id' => $stream_id,
				),
			);
		}

		return $endpoints;
	}

	public function getAllAccess($force=false) {
		if (!$force && !empty($this->pages)) return $this->pages;
		$this->login();

		$pages = $this->getPages($force);
		foreach ($pages as $page_id => $page) {
			try {
				$comms = $this->getCommunities($page, $force);
				foreach ($comms as $comm_id => $comm) {
					try {
						$this->getStreams($comm, $force);
					} catch(ge $e) {
						if ($e->getCode() % 100 != 10) // if the issue is anything but "no categories", then
							die(__log($e));
					}
				}
			} catch(ge $e) {
				if ($e->getCode() % 100 != 10) // if the issue is anything but "no communities", then
					die(__log($e));
			}
		}

		return $this->pages;
	}

	public function getPages($force=false) {
		if (!$force && !empty($this->pages) && time() - $this->_recache_after < $this->last_page_fetch) return $this->pages;
		$this->login();
		// https://plus.google.com/_/pages/getidentities/?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_20140130.07_p3&avw=pr%3Apr&f.sid=7752046231967176312&_reqid=603298&rt=j
		// g+pages_list

		$url = $this->_pf('g+pages_list');
		$arr = array(false);

		list($obj, $raw) = $this->_fajax($url, '', 10801);

		if (!$obj || !is_array($obj)
				|| !isset(
					$obj[0],
					$obj[0][1],
					$obj[0][1][1]
				)
			)
			throw new ge($this->_e['10805'], 10805, array('obj' => $obj, 'resp' => $raw));

		if (empty($obj[0][1][1]))
			throw new ge($this->_e['10810'], 10810, array('obj' => $obj));

		$list = $bad = array();
		foreach ($obj[0][1][1] as $page) {
			if (!isset($page[2], $page[30], $page[4], $page[4][1])) {
				$bad[] = $page;
				continue;
			}
			$list[$page[30].''] = array(
				'id' => $page[30],
				'name' => $page[4][1], 
				'url' => $page[2],
				'type' => isset($page[46], $page[46][3]) ? '1' : '2',
				'last_fetch' => time(),
			);
		}

		if (empty($list))
			throw new ge($this->_e['10815'], 10815, array('bad' => $bad, 'obj' => $obj));

		foreach ($list as $page_id => $page)
			$this->pages[$page_id] = !isset($this->pages[$page_id])
				? array_merge(array('last_comm_fetch' => 0), $page)
				: array_merge($this->pages[$page_id], $page);

		return $this->pages;
	}

	public function getKnownPageCommunities($page_id) {
		$list = $comm_to_page = array();
		$comm_to_page = array_filter($this->community_to_page, function($v) use($page_id) { return $v == $page_id; });

		foreach ($comm_to_page as $comm_id => $pid)
			if (isset($this->communities[$comm_id]))
				$list[$comm_id] = $this->communities[$comm_id];

		return $list;
	}

	public function getCommunities($page, $force=false) {
		$opage = $page;
		$page = !is_string($page) ? $this->pages[$page['id']] : ( isset($this->pages[$page]) ? $this->pages[$page] : null );

		if (!is_array($page) || !isset($page['id'], $page['type']))
			throw new ge($this->_e['10651'], 10651, array('requested' => $opage, 'pages' => $this->pages));

		$current = $this->getKnownPageCommunities($page['id']);
		if (!$force && !empty($current) && time() - $this->_recache_after < $page['last_comm_fetch']) return $current;
		$this->login();

		// URL https://plus.google.com/_/communities/getcommunities?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_20140130.07_p3&avw=pr%3Apr&f.sid=7752046231967176312&_reqid=103298&rt=j

		$url = $this->_url(h\_f(array(
			'context' => $page['type'] == 1 ? '' : 'b/'.$page['id'].'/',
		), $this->_pf('g+community_list')));
		$arr = array(array(1));
		$params = 'f.req='.rawurlencode(@json_encode($arr)).'&at='.$this->static_code.'&';

		list($obj, $raw) = $this->_fajax($url, $params, 10601);

		if (!$obj || !is_array($obj)
				|| !isset(
					$obj[0],
					$obj[0][1],
					$obj[0][1][2]
				)
			)
			throw new ge($this->_e['10605'], 10605, array('obj' => $obj, 'resp' => $resp));

		if (empty($obj[0][1][2]))
			throw new ge($this->_e['10610'], 10610, array('obj' => $obj));

		$list = $bad = array();
		foreach ($obj[0][1][2] as $entry) {
			if (!isset($entry[0], $entry[0][0], $entry[0][0][0])) {
				$bad[] = $entry;
				continue;
			}
			$comm = $entry[0][0];
			$list[$comm[0].''] = array(
				'id' => $comm[0],
				'name' => isset($comm[1], $comm[1][0]) ? $comm[1][0] : $comm[0], 
				'owner_id' => $page['id'],
				'last_fetch' => time(),
			);
		}

		if (empty($list))
			throw new ge($this->_e['10615'], 10615, array('bad' => $bad, 'obj' => $obj));

		$page_comms = array();
		foreach ($list as $comm_id => $comm)
			$page_comms[] = $this->communities[$comm_id] = !isset($this->communities[$comm_id])
				? array_merge(array('last_stream_fetch' => 0), $comm)
				: array_merge($this->communities[$comm_id], $comm);

		$this->_update_community_map($page_comms, $page['id']);
		$this->pages[$page['id']]['last_comm_fetch'] = time();

		return $page_comms;
	}

	public function getKnownCommunityStreams($comm_id) {
		$list = $stream_to_comm = array();
		$stream_to_comm = array_filter($this->stream_to_community, function($v) use($comm_id) { return $v == $comm_id; });

		foreach ($stream_to_comm as $stream_id => $cid)
			if (isset($this->streams[$stream_id]))
				$list[$stream_id] = $this->streams[$stream_id];

		return $list;
	}

	public function getStreams($comm, $force=false) {
		$ocomm = $comm;
		$comm = !is_string($comm) ? $this->communities[$comm['id']] : ( isset($this->communities[$comm]) ? $this->communities[$comm] : null );

		if (!is_array($comm) || !isset($comm['id']))
			throw new ge($this->_e['10751'], 10751, array('requested' => $ocomm, 'pages' => $this->pages));

		$current = $this->getKnownCommunityStreams($comm['id']);
		if (!$force && !empty($current) && time() - $this->_recache_after < $comm['last_stream_fetch']) return $current;
		$this->login();
		// https://plus.google.com/b/116401124225489320013/_/communities/landing?soc-app=5&cid=0&soc-platform=1&hl=en&ozv=es_oz_20140129.09_p3&avw=sq%3A3&f.sid=6747696279920566422&_reqid=1076160&rt=j
		// ["112567801843023057233",null,false,[360,2,[]],2,false]

		$url = $this->_url(h\_f(array(
			'context' => $this->pages[$comm['owner_id']]['type'] == 1 ? '' : 'b/'.$comm['owner_id'].'/',
		), $this->_pf('g+community_details')));

		$arr = array(
			$comm['id'], null, false,
			array(
				1, // used to be '360,'. seems to remove key #6, which seems to be recursive style reference back to owner... plus some unneeded crap
				2, array()
			),
			2, false
		);
		$params = 'f.req='.rawurlencode(@json_encode($arr)).'&at='.$this->static_code.'&';

		list($obj, $raw) = $this->_fajax($url, $params, 10701);

		if (!$obj || !is_array($obj)
				|| !isset(
					$obj[0],
					$obj[0][1],
					$obj[0][1][1],
					$obj[0][1][1][2],
					$obj[0][1][1][2][0]
				)
			)
			throw new ge($this->_e['10705'], 10705, array('obj' => $obj, 'resp' => $resp));

		// this should never happen since 'discussion' is an automatically created category... but maybe it will... who knows
		if (empty($obj[0][1][1][2][0]))
			throw new ge($this->_e['10710'], 10710, array('obj' => $obj));

		$list = $bad = array();
		foreach ($obj[0][1][1][2][0] as $cat) {
			if (!isset($cat[0], $cat[1])) {
				$bad[] = $cat;
				continue;
			}
			$list[$cat[0].''] = array(
				'id' => $cat[0],
				'name' => isset($cat[1]) ? $cat[1] : $cat[0], 
				'owner_id' => $comm['id'],
			);
		}

		if (empty($list))
			throw new ge($this->_e['10715'], 10715, array('bad' => $bad, 'obj' => $obj));

		$comm_streams = array();
		foreach ($list as $stream_id => $stream)
			$comm_streams[] = $this->streams[$stream_id] = !isset($this->stream[$stream_id])
				? $stream
				: array_merge($this->streams[$stream_id], $stream);

		$this->communities[$comm['id']]['last_stream_fetch'] = time();

		return $comm_streams;
	}

	protected function _update_community_map($comms, $page_id) {
		$this->community_to_page = array_filter($this->community_to_page, function($v) use($page_id) { return !($v == $page_id); });

		foreach ($comms as $comm_id => $comm)
			$this->community_to_page[$comm_id] = $page_id;
	}

	protected function _update_stream_map($streams, $comm_id) {
		$this->stream_to_community = array_filter($this->stream_to_community, function($v) use($comm_id) { return !($v == $comm_id); });

		foreach ($streams as $stream_id => $stream)
			$this->stream_to_community[$stream_id] = $comm_id;
	}

	protected function _link_info($url) {
		// $r[0][1][5][0][7]->{'39748951'}
		// https://plus.google.com/_/sharebox/linkpreview/?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.09_p3&avw=pr%3Apr&f.sid=::16digits::&_reqid=::rint::&rt=j
		// f.req = ["http://quadshot.com/",false,false,null,null,null,null,null,null,null,null,null,true]

		$target_url = $this->_url($this->_pf('g+link_info'));

		$arr = array(
			$url,
			false, false,
			null, null, null, null, null, null, null, null, null,
			true
		);
		$params = 'f.req='.rawurlencode(@json_encode($arr)).'&at='.$this->static_code.'&';
		list($obj, $raw) = $this->_fajax($target_url, $params, 10401);

		if (!$obj || !is_array($obj)
				|| !isset(
					$obj[0],
					$obj[0][1],
					$obj[0][1][5],
					$obj[0][1][5][0],
					$obj[0][1][5][0][7]
				)
				|| empty($obj[0][1][5][0][7])
			)
			throw new ge($this->_e['10405'], 10405, array('url' => $url, 'obj' => $obj, 'resp' => $resp));

		$domains = array_shift($obj[0][1][5]);

		return $domains;
	}

	protected function _uplaod_image($image_path, $to_page_id) {
		list($url_filename, $file_size, $image, $img_info) = $this->_image_data($image_path);

		list($actual_upload_url, $upload_id) = $this->_get_actual_upload_endpoint($url_filename, $file_size, $to_page_id);

		$headers = array(
			'Origin' => $this->origin,
			'X-GUploader-No-308' => 'yes',
			'X-HTTP-Method-Override' => 'PUT',
			'Content-Type' => 'application/octet-stream',
			'Except' => '',
		);

		$resp = $this->_curl($actual_upload_url, $image, 'POST', false, $headers);

		if ($resp['http_code'] !== 200)
			throw new ge($this->_e['10351'], 10351, array('resp' => $resp));

		$obj = @json_decode($resp['response_body']);
		if (!$obj || !is_object($obj) || (isset($obj->errorMessage) && !empty($obj->errorMessage))
				|| !isset(
					$obj->sessionStatus,
					$obj->sessionStatus->state,
					$obj->sessionStatus->externalFieldTransfers,
					$obj->sessionStatus->upload_id,
					$obj->sessionStatus->additionalInfo,
					$obj->sessionStatus->externalFieldTransfers[0],
					$obj->sessionStatus->externalFieldTransfers[0]->status,
					$obj->sessionStatus->additionalInfo->{'uploader_service.GoogleRupioAdditionalInfo'},
					$obj->sessionStatus->additionalInfo->{'uploader_service.GoogleRupioAdditionalInfo'}->completionInfo,
					$obj->sessionStatus->additionalInfo->{'uploader_service.GoogleRupioAdditionalInfo'}->completionInfo->status
				)
				|| $obj->sessionStatus->state != 'FINALIZED'
				|| $obj->sessionStatus->externalFieldTransfers[0]->status != 'COMPLETED'
				|| $obj->sessionStatus->additionalInfo->{'uploader_service.GoogleRupioAdditionalInfo'}->completionInfo->status != 'SUCCESS'
			)
			throw new ge($this->_e['10365'], 10365, array('resp' => $resp, 'obj' => $obj ));
		
		return $obj;
	}

	protected function _get_actual_upload_endpoint($url_filename, $file_size, $to_page_id) {
		$raw_params = array(
			'protocolVersion' => '0.8',
			'createSessionRequest' => array(
				'fields' => array(
					array('external' => array( 'name' => 'file', 'filename' => $url_filename, 'put' => (object)array(), 'size' => $file_size )),
					array('inlined' => array( 'name' => 'use_upload_size_pref', 'content' => 'true', 'contentType' => 'text/plain' )),
					array('inlined' => array( 'name' => 'batchid', 'content' => '1389803229361', 'contentType' => 'text/plain' )),
					array('inlined' => array( 'name' => 'client', 'content' => 'sharebox', 'contentType' => 'text/plain' )),
					array('inlined' => array( 'name' => 'disable_asbe_notification', 'content' => 'true', 'contentType' => 'text/plain' )),
					array('inlined' => array( 'name' => 'album_mode', 'content' => 'temporary', 'contentType' => 'text/plain' )),
					array('inlined' => array( 'name' => 'effective_id', 'content' => $to_page_id, 'contentType' => 'text/plain' )),
					array('inlined' => array( 'name' => 'owner_name', 'content' => $to_page_id, 'contentType' => 'text/plain' )),
					array('inlined' => array( 'name' => 'album_abs_position', 'content' => '0', 'contentType' => 'text/plain' )),
				)
			),
		);
		$params = @json_encode($raw_params);

		if (!$params) throw new ge($this->_e['10300'], 10300, array('raw' => $raw_params, 'into' => $params));

		$headers = array(
			'X-GUploader-Client-Info' => 'mechanism=scotty xhr resumable; clientVersion=58505203'
		);

		$pre_upload_url = $this->_url($this->settings['pre_file_upload_url']);
		$resp = $this->_curl($pre_upload_url, $params, 'POST', false, $headers);

		if (!$resp['http_code'] == 200)
			throw new ge($this->_e['10301'], 10301, array(
				'resp' => array( 'raw' => $resp, 'obj' => $obj ),
				'req' => array(
					'url' => $pre_uplaod_url,
					'params' => $raw_params,
					'headers' => $headers,
				)
			));
		
		$obj = @json_decode($resp['response_body']);
		if (!$obj || !is_object($obj) || !isset(
				$obj->sessionStatus,
				$obj->sessionStatus->externalFieldTransfers,
				$obj->sessionStatus->upload_id,
				$obj->sessionStatus->externalFieldTransfers[0],
				$obj->sessionStatus->externalFieldTransfers[0]->putInfo,
				$obj->sessionStatus->externalFieldTransfers[0]->putInfo->url
			))
			throw new ge($this->_e['10315'], 10315, array('resp' => $resp, 'obj' => $obj ));

		return array($obj->sessionStatus->externalFieldTransfers[0]->putInfo->url, $obj->sessionStatus->upload_id);
	}

	protected function _image_data($file) {
		if (!file_exists($file) || !is_readable($file))
			throw new ge($this->_e['10331'], 10331, array('filename' => $file));

		$img_info = @getimagesize($file);
		if (!$img_info || !isset(self::$file_type_map[$img_info['mime']]))
			throw new GoogleExcpetion($this->_e['10335'], 10335, array('filename' => $file, 'info' => $img_info));

		$image = file_get_contents($file);
		$basename = basename($file);
		$size = filesize($file);
		$base = str_replace(array('.gif', '.jpg', '.png', '.jpeg'), '', $basename);
		$final_name = $base.self::$file_type_map[$img_info['mime']];

		return array($final_name, $size, $image, $img_info);
	}

	protected function _fajax($url, $params='', $code=10999, $extra_headers='', $extra_opts='', $expect_json=true) {
		if (is_string($extra_headers)) parse_str($extra_headers, $extra_headers);
		$extra_opts = is_array($extra_opts) ? $extra_opts : array();

		$headers = array(
			'Origin' => $this->origin,
			'X-Same-Domain' => 1,
		);
		$this->referer = !$this->referer ? $this->origin : $this->referer;

		$resp = $this->_curl($url, $params, !$params ? 'GET' : 'POST', false, array_merge($headers, $extra_headers), $extra_opts);

		if ($resp['http_code'] != 200)
			throw new ge($this->_e[$code.''], $code, array('resp' => $resp));

		return array( ( $expect_json ? h\jd($resp['response_body']) : $resp['response_body'] ), $resp);
	}

	protected function _has_cookies($cookies) {
		$good = true;
		foreach ($cookies as $k) {
			if (!isset($this->cookies[$k])) {
				$good = false;
				continue;
			}

			if (isset($this->cookies[$k]['expires']) && $this->_is_expired($this->cookies[$k]['expires'])) {
				$good = false;
				continue;
			}
		}

		return $good;
	}

	protected function _login_cookies_exist() {
		return $this->_has_cookies(array('SID', 'HSID', 'SSID', 'APISID', 'SAPISID'));
	}

	protected function _id_from_url($url) {
		$id = h\pr('#^.*\/\/plus.google.com\/(?:b\/)?(\d+?)(?:\/.*$|$)#si', '$1', $url);
		if ($id == $url)
			throw new ge($this->_e['10950'], 10950, array('url' => $url));

		return $id;
	}

	protected function _grab_profile_id($content) {
		$snippet = h\pr('#^.*(key: \'2\'[^\]]+?\]).*$#s', '$1', $content);
		if ($snippet == $content)
			throw new ge($this->_e['10075'], 10075, array('snippet-result' => $snippet));
		
		$profile_id = h\pr('#^.*data:.*?\["([^"]+?)".*$#', '$1', $snippet);
		if ($profile_id == $snippet)
			throw new ge($this->_e['10076'], 10076, array('profile_id-result' => $profile_id));

		return $profile_id;
	}

	protected function _grab_static_code($content) {
		$code = h\pr('#^.*csi.gstatic.com/csi","([^"]+)".*$#si', '$1', $content);
		if ($code == $content)
			throw new ge($this->_e['10080'], 10080, array('code-result' => $code));

		return $code;
	}

	protected function _parse_login_page_fields($body) {
		$fields = array();

		preg_match_all('#(<input ([^>]+)>)#si', $body, $inputs, PREG_SET_ORDER);
		foreach ($inputs as $input) {
			preg_match_all('#([a-z][a-z0-9]*)\s*=\s*("|\')([^\2]*?)\2#si', $input[2], $raw_atts, PREG_SET_ORDER);
			$attrs = array();
			foreach ($raw_atts as $att) $attrs[strtolower($att[1])] = $att[3];
			if (isset($attrs['name'], $attrs['value'], $attrs['type']) && strtolower($attrs['type']) == 'hidden')
				$fields[$attrs['name']] = $attrs['value'];
		}

		return $this->login_page_fields = $fields;
	}
}

/*
	public function getPages() {
		$this->login();

		// MAYBE https://plus.google.com/_/pages/getidentities/?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_20140130.07_p3&avw=pr%3Apr&f.sid=7752046231967176312&_reqid=603298&rt=j

		// https://plus.google.com/_/dashboard/home?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_20140129.09_p3&avw=pr%3Apr&f.sid=7805832130721151676&_reqid=944687&rt=j
		// https://plus.google.com/_/dashboard/home?soc-app=1&cid=0&soc-platform=1&hl=en&ozv=es_oz_::8date::.09_p3&avw=pr%3Apr&f.sid=::16digit::::&_reqid=::rint::&rt=j
		$url = $this->_pf('g+page_list_url');

		$headers = array(
			'Origin' => $this->origin,
			'X-Same-Domain' => 1,
		);
		
		$params = 'at='.$this->static_code.'&';

		$resp = $this->_curl($url, $params, 'POST', false, $headers);

		if ($resp['http_code'] != 200)
			throw new ge('Could not fetch the list of the pages you have access to post on.', 10501, array('resp' => $resp));

		$obj = h\jd($resp['response_body']);
		if (!$obj || !is_array($obj)
				|| !isset(
					$obj[0],
					$obj[0][1],
					$obj[0][1][1],
					$obj[0][1][1][1],
					$obj[0][1][1][1][0]
				)
			)
			throw new ge('Did not receive a list of pages you can access when asking Google for it.', 10505, array('obj' => $obj, 'resp' => $resp));

		if (!count($obj[0][1][1][1][0]))
			throw new ge('You do not have access to edit any pages. You can only post to your profile.', 10510, array('obj' => $obj, 'resp' => $resp));
		
		$list = $bad = array();
		foreach ($obj[0][1][1][1][0] as $entry) {
			list($page, $info, $cover) = $entry;
			if (!isset($page[30], $page[2], $page[4], $page[4][1])) {
				$bad[] = $page;
				continue;
			}
			$list[$page[30].''] = array(
				'id' => $page[30],
				'name' => $page[4][1],
				'url' => $page[2],
				'followers' => isset($info[0], $info[0][0]) ? $info[0][0] : 0,
				'last_post' => isset($info[0], $info[0][1]) ? $info[0][1] : '',
			);
		}

		if (!empty($bad)) $this->bad_page_records = array_merge($this->bad_page_records, $bad);
		if (empty($list))
			throw new ge('We could not determine the list of pages you have access to post on.', 10515, array('list' => $list, 'bad' => $bad, 'resp' => $resp));

		return $this->available_pages = $list;
	}
*/
	
}
/*
[
	"Whoa! The first comment!",
	"oz:110087285508539122907.143eb617a9e.0",
	null,null,null,null,
	"[]",
	null,null,false,[],null,null,null,[],null,false,
	null,null,null,null,null,null,null,null,null,null,null,null,false,
	null,null,null,null,
	[
		[335,0],
		"http://quadshot.com/",
		null,null,null,null,
		[
			1391223531253,
			"http://quadshot.com/",
			"http://quadshot.com/",
			null,
			["og:article"],
			[],[]
		],
		{
			"39748951":[
				"http://quadshot.com/",
				"http://quadshot.com/wp-content/uploads/2013/12/opentickets-slide.png",
				"Quadshot Software - WordPress and E-commerce Web Development",
				"Quadshot Software is a web development company in Las Vegas. Responsive WordPress and e-commerce websites. Software - Made Simple.",
				null,
				[
					"//lh4.googleusercontent.com/proxy/xC3XPDRpkpBruqJJBjgdrCFJgLzpZTDWTTf46mDYxftFpsbBwWXYIKdpARo6Mvp_ebP9MsnSucme36jTneTL8Y7UcBXgQUEt4W8Pyty6m-51j5pU=w120-h120",
					120,120,null,null,null,null,null,
					[2,"https://lh4.googleusercontent.com/proxy/xC3XPDRpkpBruqJJBjgdrCFJgLzpZTDWTTf46mDYxftFpsbBwWXYIKdpARo6Mvp_ebP9MsnSucme36jTneTL8Y7UcBXgQUEt4W8Pyty6m-51j5pU=w800-h800"]
				],
				"//s2.googleusercontent.com/s2/favicons?domain=quadshot.com",
				[],null,null,
				[[[339,338,336,335,0],"http://quadshot.com/wp-content/uploads/2013/04/wordpress-logo-slide.png",null,null,null,null,null,{"40265033":["http://quadshot.com/wp-content/uploads/2013/04/wordpress-logo-slide.png","http://quadshot.com/wp-content/uploads/2013/04/wordpress-logo-slide.png",null,null,null,["//lh4.googleusercontent.com/proxy/JLg39r6XlDcc_2TyI3K7MjSTRLR6_YBgh5rW9bM3LtvHB0dHnB15hq6WbS7L35-H06BwaUJe5q7hvRMrcXm2Xh_sXwXaY-xLGk9mR9iwewPjwZGl_aTy=w120-h120",120,120,null,null,null,null,null,[2,"https://lh4.googleusercontent.com/proxy/JLg39r6XlDcc_2TyI3K7MjSTRLR6_YBgh5rW9bM3LtvHB0dHnB15hq6WbS7L35-H06BwaUJe5q7hvRMrcXm2Xh_sXwXaY-xLGk9mR9iwewPjwZGl_aTy=w800-h800"]],null,[],null,null,[],null,null,null,null,null,null,null,"960","480",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}],[[339,338,336,335,0],"http://quadshot.com/wp-content/uploads/2013/12/opentickets-slide.png",null,null,null,null,null,{"40265033":["http://quadshot.com/wp-content/uploads/2013/12/opentickets-slide.png","http://quadshot.com/wp-content/uploads/2013/12/opentickets-slide.png",null,null,null,["//lh4.googleusercontent.com/proxy/xC3XPDRpkpBruqJJBjgdrCFJgLzpZTDWTTf46mDYxftFpsbBwWXYIKdpARo6Mvp_ebP9MsnSucme36jTneTL8Y7UcBXgQUEt4W8Pyty6m-51j5pU=w120-h120",120,120,null,null,null,null,null,[2,"https://lh4.googleusercontent.com/proxy/xC3XPDRpkpBruqJJBjgdrCFJgLzpZTDWTTf46mDYxftFpsbBwWXYIKdpARo6Mvp_ebP9MsnSucme36jTneTL8Y7UcBXgQUEt4W8Pyty6m-51j5pU=w800-h800"]],null,[],null,null,[],null,null,null,null,null,null,null,"960","480",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}],[[339,338,336,335,0],"http://quadshot.com/wp-content/uploads/2013/12/35a4814-150x150.jpg",null,null,null,null,null,{"40265033":["http://quadshot.com/wp-content/uploads/2013/12/35a4814-150x150.jpg","http://quadshot.com/wp-content/uploads/2013/12/35a4814-150x150.jpg",null,null,null,["//lh3.googleusercontent.com/proxy/gjiZiJKeGC4ZdaIEecVsBHhqZkNumLTqkhHRb6G8FTjAHtJvqDInovDN7hpCEynKKx_K8gQZoE_k-YZ8NPka1hQNIsCK73rpIx34KbO2G4YZag=w120-h120",120,120,null,null,null,null,null,[2,"https://lh3.googleusercontent.com/proxy/gjiZiJKeGC4ZdaIEecVsBHhqZkNumLTqkhHRb6G8FTjAHtJvqDInovDN7hpCEynKKx_K8gQZoE_k-YZ8NPka1hQNIsCK73rpIx34KbO2G4YZag=w800-h800"]],null,[],null,null,[],null,null,null,null,null,null,null,"150","150",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}],[[339,338,336,335,0],"http://quadshot.com/wp-content/uploads/2013/12/129c758-150x150.jpg",null,null,null,null,null,{"40265033":["http://quadshot.com/wp-content/uploads/2013/12/129c758-150x150.jpg","http://quadshot.com/wp-content/uploads/2013/12/129c758-150x150.jpg",null,null,null,["//lh3.googleusercontent.com/proxy/FEI_ABZEAwNAHPXyGDifzuOizZTEt0UevDwg2GHILVWJA67mO3qlv4lyX_9LlFn0t8uvZEbTwgeapDiK5g9e1Cio3a1bK0SoS1HsbvFOrt2tng=w120-h120",120,120,null,null,null,null,null,[2,"https://lh3.googleusercontent.com/proxy/FEI_ABZEAwNAHPXyGDifzuOizZTEt0UevDwg2GHILVWJA67mO3qlv4lyX_9LlFn0t8uvZEbTwgeapDiK5g9e1Cio3a1bK0SoS1HsbvFOrt2tng=w800-h800"]],null,[],null,null,[],null,null,null,null,null,null,null,"150","150",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}],[[339,338,336,335,0],"http://quadshot.com/wp-content/uploads/2013/12/1eeb4f7-150x150.jpg",null,null,null,null,null,{"40265033":["http://quadshot.com/wp-content/uploads/2013/12/1eeb4f7-150x150.jpg","http://quadshot.com/wp-content/uploads/2013/12/1eeb4f7-150x150.jpg",null,null,null,["//lh5.googleusercontent.com/proxy/zLfEKXdoESbFUCRz9bx0vAgP516rZDOkbaL0lHHR8KXMhY2uVWFWKvL9iEXob9q3JzxW9zKVZnc9-fPqtBN8cMyNP6IP9sqL5Kmi2aJs-_b6Dw=w120-h120",120,120,null,null,null,null,null,[2,"https://lh5.googleusercontent.com/proxy/zLfEKXdoESbFUCRz9bx0vAgP516rZDOkbaL0lHHR8KXMhY2uVWFWKvL9iEXob9q3JzxW9zKVZnc9-fPqtBN8cMyNP6IP9sqL5Kmi2aJs-_b6Dw=w800-h800"]],null,[],null,null,[],null,null,null,null,null,null,null,"150","150",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}]],
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
				null,null,null,null,null,null,null,null,
				[[339],null,null,null,null,null,null,{"40265033":["http://quadshot.com/wp-content/uploads/2013/12/opentickets-slide.png","http://quadshot.com/wp-content/uploads/2013/12/opentickets-slide.png",null,null,null,null,null,[],null,null,[],null,null,null,null,null,null,null,"960","480",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}]
			]
		}
	],
	null,
	[["113472523815604102684","08b7e53d-8fff-4d8f-b418-9874b917f062"]],[[[null,null,null,["113472523815604102684"]]]],
	null,null,2,null,null,null,"!A0Jln0-ajUcymER-XuP3WQnlMAIAAAAoUgAAAAsqAPcGyFvbRG3brKRzjhSCeCtzm9Dc1U7IMXbA6Dip2dY8YY6-S-SElobDJIt0W2dqec4DuQXG9uDz992dYhRvz-zMzaIIriRvBPavZcacUS73LgAZarAm0vi39Z9PTKD_z9B_tSjtgrT7CLWIl470mJ04WmdMAxvbFug4ToxKvnBQF08ztA0PKOgsautvZS7zxlDJZl7OpAy16KWU2S-rae-CyBT7B_ry6QaRblvY1zecV1CwQ20TDDxa-U23IfHoeAkpDEIbASuKSAf20ap8nlgLSEQYy_fiW7-h4vxAPa52zz2V9L0QBRAyc6zDWXLAL3DQb_YGBnbP",null,null,null,[],[[true]],null,[]]
*/
/*
[
"the best search engine",
"oz:110087285508539122907.143e65a3d6e.4",
null,null,null,null,
"[]",
null,null,true,[],false,null,null,[],null,false,
null,null,null,null,null,null,null,null,null,null,false,false,false,
null,null,null,null,
[
	[337,336,335,0],
	"https://www.google.com/",
	null,null,null,null,
	[
		1391152900840,
		"http://google.com/",
		"https://www.google.com/",
		null,
		["http://schema.org/WebPage"],
		[],
		[]
	],
	{
		"40154698":[
			"http://google.com/",
			"https://www.google.com/images/google_favicon_128.png",
			"Google",
			null,null,
			[
				"//lh6.googleusercontent.com/proxy/yW5PKm0zpbFxD4-kF8sLow3UEdT2oLvuB15rdIbOCPfS0lQqohG_pRSLFjxuqOL_LnRhC_3TcV1ljPvcHAWt1yKUWX8=w120-h120",
				120,
				120,
				null,null,null,null,null,
				[
					2,
					"https://lh6.googleusercontent.com/proxy/yW5PKm0zpbFxD4-kF8sLow3UEdT2oLvuB15rdIbOCPfS0lQqohG_pRSLFjxuqOL_LnRhC_3TcV1ljPvcHAWt1yKUWX8=w800-h800"
				]
			],
			"//s2.googleusercontent.com/s2/favicons?domain=google.com",
			[],
			null,null,
			[[[339,338,336,335,0],"https://www.google.com/images/google_favicon_128.png",null,null,null,null,null,{"40265033":["https://www.google.com/images/google_favicon_128.png","https://www.google.com/images/google_favicon_128.png",null,null,null,["//lh6.googleusercontent.com/proxy/yW5PKm0zpbFxD4-kF8sLow3UEdT2oLvuB15rdIbOCPfS0lQqohG_pRSLFjxuqOL_LnRhC_3TcV1ljPvcHAWt1yKUWX8=w120-h120",120,120,null,null,null,null,null,[2,"https://lh6.googleusercontent.com/proxy/yW5PKm0zpbFxD4-kF8sLow3UEdT2oLvuB15rdIbOCPfS0lQqohG_pRSLFjxuqOL_LnRhC_3TcV1ljPvcHAWt1yKUWX8=w800-h800"]],null,[],null,null,[],null,null,null,null,null,null,null,"128","128",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}],[[339,338,336,335,0],"https://www.google.com/images/srpr/logo11w.png",null,null,null,null,null,{"40265033":["https://www.google.com/images/srpr/logo11w.png","https://www.google.com/images/srpr/logo11w.png",null,null,null,["//lh3.googleusercontent.com/proxy/fwsyGmoyMN0IV-URGQKzFJd5cxx15xIoMywHtpbOuHkKw0mBZXOhJMSsNcrDoo3YmawGks9lWYIat6aAqSE=w120-h120",120,120,null,null,null,null,null,[2,"https://lh3.googleusercontent.com/proxy/fwsyGmoyMN0IV-URGQKzFJd5cxx15xIoMywHtpbOuHkKw0mBZXOhJMSsNcrDoo3YmawGks9lWYIat6aAqSE=w800-h800"]],null,[],null,null,[],null,null,null,null,null,null,null,"538","190",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}]],
			"google.com",
			null,[],[],[],[],
			null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
			[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
			null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,
			null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
			null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
			null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
			null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
			[[339,338,336,335,0],"https://www.google.com/images/google_favicon_128.png",null,null,null,null,null,{"40265033":["https://www.google.com/images/google_favicon_128.png","https://www.google.com/images/google_favicon_128.png",null,null,null,null,null,[],null,null,[],null,null,null,null,null,null,null,"128","128",null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[]]}]
		]
	}
],
null,[],[[[null,null,1]],null],null,null,2,null,null,null,"!A0KwT4G2rZQozER31gGayp1CcgIAAAAyUgAAAAkqAPfFK2GCwTjAueD7kQ3O7S-Sjv1Rl5UtUDhIJ9gt5zFEFv0DTd9fJy5rt3rGhhuMdlzdW2J2DEpWwL4yZGjEE9-1q3gRj2bXh8UEfEdcDuLcztp3WXnLdA5JnjElnP3BpiAnmF8ikbSQiZeKCjoL71k_J98tXZQ8N7Mt8dwziuHsLeVoX2MYs95kTmNZ0WZQeilw-_TidO62UDK8WS4x2bmT0VZeCsQ-OaYYWF3jomfybpLhPaTrRd6nQGbaxUkRG720hkFY02RTu7altAYL4VD9h2qhwXkHrpHkGJc0dUbVzpE-KgWsGSN1yqNa_vB2uVHt9O4T4k0_",null,null,null,[],[[true]],null,[]]
*/
/*
["one is the loneliest number.... and all that",
"oz:110087285508539122907.143e65a3d6e.0",
null,null,null,null,
"[]",
null,null,true,[],false,null,null,[],null,false,
null,null,null,null,null,null,null,null,null,null,false,false,false,
null,null,null,null,
[ // start image upload info
	[344,339,338,336,335],
	null,null,null,
	[{"39387941":[true,false]}],
	null,null,
	{
		"40655821":[
			"https://plus.google.com/photos/110087285508539122907/albums/5974938585889905969/5974938587045040082",
			"//lh3.googleusercontent.com/-IFJMU-zwNhw/Uus-_QOYD9I/AAAAAAAAACE/HS6Ejwevzt0/number-1.jpg",
			"number-1.jpg",
			"",
			null,null,null,
			[],null,null,
			[],null,null,null,null,null,null,null,
			"600","450",
			null,null,null,null,null,null,
			"110087285508539122907",
			null,null,null,null,null,null,null,null,null,null,
			"5974938585889905969",
			"5974938587045040082",
			"albumid=5974938585889905969&photoid=5974938587045040082",
			1,
			[],null,null,null,null,
			[],null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
			[]
		]
	}
], // end image upload info
null,
[],[[[null,null,1]],null],
null,null,2,null,null,null,
"!A0KwT4G2rZQozER31gGayp1CcgIAAAAyUgAAAAcqAPfFK2GCwTjAueD7kQ3O7S-Sjv1Rl5UtUDhIJ9gt5zFEFv0DTd9fJy5rt3rGhhuMdlzdW2J2DEpWwL4yZGjEE9-1q3gRj2bXh8UEfEdcDuLcztp3WXnLdA5JnjElnP3BpiAnmF8ikbSQiZeKCjoL71k_J98tXZQ8N7Mt8dwziuHsLeVoX2MYs95kTmNZ0WZQeilw-_TidO62UDK8WS4x2bmT0VZeCsQ-OaYYWF3jomfybpLhPaTrRd6nQGbaxUkRG720hkFY02RTu7altAYL4FD_h2qhwUNKW3iYHmpAj29CTJVMp1GfFBr0BKgSMYgqrsY9Bl6Nse_Q",
null,null,null,["updates"],[[true]],null,[]]
*/
		/*
["Hi there",
"oz:111441288754653925821.143e3e00154.0",
null,null,null,null,
"[]",
null,null,true,[],false,null,null,[],null,false,
null,null,null,null,null,null,null,null,null,null,false,false,false,
null,null,null,null,
null,
null, // image info
														 08b7e53d-8fff-4d8f-b418-9874b917f062
	[["113472523815604102684","08b7e53d-8fff-4d8f-b418-9874b917f062"]],[[[null,null,null,["113472523815604102684"]]]],
	[["aaaaaaaaaaaaaa","bbbbbbbbbbbbbb"]],[[[null,null,null,["aaaaaaaaaaaaaa"]]]] // community page
	[],[[[null,null,1]],null] // profile page
null,null,2,null,null,null,
"!A0IKYzQA6MOFQESExvHd7qIMEgIAAAAlUgAAAAkqAPfo3CrjyUgaB0yros4N1YfWNAaxJbGtX9YQv90IYYqAk6TVA97pOFSbg5F9bKrtFgzOG4wU6nqUEFWyzJdl9IRg76KLU6ZVXRlKnVY5WeATJIVslnCFO4cNqmAWGmACnXoiK0vI2mYj6rIvnckyhGCvLx2Fc-I3Hp3k31kPWAenjqfGJ3_pGCautQqNfvyeQFO3lVD1Mrwz3dR46wTGL2tBUUdrzppAXez1XUQGblE8vdJiojmo8J0jMPAH8z_Jy4SmLonQQxE3d_ZwEY-dbtyOjbIZ_FsGh3Sizsl4wVGEemstBShPFkBrIP-LKbBGz50L2YzR-4f5",
null,null,null,[],[[true]],null,[]]
*/
/*
[[["f.ri","110087285508539122907"]
,["sq.gslr",[["112567801843023057233",["Don't Mind Me","read the title",,"//lh6.googleusercontent.com/-ksVZA1s4Cto/AAAAAAAAAAI/AAAAAAAAAAU/cLGpsau7tSU/photo.jpg",,,,,"",,0,,[,,,,,,,,,,[]
]
]
,[1,1]
,2,,0]
,,[[["f9ae86df-efee-42e6-81ad-bb452742a391","Discussion","",[0,0]
]
]
]
,[1,1]
,[0]
,1,1,[1,1,1,1,0,0,0,1]
,,[]
,[0,1]
,2,[[]
]
]
,,,[10000,300000,10000]
,,[[[]
,"CAIQ__________9_IAAoAA",[18,2,"112567801843023057233",,,20,,"social.google.com",[]
,,,,,,,[]
,,,2,,,0,,15,,[[1002,2]
,[128,126,129,131,130,137,133]
,0,0]
,,,0,,,0,,,,0]
,,[]
,,,[[2,,[133,,0,,0]
]
,[2,,[128,,0,,1,{"45308103":["112567801843023057233",,[1,1,,[["116401124225489320013",,"eyehategplus",,1,["eyehategplus",,,,"4ef7ffdc6687f","ApRjCHSCAnC4EJxA0-Z","1FFjKmwp8oojBoJxIog",,,,1,,,,,0,,[]
,[]
,,0,,[]
,,,[]
,[]
,,3,0,,[]
,,[]
]
,1]
]
]
]
}]
]
,[2,,[126,,0,,1,{"44150514":[["112567801843023057233",["Don't Mind Me","read the title",,"//lh6.googleusercontent.com/-ksVZA1s4Cto/AAAAAAAAAAI/AAAAAAAAAAU/cLGpsau7tSU/photo.jpg",,,,,"",,0,,[,,,,,,,,,,[]
]
]
,[1,1]
,2,,0]
,,[[["f9ae86df-efee-42e6-81ad-bb452742a391","Discussion","",[0,0]
]
]
]
,[1,1]
,[0]
,1,1,[1,1,1,1,0,0,0,1]
,,[]
,[0,1]
,2,[[]
]
]
}]
]
]
,,"CAA\u003d",[360,2,[]
]
,[[[[,[[[[]
,[[0,"sii2:133",1,193]
,[0,"sii2:128",0,126]
,[0,"sii2:126",0,110]
]
]
]
]
,[0,520,520]
]
]
,1]
,[[[,[[[[]
,[[0,"sii2:133",1,193]
]
]
,[[]
,[[0,"sii2:128",0,126]
,[0,"sii2:126",0,110]
]
]
]
]
,[0,360,520]
]
]
,2]
,[[[,[[[[]
,[[0,"sii2:133",1,193]
]
]
,[[]
,[]
]
,[[]
,[[0,"sii2:128",0,126]
,[0,"sii2:126",0,110]
]
]
]
]
,[0,360,520]
]
]
,3]
]
]
,,,,,[16,,,,,,,1,,,,8,1,["112567801843023057233"]
]
]
,[1]
,[[,1,,[["116401124225489320013",,"eyehategplus",,1,["eyehategplus",,,,"4ef7ffdc6687f","ApRjCHSCAnC4EJxA0-Z","1FFjKmwp8oojBoJxIog",,,,1,,,,,0,,[]
,[]
,,0,,[]
,,,[]
,[]
,,3,0,,[]
,,[]
]
,1]
]
]
]
,["sq.smld",[[2,1,,[["116401124225489320013",,"eyehategplus",,1,["eyehategplus",,,,"4ef7ffdc6687f","ApRjCHSCAnC4EJxA0-Z","1FFjKmwp8oojBoJxIog",,,,1,,,,,0,,[]
,[]
,,0,,[]
,,,[]
,[]
,,3,0,,[]
,,[]
]
,1]
]
]
]
]
]
,["di",542,,,,,[]
,[]
,,,[]
,[]
,[]
]
,["e",4,,,2135]
]]
*/
