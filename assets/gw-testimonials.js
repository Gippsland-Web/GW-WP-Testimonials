(function($){
var options = { 
    url: gwTestimonialOptions.ajaxUrl,  // this is part of the JS object you pass in from wp_localize_scripts.
    type: 'post',        // 'get' or 'post', override for form's 'method' attribute 
    dataType: 'json',
    success : function(responseText, statusText, xhr, $form) {
        $('#gw-testimonial-entry').html('Your form has been submitted successfully');
    },
// use beforeSubmit to add your nonce to the form data before submitting.
 beforeSubmit : function(arr, $form, options){
    arr.push( { "name" : "nonce", "value" : gwTestimonialOptions.nonce });
    },
}; 
    // you should probably use an id more unique than "form"
$('#gw-testimonial-entry').ajaxForm(options); 




})(jQuery);


jQuery(document).ready(function(){  
    var sudoSlider = jQuery("#testimonial-slider").sudoSlider({ 
        effect: gwTestimonialOptions.sliderEffect,
        pause: gwTestimonialOptions.sliderPause,
        auto:gwTestimonialOptions.sliderAuto,
        prevNext:gwTestimonialOptions.sliderPrevNext
    });
});