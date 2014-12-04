jQuery(document).ready(function ($) {

    $('#ppp-tweets-link-to-post, #ppp-tweets-cancel-ext').click( function( e ) {
        $('#ppp-tweets-post-link').toggle();
        $('#ppp-tweets-ext-link').toggle();
        $('#ppp-tweets-ext-notice').toggle();
    });

    $('#ppp-tweets-ext-link').click( function() {
        $('select#ppp_tweets_link').val('0').trigger('chosen:updated');
    });

    // Setup Chosen menus
    $('.ppp-tweets-select-chosen').chosen({
        inherit_select_classes: true,
        placeholder_text_single: 'Select A Post',
        placeholder_text_multiple: 'Select Posts',
    });

    // Add placeholders for Chosen input fields
    $( '.chosen-choices' ).on( 'click', function () {
        $(this).children('li').children('input').attr( 'placeholder', 'Type to search' );
    });

    // Variables for setting up the typing timer
    var typingTimer;               // Timer identifier
    var doneTypingInterval = 342;  // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms

    // Replace options with search results
    $('.ppp-tweets-select.chosen-container .chosen-search input, .ppp-tweets-select.chosen-container .search-field input').keyup(function(e) {

        var val = $(this).val(), container = $(this).closest( '.ppp-tweets-select-chosen' );
        var menu_id = container.attr('id').replace( '_chosen', '' );
        var lastKey = e.which;

        // Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys)
        if(
            val.length <= 3 ||
            (
                e.which == 16 ||
                e.which == 13 ||
                e.which == 91 ||
                e.which == 17 ||
                e.which == 37 ||
                e.which == 38 ||
                e.which == 39 ||
                e.which == 40
            )
        ) {
            return;
        }

        var data = {}

        clearTimeout(typingTimer);
        typingTimer = setTimeout(
            function(){
                $.ajax({
                    type: 'GET',
                    url: ajaxurl,
                    data: {
                        action: 'ppp_tweets_post_search',
                        s: val,
                    },
                    dataType: "json",
                    beforeSend: function(){
                        $('ul.chosen-results').empty();
                    },
                    success: function( data ) {

                        // Remove all options but those that are selected
                        $('#' + menu_id + ' option:not(:selected)').remove();
                        $.each( data, function( key, item ) {
                            // Add any option that doesn't already exist
                            if( ! $('#' + menu_id + ' option[value="' + item.id + '"]').length ) {
                                $('#' + menu_id).prepend( '<option value="' + item.id + '">' + item.name + '</option>' );
                            }
                        });
                         // Update the options
                        $('.ppp-tweets-select-chosen').trigger('chosen:updated');
                        $('#' + menu_id).next().find('input').val(val);
                    }
                }).fail(function (response) {
                    if ( window.console && window.console.log ) {
                        console.log( data );
                    }
                }).done(function (response) {

                });
            },
            doneTypingInterval
        );
    });

    // This fixes the Chosen box being 0px wide when the thickbox is opened
    $( '#ppp-tweets-cancel-ext' ).click( function() {
        $( '.ppp-tweets-select-chosen' ).css( 'width', 'auto' );
    });

});
