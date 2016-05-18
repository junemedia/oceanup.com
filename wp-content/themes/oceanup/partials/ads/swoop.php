<!-- +SWOOP -->
<script type="text/javascript">
  (function addSwoopOnce(domain) {
    var win = window;
    try {
      while (!(win.parent == win || !win.parent.document)) {
        win = win.parent;
      }
    } catch (e) {
      /* noop */
    }
    var doc = win.document;
    if (!doc.getElementById('swoop_sdk')) {
      var serverbase = doc.location.protocol + '//ardrone.swoop.com/';
      var s = doc.createElement('script');
      s.type = "text/javascript";
      s.src = serverbase + 'js/spxw.js';
      s.id = 'swoop_sdk';
      s.setAttribute('data-domain', domain);
      s.setAttribute('data-serverbase', serverbase);
      doc.head.appendChild(s);
    }
  })('SW-10152718-10');
</script>
<!-- -SWOOP -->
