/*
 * OU "namespace"
 *
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
