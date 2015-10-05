/*
 * OU "namespace"
 *
 * TODO: should get moved into oceanup.js
 */
var OU = OU || {};

OU.breakPoint = {
  mobile: 480,
  tablet: 768,
  desktop: false
};

OU.setScreenContext = function setScreenContext(screenWidth) {
  var mobile  = OU.breakPoint.mobile;
  var tablet = OU.breakPoint.tablet;

  if (screenWidth <= mobile) {
    return 'mobile';
  }
  if (screenWidth > mobile && screenWidth <= tablet) {
    return 'tablet';
  }
  return 'desktop';
}

OU.screenContext = OU.setScreenContext(document.documentElement.clientWidth);
console.info('screen context:', OU.screenContext);


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

    // rail big box 2
    { slot_id: '538014856_300x250BTF', auid: '538014856' },

    // rail skyscraper
    { slot_id: '538096517_160x600BTF', auid: '538096517' },

    // footer banner ad
    { slot_id: 'footer_728x90BTF', auid: '538014854' }
  );

  // CrowdIgnite 2x2 rail unit
  (function() {
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'http://widget.crowdignite.com/widgets/34389?_ci_wid=_CI_widget_34389';
    script.async = true;
    script.charset = 'utf-8';
    document.getElementsByTagName('head')[0].appendChild(script);
  })();

}

/* Mobile-only ad units */
else {
  console.info('MOBILE');
  OX_ads.push(
    // header mobile banner ad
    { slot_id: 'header_728x90ATF', auid: '538099189' },

    // footer mobile banner ad
    { slot_id: 'footer_728x90BTF', auid: '538099192' }
  );

}

/* CrowdIgnite units */
if (OU.screenContext === 'desktop' || OU.screenContext === 'tablet') {
  // Post 3x2 unit
  if (document.getElementById('post_CI_widget') !== null) {
    (function() {
      var script = document.createElement('script');
      script.type = 'text/javascript';
      script.src = 'http://widget.crowdignite.com/widgets/34397?_ci_wid=post_CI_widget';
      script.async = true;
      script.charset = 'utf-8';
      document.getElementsByTagName('head')[0].appendChild(script);
    })();
  }
}
else {
  // Post 2x2 unit
  (function() {
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'http://widget.crowdignite.com/widgets/34401?_ci_wid=post_CI_widget';
    script.async = true;
    script.charset = 'utf-8';
    document.getElementsByTagName('head')[0].appendChild(script);
  })();
}
