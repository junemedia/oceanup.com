/* Global ad units */
// I got nothing at this time...

/* Desktop-only ad units */
if (OU.screenContext === 'desktop') {
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
  // I got nothing at this time...
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
