var _qs_sa_edit_post = _qs_sa_edit_post || {};
(function($, q, qt) {
	var custom;

	function toggle_repost_form(e) {
		e.preventDefault();
		var acct = $(this).closest('[acct]');
		$('.repost-form', acct).slideToggle(200);
	}

	function toggle_image_type_selection(e) {
		e.preventDefault();
		var self = $(this), par = self.closest('.qssa-image-selection'), id_field = par.find('.use_image');
		self.addClass('selected').siblings().removeClass('selected');

		if (self.hasClass('featured')) {
			id_field.val(wp.media.view.settings.post.featuredImageId+'');
		} else if (self.hasClass('none')) {
			id_field.val('0');
		}
	}

	function add_to_location(data) {
		var str = $.param(data), url = window.location.href, hash_parts = url.split(/#/), parts = hash_parts[0].split(/\?/);
		if (parts.length > 1) {
			url = parts.join('?') + '&' + str;
		} else {
			url = parts[0] + '?' + str;
		}
		return url;
	}

	function submit_repost_request(e) {
		e.preventDefault();
		var self = $(this), acct = self.closest('[acct]'), post_id = $('#post_ID').val();
		var data = $.extend(acct.louSerialize(), { pi:post_id, rp:acct.attr('acct'), qssa_rp:1, nonce:_qs_sa_edit_post.nonce });
		url = add_to_location(data);
		window.location.href = url;
		/*
		console.log('data', data, $.param(data));
		q.aj(data, function() {
			//window.location.reload();
		}, function() {
			//window.location.reload();
		});
		*/
	}

	function update_featured_images() {
		var image = wp.media.view.settings.post.featuredImageId >= 0
			? $('<img src="'+wp.media.featuredImage.frame().state('featured-image').get('selection').first().attributes.sizes.thumbnail.url+'" class="preview-image" />')
			: $('<div class="fake-image">No feature image selected</div>');
		$('#qs-sa-accounts .image-type.featured').each(function() { image.clone().appendTo($(this).find('.preview-image-wrap').empty()); })
			.filter('.selected').closest('[acct]').find('.use_image').val(wp.media.view.settings.post.featuredImageId+'');
		delete(image);
	}

	function show_mediabox(e) {
		e.preventDefault();
		var self = $(this), par = self.closest('.qssa-image-selection'), id_field = par.find('.use_image'), preview_cont = par.find('.image-type.select .preview-image-wrap');
		
		var on_select = function() {
			var attachment = custom.state().get('selection').first().attributes;
			id_field.val(attachment.id);
			$('<img src="'+attachment.sizes.thumbnail.url+'" class="preview-image" />').appendTo(preview_cont.empty());
		};

		if ( custom ) {
			custom.state('select-image').on('select', on_select);
			custom.open();
			return;
		} else {
			custom = wp.media({
				frame: 'select',
				state: 'select-image',
				library: { type:'image' },
				multiple: false
			});

			custom.states.add([
				new wp.media.controller.Library({
					id: 'select-image',
					title: 'Select an Image',
					priority: 20,
					toolbar: 'select',
					filterable: 'uploaded',
					library: wp.media.query( custom.options.library ),
					multiple: custom.options.multiple ? 'reset' : false,
					editable: true,
					displayUserSettings: false,
					displaySettings: true,
					allowLocalEdits: true
				}),
			]);

			custom.state('select-image').on('select', on_select);
			custom.open();
		}
	}

	$(document).on('click', '.add_media', function(e) { in_custom = false; });
	$(document).on('click', '#qs-sa-accounts .image-type', toggle_image_type_selection);
	$(document).on('click', '#qs-sa-accounts .repost-form-show', toggle_repost_form);
	$(document).on('click', '#qs-sa-accounts .repost-submit', submit_repost_request);
	$(document).on('click', '#qs-sa-accounts .select-image-button', show_mediabox);
	$(document).on('click', '#remove-post-thumbnail', update_featured_images);

	$(function() {
		var orig = wp.media.featuredImage.set;
		wp.media.featuredImage.set = function(id) {
			orig.apply(this, [].slice.call(arguments));
			console.log('selected', wp.media.view.settings.post.featuredImageId);
			update_featured_images();
		};
	});
})(jQuery, QS, QS.Tools);
