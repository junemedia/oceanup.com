var QS = QS || {};
QS.Tools = QS.Tools || {};
var console = console || { log:function(){} };

(function($, q) {
	qt = QS.Tools;
  if (!Array.prototype.filter) Array.prototype.filter = function(fun) { "use strict"; if (this == null) throw new TypeError(); var t = Object(this); var len = t.length >>> 0; if (typeof fun != "function") throw new TypeError(); var res = []; var thisp = arguments[1]; for (var i = 0; i < len; i++) { if (i in t) { var val = t[i]; if (fun.call(thisp, val, i, t)) res.push(val); } } return res; }; 

  qt.is = function(o) { return typeof o != 'undefined' && o != null; };
  qt.isO = function(o) { return qt.is(o) && typeof o == 'object'; };
  qt.isA = function(o) { return qt.isO(o) && o instanceof Array; };
  qt.isF = function(o) { return typeof o == 'function'; };
  qt.isN = function(o) { return typeof o == 'number'; };
  qt.isS = function(o) { return typeof 0 == 'string'; };
  qt.toInt = function(o) { var r = parseInt(o); return isNaN(r) ? 0 : r; };
  qt.toFloat = function(o) { var r = parseFloat(o); return isNaN(r) ? 0 : r; };
  qt.pl = function(o, p) { return qt.toFloat(o).toFixed(p); };
  qt.yn = function(o) { return !!qt.toInt(o); };
  qt.queryString = (function() { var qs = {}, q = window.location.search.substring(1), vars = q.split("&"); for (var i=0;i<vars.length;i++) { var pair = vars[i].split("="); if (typeof qs[pair[0]] === "undefined") { qs[pair[0]] = pair[1]; } else if (typeof qs[pair[0]] === "string") { var arr = [ qs[pair[0]], pair[1] ]; qs[pair[0]] = arr; } else { qs[pair[0]].push(pair[1]); } } return qs; })();
  qt.hash = (function() { var rp = '_rp'+(Math.random()*10000000); function tstr() { var out = '', i = ''; if (this[rp]) { this[rp] = undefined; return out; } for (i in this) if (i != rp && this.hasOwnProperty(i)) out += i + ':' + ( this[i] instanceof Object ? ((this[rp] = true) && this[i] != this && !this[i][rp] ? tstr.call(this[i]) : '') : (this[i].toString || tstr).call(this[i]) ); return out; };   function hash(el) { if (typeof el == 'number') return el.valueOf(); var s = (el instanceof Object ? tstr : el.toString || tstr).call(el), h = 0; if (s.length) for (var i = 0; i < s.length; i++) h = ((h << 5) - h) + s.charCodeAt(i); return h; };   return hash; })();

  qt.cbs = function(c, f, s) {
    var t = this, _cbs = {}, f = typeof f == 'string' && f.length > 0 ? f : 'callback', s = typeof s == 'string' && s.length > 0 ? s : 'callbacks';
    function cb_add(n, u) { if (typeof u == 'function') { if (!(_cbs[n] instanceof Array)) _cbs[n] = []; _cbs[n].push(u); } };  
    function cb_remove(n, u) { if (typeof u == 'function' && _cbs[n] instanceof Array) { _cbs[n] = _cbs[n].filter(function(f) { return f.toString() != u.toString(); }); } };
    function cb_get(n) { if (!(_cbs[n] instanceof Array)) return []; return _cbs[n].filter(function(f) { return true; }); };
    function cb_trigger(n, p, x) { var x = x || this; var p = p || []; var cbs = cb_get(n); if (cbs instanceof Array) { for (var i=0; i<cbs.length; i++) cbs[i].apply(x, p); } };
    function _debug_handlers() { console.log('debug_'+s, $.extend({}, _cbs)); };
    t.add = cb_add; t.remove = cb_remove; t.get = cb_get; t.trigger = cb_trigger; t.debug = _debug_handlers; 
    if (typeof c == 'function') { c.prototype[f] = cb_trigger; c.prototype[s] = t; c[f] = cb_trigger; c[s] = t;
    } else if (typeof c == 'object' && c !== null) { c[f] = cb_trigger; c[s] = t; }
  };  

  qt.cookie = { 
    set: function(name, value, expire, path) { var name = $.trim(name); if (name == '') return; var value = escape($.trim(value)); if (typeof expire == 'undefined' || expire == null || expire == 0) { expire = ''; } else if (expire < 0) { var dt = new Date(); dt.setTime(dt.getTime() - 100000); expire = ';expires='+dt.toUTCString(); } else { var dt = new Date(); dt.setTime(dt.getTime() + expire*1000); expire = ';expires='+dt.toUTCString(); } if (typeof path == 'undefined' || path == null) { path = ''; } else { path = ';path='+path; } document.cookie = name+'='+value+expire+path; },
    get: function(name) { var name = $.trim(name); if (name == '') return; var n,e,i,arr=document.cookie.split(';'); for (i=0; i<arr.length; i++) { e = arr[i].indexOf('='); n = $.trim(arr[i].substr(0,e)); if (n == name) return $.trim(unescape(arr[i].substr(e+1))); } }
  };

	q.aj = function(data, func, efunc) {
    var func = func || function(){}, efunc = function(){}; 

    $.ajax({
      url: ajaxurl,
      type: 'post',
      data: data,
      dataType: 'json',
      success: function(r) {
        var r = qt.isO(r) ? r : { e:['Invalid Response'], _raw:r };
        if (qt.isO(r.e)) {
          if (qt.isA(r.e)) for (var i=0; i<r.e.length; i++) console.log('AJAX ERROR: ', r.e[i]);
          else console.log('AJAX ERROR: ', r.e);
        }   
        func(r);
      },  
      error: efunc
    }); 
  };  

})(jQuery, QS);

(function($) {
	$.fn.qsEndOf = function(settings) {
		return this.each(function() {
			var elem = this, elemLen = elem.value.length;
			if (document.selection) {
				elem.focus();
				var oSel = document.selection.createRange();
				oSel.moveStart('character', -elemLen);
				oSel.moveStart('character', elemLen);
				oSel.moveEnd('character', 0);
				oSel.select();
			} else if (elem.selectionStart || elem.selectionStart == '0') {
				elem.selectionStart = elemLen;
				elem.selectionEnd = elemLen;
				elem.focus();
			}
    });
	};
})(jQuery);

(function($) {
	$.fn.qsBlock = function(settings) {
		return this.each(function() {
			var element = $(this), off = element.offset(), dims = { width:element.width(), height:element.height() },
					sets = $.extend(true, { msg:'<h1>Loading...</h1>', css:{ backgroundColor:'#000000', opacity:0.5 }, msgcss:{ color:'#ffffff' } }, settings),
					bd = $('<div class="block-backdrop"></div>').appendTo('body'), msg = $('<div class="block-msg">'+sets.msg+'</div>').appendTo('body'),
					mhei = msg.height();
			bd.css($.extend({
				position: 'absolute',
				width: dims.width,
				height: dims.height,
				top: off.top,
				left: off.left
			}, sets.css));
			msg.css($.extend({
				textAlign: 'center',
				position: 'absolute',
				width: dims.width,
				top: off.top + ((dims.height - mhei) / 2), 
				left: off.left
			}, sets.msgcss));

			var ublock = function() { bd.remove(); msg.remove(); element.off('unblock', ublock); }
			element.on('unblock', ublock);
		});
  };  

  $.fn.qsUnblock = function(element) { return this.each(function() { $(this).trigger('unblock'); }); };
})(jQuery);

(function($, undefined) {
	$.fn.louSerialize = function(data) {
		function _extractData(selector) {
			var data = {};
			var self = this;
			$(selector).filter(':not(:disabled)').each(function() {
				if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio')
					if ($(this).filter(':checked').length == 0) return;
				if (typeof $(this).attr('name') == 'string' && $(this).attr('name').length != 0) {
					var res = $(this).attr('name').match(/^([^\[\]]+)(\[.*\])?$/);
					var name = res[1];
					var val = $(this).val();
					if (res[2]) {
						var list = res[2].match(/\[[^\[\]]*\]/gi);
						if (list instanceof Array && list.length > 0) {
							if (data[name]) {
								if (typeof data[name] != 'object') data[name] = {'0':data[name]};
							} else data[name] = {};
							data[name] = _nest_array(data[name], list, val);
						}
					} else data[name] = val;
				}
			});
			return data;
		}

		function _nest_array(cur, lvls, val) {
			if (typeof cur != 'object' && lvls instanceof Array && lvls.length > 0) cur = [];
			var lvl = lvls.shift();
			lvl = lvl.replace(/^\[([^\[\]]*)\]$/, '$1') || '';
			if (lvl == '') {
				if (!(cur instanceof Array)) cur = [];
				if (lvls.length > 0) cur[cur.length] = _nest_array([], lvls, val);
				else cur[cur.length] = val;
			} else {
				if (lvls.length > 0) {
					if (cur[lvl]) {
						if (typeof cur[lvl] != 'object') cur[lvl] = {'0':cur[lvl]};
					} else cur[lvl] = {};
					cur[lvl] = _nest_array(cur[lvl], lvls, val);
				} else cur[lvl] = val;
			}
			return cur;
		}
		var data = data || {};
		return $.extend(data, _extractData($('input[name], textarea[name], select', this)));
	}

	$.paramStandard = $.param;

	$.paramAll = function(a, tr, cur, dep) {
		var dep = dep || 0;
		var cur = cur || '';
		var res = [];
		var a = $.extend({}, a);

		var nvpair = false;
		$.each(a, function(k, v) {
			if (k == 'name' && typeof v == 'string' && typeof a['value'] == 'string' && v.length > 0) {
				cur = v;
				nvpair = true;
				return;
			} else if (nvpair && k == 'value') {
				nvpair = false;
				var t = cur;;
			} else {
				var t = cur == '' ? k : cur+'['+k+']';
			}
			switch (typeof(v)) {
				case 'number':
				case 'string': t = t+'='+escape(v); break;
				case 'boolean': t = t+'='+escape(parseInt(v).toString()); break;
				case 'undefined': t = t+'='; break;
				case 'object': t = $.paramAll(v, tr, t, dep+1); break;
				default: return; break;
			}
			if (typeof(t) == 'object') {
				for (i in t) res[res.length] = t[i];
			} else res[res.length] = t;
		});
		return dep == 0 ? res.join('&') : res;
	}

	$.param = function(a, tr, ty) {
		switch (ty) {
			case 'standard': return $.paramStandard(a, tr); break;
			default: return $.paramAll(a, tr); break;
		}
	}

	$.deparam = function(q) {
		var params = {};
		if (typeof q == 'string') {
			var p = q.split('&');
			for (var i=0; i<p.length; i++) {
				var parts = p[i].split('=');
				var n = parts.shift();
				var v = parts.join('=');
				var tmp = v;
				var pos = -1;
				while ((pos = n.lastIndexOf('[')) != -1) {
					var k = n.substr(pos);
					k = k.substr(1, k.length-2);
					n = n.substr(0, pos);
					var t = {};
					t[k] = tmp;
					tmp = t;
				}
				if (typeof params[n] == 'object') params[n] = $.extend(true, params[n], tmp);
				else params[n] = tmp;
			}
		}
		return params;
	};

	$['lou'+'Ver']=function(s){alert(s.o.author+':'+s.o.version+':'+s.o.proper);}
})(jQuery);
