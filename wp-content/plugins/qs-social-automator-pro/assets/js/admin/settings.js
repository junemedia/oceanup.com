var _qssa_pro_admin_settings = _qssa_pro_admin_settings || { nonce:'' };
(function($, q, qt, S) {
	var global_last = {}, thresh = 2;
	function protect(func, delay, part) {
		var local_last = (new Date()).getTime()+':'+(Math.random() * 100000), delay = 200, part = part || 'all';
		global_last[part] = local_last;
		setTimeout(function() { if (local_last == global_last[part]) func(); }, delay);
	}

	function aj(sa, data, func, efunc) {
		var sa = sa || '', data = $.extend({}, data, { action:'qs-sa-pro/ajax/admin', sa:sa, n:S.nonce });
		q.aj(data, func, efunc);
	}

	function run_ajax_search(e, selbox, chosen) {
		var t = $(this);
		protect(function() {
			var val = t.val(), ty = selbox.attr('ajax-type');
			if (val.length >= thresh && val != t.attr('last')) {
				t.attr('last', val);
				encourage.call(selbox, 'searching', chosen);
				aj(ty, { s:val, t:thresh }, function(r) {
					if (qt.isO(r) && qt.is(r.r)) {
						selbox.find('option:not(:selected)').remove();
						if (r.c) {
							for (var i=0; i<r.c; i++) {
								if (selbox.find('option[value="'+r.r[i].value+'"]').length) continue;
								var l = r.r[i].name
									+(qt.is(r.r[i].extra) && r.r[i].extra ? ' ['+r.r[i].extra+']' : '')
									+(qt.is(r.r[i].id) && r.r[i].id ? ' (#'+r.r[i].id+')' : '');
								$('<option value="'+r.r[i].value+'">'+l+'</option>').appendTo(selbox);
							}
							selbox.trigger('chosen:updated');
						} else {
							encourage.call(selbox, 'none', chosen);
						}
						t.val(val).trigger('keyup');
					}
				});
			} else {
				encourage.call(selbox, 'tooshort', chosen);
			}
		}, 250, selbox.attr('name'));
	}

	function encourage(ty, chosen) {
		var t = chosen.search_field, val = t.val(), len = val.length, selbox = $(this), sets = chosen.options, sr = chosen.search_results;
		sets = $.extend({ tooshort:'keep tadjlfaskjldfjalsdjflayping...', searching:'seiakdnfklasjdlfkjaslkdfjarching...' }, sets);
		switch (ty) {
			case 'tooshort':
				sr.find('.no-results').remove();
				if (len < thresh) $('<li class="no-results">'+sets.tooshort+'</li>').appendTo(sr);
			break;

			case 'searching':
				sr.find('.no-results').remove();
				$('<li class="no-results">'+sets.searching+'</li>').appendTo(sr);
			break;

			case 'none':
				sr.find('.no-results').remove();
				$('<li class="no-results">'+chosen.results_not_found+' "<span>'+val+'</span>"</li>').appendTo(sr);
			break;
		}
	}

	function none(ty) {
		ty = ty || 'tooshort';
		encourage.call(this.form_field, ty, this);
	}

	function _init_all(e, scope) {
		var scope = $(scope || body);
		$('.use-chosen.chosen-ajax', scope).each(function() {
			var t = $(this), chosen = t.data('chosen');
			if (typeof chosen != 'object') return;
			chosen.options = $.extend(chosen.options, { disable_search_threshold:1, search_contains:1, tooshort:'Keep typing...', searching:'Searching...', none:chosen.results_not_found });
			chosen.search_contains = 1;
			chosen.no_results = none;
			t.data({ chosen:chosen });
			chosen.search_field.on('keyup', function(e) { run_ajax_search.apply(this, [e, t, chosen]); })
				.on('blur', function(e) { $(this).removeAttr('last'); });
		});
	}

	$(window).on('init-all.qssa', _init_all);
})(jQuery, QS, QS.Tools, _qssa_pro_admin_settings);
