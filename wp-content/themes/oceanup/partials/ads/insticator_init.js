// only initialize on desktop
if (OU.screenContext === 'desktop') {
  /*<![CDATA[*/
  (function (a, c, s, u) {
    'Insticator' in a || (a.Insticator = {
      ad: {
        loadAd: function (b) {
          Insticator.ad.q.push(b)
        },
        q: []
      },
      helper: {},
      embed: {},
      version: "3.0",
      q: [],
      load: function (t, o) {
        Insticator.q.push({t: t, o: o})
      }
    });
    var b = c.createElement(s);
    b.src = u;
    b.async = !0;
    var d = c.getElementsByTagName(s)[0];
    d.parentNode.insertBefore(b, d)
  })(window, document, 'script', '//d2na2p72vtqyok.cloudfront.net/client-embed/683d18db-6bc5-4f27-a9a3-2e8665d1ab4c.js');
  /*]]>*/
}
