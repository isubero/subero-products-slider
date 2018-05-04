function initializeSuberoProductsSlider( element_id ) {

    jQuery('.' + element_id).slick({
        accessibility: true,
        arrows: true,
        dots: true,
        infinite: true,
        autoPlay: true,
        speed: 300,
        slidesToShow: 3,
        slidesToScroll: 3,
        responsive: [
            {
            breakpoint: 1024,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
                infinite: true,
                dots: true
            }
            },
            {
            breakpoint: 600,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
            },
            {
            breakpoint: 480,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
            }
            }
        ]
    });
}

jQuery(document).ready(function() {
    
    const sliders = document.querySelectorAll('.sb-products-slider-wrapper');

    sliders.forEach(slider => {

        var data = {
            'action'        : 'get_slider_content_ajax_handler',
            'product_ids'   : slider.getAttribute('products'),
			'category'      : slider.getAttribute('category'),
			'on_sale'       : slider.getAttribute('on-sale'),
            'limit'         : slider.getAttribute('limit')
        }

        jQuery.post(sps_ajax_object.ajax_url, data, function(response) {
            
            slider.innerHTML = response;
            
            // Parse response string into an html node.
            let parser = new DOMParser();
            let document = parser.parseFromString(response, "text/html");
            let htmlElement = document.querySelector('.subero-products-slider');
            
            initializeSuberoProductsSlider( htmlElement.getAttribute('slider-id') );

        });
    
    });
    
});