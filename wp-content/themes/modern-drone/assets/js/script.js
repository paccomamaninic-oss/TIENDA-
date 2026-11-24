jQuery(document).ready(function($) {
  var scroll = $(window).scrollTop();
  var scrollup = $('.scroll-top');
  /*------------------------------------------------
            Scroll Top
  ------------------------------------------------*/
  scrollup.click(function () {
    $('html, body').animate({
      scrollTop: '0px'
    }, 800);
    return false;
  });
  $(window).scroll(function () {
    var scroll = $(window).scrollTop();
    if (scroll >= 200) {
      scrollup.fadeIn();
    } else {
      scrollup.fadeOut();
    }
  });
});

jQuery(document).ready(function() {
  jQuery('.testimonial-section .owl-carousel').owlCarousel({
    loop: true,
    margin: 20,
    nav: true,
    navText: ["<i class='fa-solid fa-angles-left'></i>", "<i class='fa-solid fa-angles-right'></i>"], 
    dots: true,
    rtl: false,
    responsive: {
      0: { 
        items: 1 
      },
      600: { 
        items: 2 
      },
      992: { 
        items: 3 
      },
      1200: { 
        items: 4 
      }
    },
    autoplay: true,
  });
});

jQuery(document).ready(function() {
  jQuery('.latest-news .owl-carousel').owlCarousel({
    loop: true,
    margin: 40,
    nav: true,
    navText: ["<i class='fa-solid fa-angles-left'></i>", "<i class='fa-solid fa-angles-right'></i>"], 
    dots: true,
    rtl: false,
    responsive: {
    0: { 
      items: 1 
    },
    600: { 
      items: 2 
    },
    992: { 
      items: 3 
    },
    1200: { 
      items: 3
    }
  },
    autoplay: true,
  });
});

jQuery(document).ready(function () {
  var modern_drone_owl = jQuery('.product-main-list.owl-carousel');

  modern_drone_owl.owlCarousel({
    loop: true,
    margin: 20,
    nav: true,
    navText: ["<i class='fa-solid fa-arrow-left'></i>", "<i class='fa-solid fa-arrow-right'></i>"],
    dots: true,
    rtl: false,
    responsive: {
      0: { items: 1 },
      600: { items: 2 },
      992: { items: 3 },
      1024: { items: 4 },
      1440: { items: 7 }
    },
    autoplay: true,
    onInitialized: function () {
      var modern_drone_controls = jQuery('<div class="owl-controls"></div>');
      modern_drone_owl.find('.owl-nav').appendTo(modern_drone_controls);
      modern_drone_owl.find('.owl-dots').appendTo(modern_drone_controls);
      modern_drone_owl.append(modern_drone_controls);
    }
  });
});

jQuery(document).ready(function ($) {
    var slider = $('.slider-section.owl-carousel');
    slider.owlCarousel({
        loop: true,
        items: 1,
        nav: false,
        margin: 30,
        dots: true,
        autoplay: true,
        autoplayTimeout: 4000,
    });

    $('.custom-next').click(function () {
        slider.trigger('next.owl.carousel');
    });

    $('.custom-prev').click(function () {
        slider.trigger('prev.owl.carousel');
    });
});