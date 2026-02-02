(function() {
  'use strict';
  
  // Get settings from WordPress
  var selector = (typeof sectionBlurSettings !== 'undefined' && sectionBlurSettings.selector) 
    ? sectionBlurSettings.selector 
    : '.fusion-fullwidth > div';
  
  var maxBlur = (typeof sectionBlurSettings !== 'undefined' && sectionBlurSettings.maxBlur) 
    ? parseFloat(sectionBlurSettings.maxBlur) 
    : 20;
    
  var thresholdTopRatio = (typeof sectionBlurSettings !== 'undefined' && sectionBlurSettings.thresholdTop) 
    ? parseFloat(sectionBlurSettings.thresholdTop) 
    : 0.40;
    
  var thresholdBottomRatio = (typeof sectionBlurSettings !== 'undefined' && sectionBlurSettings.thresholdBottom) 
    ? parseFloat(sectionBlurSettings.thresholdBottom) 
    : 0.33;
  
  function updateFusionBlur() {
    var thresholdTop = window.innerHeight * thresholdTopRatio;
    var thresholdBottom = window.innerHeight * thresholdBottomRatio;
    var winHeight = window.innerHeight;
    
    var elements = document.querySelectorAll(selector);
    if (elements.length === 0) return;
    
    elements.forEach(function(fullwidth) {
      // Exclude elements with '-mesh' in their class name
      if ([...fullwidth.classList].some(cls => cls.includes('-mesh'))) return;
      var rect = fullwidth.getBoundingClientRect();
      var blur = 0;
      
      // Blur if bottom is less than thresholdBottom from top (leaving at top)
      if (rect.bottom > 0 && rect.bottom < thresholdBottom) {
        var distance = thresholdBottom - rect.bottom;
        var ratio = Math.min(1, distance / thresholdBottom);
        blur = Math.pow(ratio, 2) * maxBlur;
      }
      // Blur if top is more than (window height - thresholdTop) from top (leaving at bottom)
      else if (rect.top < winHeight && rect.top > winHeight - thresholdTop) {
        var distance = rect.top - (winHeight - thresholdTop);
        var ratio = Math.min(1, distance / thresholdTop);
        blur = Math.pow(ratio, 2) * maxBlur;
      }
      // Fully outside viewport: max blur
      else if (rect.bottom <= 0 || rect.top >= winHeight) {
        blur = maxBlur;
      }
      // Inside viewport: no blur
      else {
        blur = 0;
      }
      
      fullwidth.style.filter = blur > 0 ? 'blur(' + blur.toFixed(2) + 'px)' : 'none';
    });
  }
  
  // Run on DOMContentLoaded and then on scroll/resize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      updateFusionBlur();
      window.addEventListener('scroll', updateFusionBlur);
      window.addEventListener('resize', updateFusionBlur);
    });
  } else {
    // DOM already loaded
    updateFusionBlur();
    window.addEventListener('scroll', updateFusionBlur);
    window.addEventListener('resize', updateFusionBlur);
  }
})();
