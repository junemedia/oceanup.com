<?php

/**
 *  * LiveRamp Match Partner tags
 *   */
$lr_ei_tagid = '424306';
$lr_rc_tagid = '424346';

// default to using recookie
$lr_recookie = true;

// open the tag...
$lr_tag = '<iframe name="_rlcdn" width=0 height=0 frameborder=0 src="';

// if user is logged in and has an email address, serve
// match partner tag
if ( is_user_logged_in() ) {
  $current_user = wp_get_current_user();

  if ( $current_user->user_email ) {
    $lr_tag .= '//ei.rlcdn.com/' . $lr_ei_tagid . '.html';
    $lr_tag .= '?s=' . sha1( strtolower( $current_user->user_email ) );

    $lr_recookie = false;
  }
}

// otherwise serve recookier tag
if ( $lr_recookie )  {
  $lr_tag .= '//rc.rlcdn.com/' . $lr_rc_tagid . '.html';
}

// ...close the tag
$lr_tag .= '"></iframe>';

echo $lr_tag;
