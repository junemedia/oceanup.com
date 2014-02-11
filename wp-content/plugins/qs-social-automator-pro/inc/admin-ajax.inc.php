<?php
namespace QS\SAPro\admin\ajax {
(__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; /* prevent direct access */ use QS\helpers as h;

function init() {
	add_action('wp_ajax_qs-sa-pro/ajax/admin', __NAMESPACE__.'\\handle', 10);
	add_action('qs-sa-pro/ajax/admin/sa=users', __NAMESPACE__.'\\user_search', 10, 3);
	add_action('qs-sa-pro/ajax/admin/sa=tax', __NAMESPACE__.'\\tax_search', 10, 3);
}

function tax_search($resp, $post, $sa) {
	@list($sa, $tax_slug) = explode(':', $sa);
	$tax = get_taxonomy($tax_slug);

	if (empty($tax_slug)) {
		$resp['e'][] = 'You must specify a valid taxonomy. ['.$tax_slug.']';
		return $resp;
	}

	$search = isset($post['s']) ? $post['s'] : '';
	$thresh = isset($post['t']) && (int)$post['t'] >= 2 ? (int)$post['t'] : 2;
	if (strlen($search) < $thresh) {
		$resp['e'][] = 'You must give at least '.$thresh.' characters to conduct a search.';
		return $resp;
	}

	$terms = get_terms(array($tax_slug), array(
		'search' => $search,
		'hide_empty' => false,
	));

	$resp['r'] = array();
	$resp['c'] = 0;
	foreach ($terms as $term) {
		$resp['r'][] = array(
			'value' => $term->term_id,
			'id' => $term->term_id,
			'name' => $term->name,
			'extra' => $term->slug,
		);
		$resp['c']++;
	}

	return $resp;
}

function user_search($resp, $post, $sa) {
	$search = isset($post['s']) ? $post['s'] : '';
	$thresh = isset($post['t']) && (int)$post['t'] >= 2 ? (int)$post['t'] : 2;
	if (strlen($search) < $thresh) {
		$resp['e'][] = 'You must give at least '.$thresh.' characters to conduct a user search.';
		return $resp;
	}

	$users = get_users(array(
		'search' => '*'.trim($search, '*').'*',
	));

	$resp['r'] = array();
	$resp['c'] = 0;
	foreach ($users as $user) {
		$resp['r'][] = array(
			'value' => $user->ID,
			'id' => $user->ID,
			'name' => $user->display_name,
			'extra' => $user->user_login.' : '.$user->user_email,
		);
		$resp['c']++;
	}

	return $resp;
}

function handle() {
	// organize data
	$post = $_POST;
	$nonce = isset($post['n']) ? $post['n'] : false;
	$sa = isset($post['sa']) ? $post['sa'] : '';
	$base_sa = array_shift(explode(':', $sa));

	// make sure we are receiving ajax from the settings page
	if (!wp_verify_nonce($nonce, 'qs-sa-pro/admin-ajax')) return _out(array('e' => 'Could not process the request.'));

	// call any handlers attached to this ajax hook
	$resp = array('e' => array(), 'm' => array());
	$resp = apply_filters('qs-sa-pro/ajax/admin/sa='.$base_sa, $resp, $post, $sa);
	$resp = apply_filters('qs-sa-pro/ajax/admin', $resp, $post, $sa);

	if (empty($resp['e'])) unset($resp['e']);
	if (empty($resp['m'])) unset($resp['m']);

	_out($resp);
}

// generic ajax response function, based on requested response type and response data
function _out($data, $die=true) {
	// find the expected response type
	$resp_type = _determine_response_type();
	// indicate that the response is of that type
	header('Content-Type: '.$resp_type);

	// depending on the response type, do something different with the response data
	switch ($resp_type) {
		// text and html responses are mostly the same
		case 'text/plain':
		case 'text/html':
			if (!is_scalar($data)) {
				if (isset($data['html'])) echo force_balance_tags($data['html']);
				else if (isset($data['text'])) echo $data['text'];
				else if (isset($data['out'])) echo $data['out'];
				else echo @json_encode($data);
			} else {
				echo $data;
			}
		break;
		
		// javascript/json responses are favored by me, so this is the default, and the easiest to print out
		case 'application/json':
		case 'text/json':
		case 'application/javascript':
		case 'text/javascript':
		default:
			echo @json_encode($data);
		break;
	}

	// die if needed
	if ($die) exit;
}

// break down the request header 'accept' value, and determine the first accepted response type, or select our default
function _determine_response_type() {
	// get the header value
	$accepts_raw = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'application/json';

	// break it up into sections, of which we are only concerned with the first
	$parts = preg_split('#\s*;\s*#', $accepts_raw);

	// split the first grouping up by the standard delimiters
	$types = preg_split('#\s*,\s*#', (string)array_shift($parts));

	// get the first of the accepted types
	$accept = trim((string)array_shift($types));

	// return the resulting type, default to our primary default type
	return $accept ? $accept : 'application/json';
}

if (defined('ABSPATH') && function_exists('add_action')) {
	init();
}

}
