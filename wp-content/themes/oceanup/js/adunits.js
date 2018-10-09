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

  // Zergnet sidebar unit
  (function() {
    var zergnet = document.createElement('script');
    zergnet.type = 'text/javascript';
    zergnet.async = true;
    zergnet.src = (document.location.protocol == "https:" ? "https:" : "http:") + '//www.zergnet.com/zerg.js?id=51911';
    var znscr = document.getElementsByTagName('script')[0];
    znscr.parentNode.insertBefore(zergnet, znscr);
  })();

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
  // I got nothing at this time...
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
