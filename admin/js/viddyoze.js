let figure = jQuery(".video").hover( hoverVideo, hideVideo );
let loadingInterval;

function hoverVideo(e) {
    jQuery('video', this).get(0).play(); 
}

function hideVideo(e) {
    jQuery('video', this).get(0).pause(); 
}
      
function checkIfImageExists(url, callback) {
    const img = new Image();
    
    img.src = url;
    
    if (img.complete) {
      callback(true);
    } else {
      img.onload = () => {
        callback(true);
      };
      img.onerror = () => {
        callback(false);
      };
    }
  }

function changeAtiveTab(event,tabID){
    let element = event.target;
    while(element.nodeName !== "A"){
      element = element.parentNode;
    }
    ulElement = element.parentNode.parentNode;
    aElements = ulElement.querySelectorAll("li > a");
    tabContents = document.getElementById("tabs-id").querySelectorAll(".tab-content > div");
    for(let i = 0 ; i < aElements.length; i++){
      aElements[i].classList.remove("text-white");
      aElements[i].classList.remove("bg-blue-600");
      aElements[i].classList.add("text-blue-600");
      aElements[i].classList.add("bg-white");
      tabContents[i].classList.add("hidden");
      tabContents[i].classList.remove("block");
    }
    element.classList.remove("text-blue-600");
    element.classList.remove("bg-white");
    element.classList.add("text-white");
    element.classList.add("bg-blue-600");
    document.getElementById(tabID).classList.remove("hidden");
    document.getElementById(tabID).classList.add("block");
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        var addables = input.parentElement.querySelectorAll('.addable');
        var editables = input.parentElement.querySelectorAll('.editable');
        var images_data = input.parentElement.querySelectorAll('.datavalue');
        reader.onload = function (e) {
            const isImage = input.files[0].type.match("image.*");
            if (isImage) {   
                for (var i = 0; i < addables.length; i++) {
                    addables[i].classList.add('hidden');
                }
                for (var i = 0; i < editables.length; i++) {
                    editables[i].classList.remove('hidden');
                }
                for (var i = 0; i < editables.length; i++) {
                    editables[i].classList.remove('hidden');
                    images_data[i].value = e.target.result;
                    images_data[i].style.border = "red 1px solid";
                }
                input.parentElement.style.backgroundImage = "url('"+ e.target.result +"')";
            }
            else {
                input.value = "";
            }
        };

        reader.readAsDataURL(input.files[0]);
        var has_empty_files = jQuery('.datavalue').filter(function(){
            return jQuery.trim(this.value) == ''
        }).length - 1  > 0 ;
        var viddyozeSubmitBtn = document.getElementById("viddyoze_submit_btn");
        var viddyozePreviewBtn = document.getElementById("viddyoze_preview_btn");
        if (has_empty_files) {
            viddyozePreviewBtn.classList.remove('bg-blue-600');
            viddyozePreviewBtn.classList.add('bg-blue-200');
            viddyozePreviewBtn.disabled = true;
            viddyozeSubmitBtn.classList.remove('bg-blue-600');
            viddyozeSubmitBtn.classList.add('bg-blue-200');
            viddyozeSubmitBtn.disabled = true;
        }
        else {
            viddyozePreviewBtn.classList.remove('bg-blue-200');
            viddyozePreviewBtn.classList.add('bg-blue-600');
            viddyozePreviewBtn.disabled = false; 
            viddyozeSubmitBtn.classList.remove('bg-blue-200');
            viddyozeSubmitBtn.classList.add('bg-blue-600');
            viddyozeSubmitBtn.disabled = false; 
        }
    }
}
function toggleFont() {
    // Get the checkbox
    var checkBox = document.getElementById("toogle_fonts");

  
    // If the checkbox is checked, display the output text
    if (checkBox.checked == true){
        jQuery('.font-things').show();
    } else {
        jQuery('.font-things').hide();
    }
}

/**
 * Create and show a dismissible admin notice
 */
function myAdminNotice( msg ) {

    /* create notice div */

    var div = document.createElement( 'div' );
    div.classList.add( 'notice', 'notice-info', 'is-dismissible' );

    /* create paragraph element to hold message */

    var p = document.createElement( 'p' );

    /* Add message text */

    p.appendChild( document.createTextNode( msg ) );

    // Optionally add a link here

    /* Add the whole message to notice div */

    div.appendChild( p );

    /* Create Dismiss icon */

    var b = document.createElement( 'button' );
    b.setAttribute( 'type', 'button' );
    b.classList.add( 'notice-dismiss' );

    /* Add screen reader text to Dismiss icon */

    var bSpan = document.createElement( 'span' );
    bSpan.classList.add( 'screen-reader-text' );
    bSpan.appendChild( document.createTextNode( 'Dismiss this notice' ) );
    b.appendChild( bSpan );

    /* Add Dismiss icon to notice */

    div.appendChild( b );

    /* Insert notice after the first h1 */

    var h1 = document.getElementsByTagName( 'h1' )[0];
    h1.parentNode.insertBefore( div, h1.nextSibling);


    /* Make the notice dismissable when the Dismiss icon is clicked */

    b.addEventListener( 'click', function () {
        div.parentNode.removeChild( div );
    });
}

function checkPreviewImage() {
    let imageCount = jQuery('.slider-nav .slide img').length;
    let previewCount = jQuery(".slider-nav .slide img[data-preview-img]").length;

    if (imageCount === previewCount) {
        clearInterval(loadingInterval);
    }
}

(function( $ ) {
    $(document).ready( function() {
        if (jQuery('.datavalue').length) {

            var has_empty_files = jQuery('.datavalue').filter(function(){
                return jQuery.trim(this.value) == ''
            }).length  > 0 ;
            var viddyozeSubmitBtn = document.getElementById("viddyoze_submit_btn");
            var viddyozePreviewBtn = document.getElementById("viddyoze_preview_btn");
            if (has_empty_files) {
                viddyozePreviewBtn.classList.remove('bg-blue-600');
                viddyozePreviewBtn.classList.add('bg-blue-200');
                viddyozePreviewBtn.disabled = true;
                viddyozeSubmitBtn.classList.remove('bg-blue-600');
                viddyozeSubmitBtn.classList.add('bg-blue-200');
                viddyozeSubmitBtn.disabled = true;
            }
            else {
                viddyozePreviewBtn.classList.remove('bg-blue-200');
                viddyozePreviewBtn.classList.add('bg-blue-600');
                viddyozePreviewBtn.disabled = false; 
                viddyozeSubmitBtn.classList.remove('bg-blue-200');
                viddyozeSubmitBtn.classList.add('bg-blue-600');
                viddyozeSubmitBtn.disabled = false; 
            }
        }
        jQuery("#viddyoze_submit_btn").on("click", function(e){
            if(!$(this).is(':disabled')){
                jQuery(this).html("Your video is being created…");
                var form = $('#viddyoze_form');
                $.ajax({
                    type: "POST",
                    url : myAjax.ajaxurl,
                    data: {action: "get_render", data : form.serialize()}, // serializes the form's elements.
                    success: function(response) {
                        var responseData = $.parseJSON(response);
                        if (responseData.error == 0) {
                            window.location.replace(responseData.url);
                        }
                        else {
                            myAdminNotice(responseData.msg);
                        }
                    }
                });

            }
        });
        jQuery("#viddyoze_preview_btn").on("click", function(e){
            if(!jQuery(this).is(':disabled')){
                jQuery(this).html("Your preview is in progress…");
                if (!jQuery('.viddyoze-player').hasClass('hidden')) {
                    jQuery('.slider-for').slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: false,
                        fade: true,
                        asNavFor: '.slider-nav'
                    });
                    jQuery('.slider-nav').slick({
                        slidesToShow: 4,
                        slidesToScroll: 1,
                        asNavFor: '.slider-for',
                        dots: false,
                        centerMode: true,
                        focusOnSelect: true
                    });
                }
                jQuery('.viddyoze-player').addClass('hidden');
                jQuery('.viddyoze-preview').removeClass('hidden');

                let loadingImage = jQuery('.slider-nav').data('loading-image');
                jQuery('.slider-for .slide').each(function(imgIndex) {
                    jQuery(this).html('<img src="'+loadingImage+'">');
                });
                jQuery('.slider-nav .slide').each(function(imgIndex) {
                    jQuery(this).html('<img src="'+loadingImage+'">');
                });
            }
        });
        jQuery('#viddyoze_font_type').on('change', function () {
            var fontId = this.value;
            jQuery('.viddyoze-font-group')
              .removeClass('viddyoze-font-selected')
              .addClass('viddyoze-font-unselected');
            jQuery('.viddyoze-font-'+fontId)
              .removeClass('viddyoze-font-unselected')
              .addClass('viddyoze-font-selected');
            var label = jQuery('.viddyoze-font-'+fontId).prop('label');
            jQuery('.viddyoze-font-family').html(label);
            jQuery('.viddyoze-font-'+fontId+' option:first').attr("selected", "selected");
        });
        jQuery('.viddyoze_font_family').on('change', function () {
            var label = jQuery(this.options[this.selectedIndex]).closest('optgroup').prop('label');
            jQuery(this).prev('label').find('.viddyoze-font-family').html(label);
        });
        jQuery('#viddyoze_form').submit(function(e){
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize() + '&toogle_fonts=' + (jQuery('#toogle_fonts:checkbox:checked').length === 1);

            jQuery.ajax({
                type: "POST",
                url : myAjax.ajaxurl,
                data: {action: "get_preview", data : formData}, // serializes the form's elements.
                success: function(response) {
                    var responseData = $.parseJSON(response)
                    $("#templateCustomisationId").val(responseData.templateCustomisationId);
                    loadingInterval = setInterval(function() {
                        checkPreviewImage();
                        checkIfImageExists(responseData.frameUrls[0], (exists) => {
                            if (exists) {
                                $(".slide1").html('<img data-preview-img="1" src="'+responseData.frameUrls[0]+'">');
                            }
                        });
                        checkIfImageExists(responseData.frameUrls[1], (exists) => {
                            if (exists) {
                                $(".slide2").html('<img data-preview-img="1" src="'+responseData.frameUrls[1]+'">');
                            }
                        });
                        checkIfImageExists(responseData.frameUrls[2], (exists) => {
                            if (exists) {
                                $(".slide3").html('<img data-preview-img="1" src="'+responseData.frameUrls[2]+'">');
                            }
                        });
                        checkIfImageExists(responseData.frameUrls[3], (exists) => {
                            if (exists) {
                                $(".slide4").html('<img data-preview-img="1" src="'+responseData.frameUrls[3]+'">');
                            }
                        });
                    }, 10000);
                    $("#viddyoze_preview_btn").addClass("hidden");
                    $("#viddyoze_submit_btn").removeClass("hidden");
                }
            });

            $('#viddyoze_submit_btn', this).attr('disabled', 'disabled');
        });

        jQuery('#viddyoze_form').change(function () {
            handleFormChange();
        });
 
        // Add Color Picker to all inputs that have 'color-field' class
        $(function() {
            let options = {
                defaultColor: false,
                change: function(event, ui){
                    handleFormChange();
                },
                clear: function() {
                    handleFormChange();
                },
                hide: true,
                palettes: true
            };
            $('.color-field').wpColorPicker(options);
        });

        function handleFormChange() {
            if (jQuery('.viddyoze-player').hasClass('hidden')) {
                jQuery("#viddyoze_preview_btn").html('Preview');
                jQuery("#viddyoze_preview_btn").removeClass("hidden");
                jQuery("#viddyoze_submit_btn").addClass("hidden");
            }
        }

         // initalise the dialog
         $('#my-dialog2').dialog({
            title: 'Embed Video',
            dialogClass: 'wp-dialog',
            autoOpen: false,
            draggable: false,
            width: 'auto',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            position: {
            my: "center",
            at: "center",
            of: window
            },
            open: function () {
            // close dialog by clicking the overlay behind it
            $('.ui-widget-overlay, .close-ui-modal').bind('click', function(){
                $('#my-dialog2').dialog('close');
            })
            },
            create: function () {
            // style fix for WordPress admin
            $('.ui-dialog-titlebar-close').addClass('ui-button');
            },
        });
        // initalise the dialog
        $('#my-dialog').dialog({
            title: 'My Dialog',
            dialogClass: 'wp-dialog',
            autoOpen: false,
            draggable: false,
            width: 'auto',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            position: {
            my: "center",
            at: "center",
            of: window
            },
            open: function () {
            // close dialog by clicking the overlay behind it
            $('.ui-widget-overlay, .close-ui-modal').bind('click', function(){
                $('#my-dialog').dialog('close');
            })
            },
            create: function () {
            // style fix for WordPress admin
            $('.ui-dialog-titlebar-close').addClass('ui-button');
            },
        });

        // bind a button or a link to open the dialog
        $(".card-content").on("click","a.open-my-dialog.delete-render", function(e){
            e.preventDefault();
            $('#viddyoze_renderid').val($(this).data('id'));
            $('#my-dialog').dialog('open');
        });

        // bind a button or a link to open the dialog
        $(".card-content").on("click","a.open-my-dialog2.copy-embed", function(e){
            e.preventDefault();
            $('.viddyoze_embed').html($(this).data('embed'));
            $('#my-dialog2').dialog('open');
        });
        $("#viddyoze_embed_autoplay_toggle").change(function() {
            if (this.checked) {
                $("#viddyoze_embed_autoplay").show();
            } else {
                $("#viddyoze_embed_autoplay").hide();
            }
        });
        const interval = setInterval(function() {      
            $(".should-get-percentage").each(function() {
                var wrapper = $(this);
                var id= $(this).data('id');
                $.ajax({
                    type : "post",
                    dataType : "json",
                    url : myAjax.ajaxurl,
                    data : {action: "get_percentage", id : id},
                    success: function(response) {
                        if(response.type == "rendering") {
                            wrapper.html(response.content);
                        }                    
                        else if(response.type == "queuing") {
                            wrapper.html(response.content);
                        }
                        else if(response.type == "finished") {
                            wrapper.removeClass("should-get-percentage").html(response.content);
                        }
                        else {
                            console.log(response);
                        }
                    }
                })
            });        
        }, 10000);
    });
    new ClipboardJS('.copy-btn');
     
})( jQuery );