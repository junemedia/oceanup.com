(function($) {
	var settings = _qsism_settings || {};

	is = function(v) { return typeof v != 'undefined' && v != null; };
	isF = function(v) { return typeof v == 'function'; };
	isO = function(v) { return is(v) && typeof v == 'object'; };
	isA = function(v) { return isO(v) && v instanceof Array; };
	toI = function(val) { var n = parseInt(val); return isNaN(n) ? 0 : n; };
	toF = function(val) { var n = parseFloat(val); return isNaN(n) ? 0 : n; };

	function aj(sa, data, func, efunc, extra) {
		var req = $.extend({}, data, { n:settings.n, action:settings.act, sa:sa }), func = func || function(r){}, efunc = efunc || function(x, s, e) { console.log(x, s, e); };
		$.ajax($.extend({
			url: ajaxurl,
			accepts: 'application/json',
			cache: false,
			data: req,
			dataType: 'json',
			type: 'POST',
			error: efunc,
			success: function(r, stts, xhr) {
				if (isO(r)) {
					if (!r.s && isA(r.e) && r.e.length) {
						for (i in r.e) genlog('ERROR: '+r.e[i]);
					}
					func(r);
				} else {
					efunc(xhr, stts, 'Invalid Response Format');
				}
			}
		}, extra));
	}

	var prog = undefined;
	function progress(perc, msg, anim_time, end_perc) {
		if (!is(prog)) prog = $('[rel="genout"] [rel="progress"]');
		var anim_time = anim_time || 0;
		var end_perc = end_perc || 95;
		prog.find('[rel="bar"]').finish().css({ width:perc+'%' });
		prog.find('[rel="text"]').html(msg);
		if (anim_time) prog.find('[rel="bar"]').animate({ width:end_perc+'%' }, { duration:anim_time * 1000 });
	}

	var log = undefined;
	function genlog(msgs, type) {
		if (!is(log)) log = $('[rel="genlog"]');
		var type = type ? type : 'err';
		if (isA(msgs)) {
			for (i in msgs) {
				$('<div class="'+type+'">'+msgs[i]+'</div>').appendTo(log);
			}
		} else {
			$('<div class="'+type+'">'+msgs+'</div>').appendTo(log);
		}
		log.finish().animate({ scrollTop: log.prop('scrollHeight') }, 700);
		$('html, body').animate({ scrollTop:log.offset().top }, 500);
	}

	var queue = [];
	function run_queue_item() {
		if (queue.length == 0) {
			$('[rel="form-actions"]').find('input[type="submit"], input[type="button"]').removeAttr('disabled');
			return;
		}
		item = queue.shift();

		if (is(item.before)) {
			genlog(item.before.msg, 'msg');
			if (item.before.duration) {
				var start_perc = item.before.start_perc || 0.5;
				var end_perc = item.before.end_perc || 95;
				progress(start_perc, item.before.msg, item.before.duration, end_perc);
			}
		} else {
			genlog('Running [ '+JSON.stringify(item)+' ].', 'notice');
		}

		if (is(item.action)) {
			switch (item.action['do']) {
				case 'wait': setTimeout(function() { run_queue_item(); }, item.action.duration); break;
				default: run_queue_item(); break;
			}
			return;
		}

		aj('run-item', item.run, function(r) {
			if (r.s) {
				genlog('... success', 'msg');
				genlog('...... response [ '+JSON.stringify(r)+' ]', 'msg');
				if (isO(r.r)) {
					if (r.r.perc && r.r.msg) {
						progress(toF(r.r.perc), r.r.msg);
						genlog('+++ '+r.r.msg, 'resp');
					}
					if (is(r.r.action)) { console.log(r.r.action); switch (r.r.action['do']) {
						case 'queue':
							if (is(r.r.action.items))
								for (var i=0; i<r.r.action.items.length; i++)
									queue.push(r.r.action.items[i]);
						break;
					} }
				}
			} else {
				genlog('... failure');
				genlog(r.e);
			}

			run_queue_item();
		}, function() {
			genlog('Communication problem occured [ '+JSON.stringify(item)+' ].');
			run_queue_item();
		});
	}

	function start_generation(force, scratch) {
		force = force || 0;
		scratch = scratch || 0;
		genlog('Starting generation...', 'msg');
		progress(0.5, 'Starting generation...');
		aj('queue', { force:force, scratch:scratch }, function(r) {
			progress(100, 'Queue Loaded.');
			setTimeout(function() {
				queue = r.r;
				progress(0.5, 'Making Preparations...');
				run_queue_item();
			}, 500);
		});
	}

	function start_generation_one(page) {
		genlog('Starting re-generation of page ['+page+']...', 'msg');
		progress(0.5, 'Starting re-generation of page ['+page+']...');
		aj('queue', { page:page }, function(r) {
			progress(100, 'Queue Loaded.');
			setTimeout(function() {
				queue = r.r;
				progress(0.5, 'Making Preparations...');
				run_queue_item();
			}, 500);
		});
	}

	$(document).on('click', '[rel="genbtn"], [rel="regen-all"]', function(e) {
		e.preventDefault();
		$(this).attr('disabled', 'disabled').siblings('input[type=submit], input[type=button]').attr('disabled', 'disabled');
		var genout = $('[rel="genout"]').show();
		$('html, body').animate({ scrollTop:genout.offset().top }, 500);
		var genall = $(this).attr('rel') == 'regen-all' ? 1 : 0,
			scratch = !genall && $('[rel="scratch"]:checked').length ? 1 : 0;
		start_generation(genall || scratch, scratch);
	});

	$(document).on('click', '[rel="regen-one"]', function(e) {
		e.preventDefault();
		var page = $(this).attr('page');
		if (!is(page) || page <= 0) return;

		$(this).attr('disabled', 'disabled').siblings('input[type=submit], input[type=button]').attr('disabled', 'disabled');
		var genout = $('[rel="genout"]').show();
		$('html, body').animate({ scrollTop:genout.offset().top }, 500);
		start_generation_one(page);
	});
})(jQuery);
