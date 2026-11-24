(function () {
  'use strict';
  function placeRelatedPlugins() {
    var panel = document.querySelector('.reycob-related-plugins');
    var content = document.querySelector('#wpbody-content > .wrap') || document.getElementById('wpbody-content');
    if (panel && content) {
      content.appendChild(panel);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', placeRelatedPlugins);
  } else {
    placeRelatedPlugins();
  }
})();
