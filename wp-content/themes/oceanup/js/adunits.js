// Initialize OpenX ads array
var OX_ads = OX_ads || [];


/* Global ad units */
// push the two mid-post ads only if they exist on the current page
if (document.getElementById('538014858_300x250MP-1') !== null) {
  OX_ads.push({
    slot_id: '538014858_300x250MP-1',
    auid: '538014858'
  });
}
if (document.getElementById('538096513_300x250MP-2') !== null) {
  OX_ads.push({
    slot_id: "538096513_300x250MP-2",
    auid: "538096513"
  });
}

/* Desktop-only ad units */
if (OU.screenContext === 'desktop') {
  OX_ads.push(
    // header banner ad
    { slot_id: 'header_728x90ATF', auid: '538014853' },

    // rail big box 1
    { slot_id: '538014855_300x250ATF', auid: '538014855' },

    // rail skyscraper
    { slot_id: '538096517_160x600BTF', auid: '538096517' },

    // footer banner ad
    { slot_id: 'footer_728x90BTF', auid: '538014854' }
  );

  // Content.ad 1x6 right rail unit
  (function(d) {
    var params =
    {
      id: "5dddd54e-0bd5-4920-bac0-06f90d7643e3",
      d:  "b2NlYW51cC5jb20=",
      wid: "314792",
      cb: (new Date()).getTime()
    };

    var qs=[];
    for(var key in params) qs.push(key+'='+encodeURIComponent(params[key]));
    var s = d.createElement('script');s.type='text/javascript';s.async=true;
    var p = 'https:' == document.location.protocol ? 'https' : 'http';
    s.src = p + "://api.content-ad.net/Scripts/widget2.aspx?" + qs.join('&');
    d.getElementById("contentad314792").appendChild(s);
  })(document);

  // Zergnet sidebar unit
  (function() {
    var zergnet = document.createElement('script');
    zergnet.type = 'text/javascript';
    zergnet.async = true;
    zergnet.src = (document.location.protocol == "https:" ? "https:" : "http:") + '//www.zergnet.com/zerg.js?id=51911';
    var znscr = document.getElementsByTagName('script')[0];
    znscr.parentNode.insertBefore(zergnet, znscr);
  })();

  // 33Across SiteCTRL
  var Tynt=Tynt||[];Tynt.push('dDKOmWzM4r56O3aKltUXmc');
  (function(){var h,s=document.createElement('script');
  s.src=(window.location.protocol==='https:'?
      'https':'http')+'://cdn.tynt.com/ti.js';
  h=document.getElementsByTagName('script')[0];
  h.parentNode.insertBefore(s,h);})();
}

/* Tablet and mobile ad units */
else {
  // ad at bottom of post
  if (document.getElementById('538014858_300x250POST-1') !== null) {
    OX_ads.push({
      slot_id: '538014858_300x250POST-1',
      auid: '538014858'
    });
  }

  OX_ads.push(
    // header mobile banner ad
    { slot_id: 'header_728x90ATF', auid: '538099189' },

    // footer mobile banner ad
    { slot_id: 'footer_728x90BTF', auid: '538099192' }
  );

}

/* Units at bottom of posts/loop */
if (OU.screenContext === 'desktop' || OU.screenContext === 'tablet') {
  (function(d) {
    var params =
    {
      id: "863aca8a-df2c-4353-8a82-9c58a6f51737",
      d:  "b2NlYW51cC5jb20=",
      wid: "314793",
      cb: (new Date()).getTime()
    };

    var qs=[];
    for(var key in params) qs.push(key+'='+encodeURIComponent(params[key]));
    var s = d.createElement('script');s.type='text/javascript';s.async=true;
    var p = 'https:' == document.location.protocol ? 'https' : 'http';
    s.src = p + "://api.content-ad.net/Scripts/widget2.aspx?" + qs.join('&');
    d.getElementById("contentad314793").appendChild(s);
  })(document);
}
else {
  (function() {
    var zergnet = document.createElement('script');
    zergnet.type = 'text/javascript'; zergnet.async = true;
    zergnet.src = (document.location.protocol == "https:" ? "https:" : "http:") + '//www.zergnet.com/zerg.js?id=43935';
    var znscr = document.getElementsByTagName('script')[0];
    znscr.parentNode.insertBefore(zergnet, znscr);
  })();
}
