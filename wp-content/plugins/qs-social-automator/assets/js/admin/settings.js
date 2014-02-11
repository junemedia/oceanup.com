var _qs_ap_admin_settings = _qs_ap_admin_settings || { nonce:'' };
(function($, q, qt, S) {
	var actions = [], current_action = undefined;
	window._qssa_msg = function(msg) {
		if (current_action.type == 'popup') current_action = undefined;
	};

	function run_action() {
		if (current_action) return;
		if (!actions.length) return;

		current_action = actions.shift();
		console.log('doing action', current_action);
		switch (current_action.type) {
			case 'block':
				var args = {};
				if (qt.is(current_action.msg)) args.msg = current_action.msg;
				current_action.obj.qsBlock(args);
				current_action = undefined;
			break;

			case 'popup':
				var pop = window.open(current_action.url, current_action.name, 'height=400,width=445,left=0,top=100,location=0,menubar=0,status=0,toolbar=0', true);
				if (pop.focus) pop.focus();
				pop.onbeforeunload = function() { current_action = undefined; }
			break;
			
			case 'refresh':
				// prevent infinite auth loop on strange failures
				$('<input type="hidden" name="already-attempted" value="1" />').appendTo(current_action.obj);
				current_action.obj.find('[rel="save-acct"]').trigger('click', [function() { current_action = undefined; }, function() { current_action = undefined; }]);
			break;
		}
	}
	setInterval(run_action, 25);

	function queue_actions(list, obj) {
		for (var i=0; i<list.length; i++) list[i].obj = obj;
		actions = actions.concat(list);
	}

	function aj(sa, data, func, efunc) {
		var data = $.extend({}, data, { sa:sa, action:'qs-sa/ajax/admin', n:S.nonce });
		console.log(data);
		q.aj(data, func, efunc);
	}

	function _reverify_click(e) {
		e.preventDefault();
		var url = $(this).attr('href'), acct = $(this).closest('[rel="acct"]');
		url = url.match(/\?/) ? url+'&popup=1' : url+'?popup=1';
		actions = [
			{ type:'block', name:'block-'+acct.attr('acct'), msg:'<h1>Reverifying...</h1>', obj:acct },
			{ type:'popup', name:'reverify-'+acct.attr('acct'), url:url, obj:acct },
			{ type:'refresh', name:'reload-'+acct.attr('acct'), obj:acct}
		];
		current_action = undefined;
	}

	function _add_account(e) {
		var btn = $(this), scope = btn.closest(btn.attr('scope') || 'body'), from = btn.attr('from') || false, from = !from ? btn.prev(':input') : scope.find(from);
		if (!from.length) {
			console.log('Could not find the [from] to pull the acct-type from.');
			return;
		}

		var data = { type:from.val() }, acct_list = $('[rel="account-list"]');

		aj('new-acct', data, function(r) {
			if (qt.isO(r) || qt.is(r.html)) {
				acct_list.find('.no-accts').remove();
				var new_acct = $(r.html).attr(r.acct).appendTo(acct_list);
				$('html, body').animate({scrollTop: new_acct.offset().top-40}, 200);
				var first = new_acct.find(':input:visible:eq(1)').focus().qsEndOf();
				_init_all(new_acct);
			} else {
				console.log('An unexpected ajax error occured. ', r);
			}
		});
	}

	function _save_account(e, afterSuccess, afterFail) {
		var btn = $(this), scope = btn.closest(btn.attr('scope')), fields = scope.louSerialize(),
				afterSuccess = qt.isF(afterSuccess) ? afterSuccess : function(){},
				afterFail = qt.isF(afterFail) ? afterFail : function(){};

		if (!scope.length || !fields.acct_id) {
			console.log('Could not find the [acct_id] for this account.', btn, scope);
			return;
		}

		scope.qsUnblock().qsBlock({ msg:'<h1>Saving...</h1>' });

		aj('save-acct', fields, function(r) {
			if (qt.isO(r) || qt.is(r.html)) {
				var acct = $(r.html).attr(r.acct).insertBefore(scope);
				$('html, body').animate({scrollTop: acct.offset().top-40}, 200);
				scope.qsUnblock().remove();
				_init_all(acct);
				if (qt.isA(r.actions)) {
					actions = [];
					current_action = undefined;
					queue_actions(r.actions, acct);
				}
				setTimeout(function() {
					acct.find('.messages-wrapper').fadeOut({duration:1000, complete:function() { $(this).remove() }});
				}, 3000);
				afterSuccess();
			} else {
				console.log('An unexpected ajax error occured. ', r);
				afterFail();
			}
		}, function() {
			afterFail();
		});
	}

	function _remove_account(e) {
		e.preventDefault();
		var btn = $(this), acct = btn.closest('[rel="acct"]'), acct_id = acct.attr('acct-id'), name = acct.find('.header:eq(0) .full-title').html(), width = $(document).width(),
				dia = $(S.templates['ays-delete']).appendTo('body'), width = width > 500 ? Math.ceil(width * (2/3)) : width - 20;
		dia.find('.account-name').html(name);
		dia.dialog({
			modal: true, autoOpen: true, resizable: false, width: width,
			buttons: {
				'Yes. Delete it.': function(ev) {
					dia.dialog('close');
					dia = $(S.templates['deleting']).appendTo('body').dialog({
						modal: true, autoOpen: true, resizable: false, draggable: false, closeOnEscape: false, width:width,
						close: function(ev, ui) { $(this).dialog('destroy').remove(); }
					});
					dia.closest('.ui-dialog').addClass('deleting-dialog');

					var data = { acct_id:acct_id }
					aj('del-acct', data, function(r) {
						if (qt.isO(r) && qt.is(r.s) && r.s) {
							acct.remove();
							dia.dialog('close');
							dia = $(S.templates['deleted']).appendTo('body');
							dia.find('.account-name').html(name);
							dia.dialog({
								modal: true, autoOpen: true, resizable: false, width:width,
								buttons: { 'Ok. Thanks.': function(ev) { dia.dialog('close'); } },
								close: function(ev, ui) { $(this).dialog('destroy').remove(); }
							});
						} else {
							console.log('Problem occurred while trying to remove the account ['+name+'].');
							dia.dialog('close');
						}
					});
				},
				'Wait. No.': function(ev) { dia.dialog('close'); }
			},
			close: function(ev, ui) { $(this).dialog('destroy').remove(); }
		});
	}

	function _update_box_settings(acct_id, closed) {
		qt.cookie.set('qssa-close-box-'+acct_id, closed ? '1' : 0, 63072000, '/wp-admin');
		aj('update-close-state', { acct_id:acct_id, closed:closed ? 1 : 0 });
	}
	function _is_closed(acct_id) { return qt.toInt(qt.cookie.get('qssa-close-box-'+acct_id)); }

	function _init_tog(accts) {
		var accts = accts || $();
		if (!accts.length) return;
		accts.each(function() { var me = $(this), acct_id = me.attr('acct-id'); if (_is_closed(acct_id)) me.addClass('closed'); });
	}

	function _tog_acct_settings(e) {
		var btn = $(this), box = btn.closest('[rel="acct"]'), acct_id = box.attr('acct-id');
		box.toggleClass('closed');
		if (qt.is(acct_id) && acct_id) _update_box_settings(acct_id, box.hasClass('closed'));
	}

	function _init_chosen(selector, scope) {
		var scope = scope || 'body', selector = selector || false;
		if (!selector) return;

		$(selector, scope).filter(':not(.almost-chosen)').addClass('almost-chosen').chosen({ disable_search_threshold:30 });
	}

	function _init_tabs(selector, scope) {
		var scope = scope || 'body', selector = selector || false;
		if (!selector) return;

		$(selector, scope).tabs();
	}

	function _init_all(scope) {
		var scope = scope || 'body';
		_init_chosen('.use-chosen', scope);
		_init_tabs('.use-tabs', scope);
		$(window).trigger('init-all.qssa', [scope]);
	}

	$(document).on('click.qs-sa', '[rel="acct"] .header h4 input', function(e) { e.stopPropagation(); });
	$(document).on('click.qs-sa', '[rel="acct"] .header h4', _tog_acct_settings);
	$(document).on('click.qs-sa', '[rel="acct"] .header [rel="control-acct"]', _tog_acct_settings);
	$(document).on('click.qs-sa', '[rel="add-btn"]', _add_account);
	$(document).on('click.qs-sa', '[rel="delete-btn"]', _remove_account);
	$(document).on('click.qs-sa', '[rel="save-acct"]', _save_account);
	$(document).on('click.qs-sa', '[rel="reverify"]', _reverify_click);

	$(function() {
		_init_all();
		_init_tog($('[rel="account-list"] [rel="acct"]'));
	});
})(jQuery, QS, QS.Tools, _qs_ap_admin_settings);
