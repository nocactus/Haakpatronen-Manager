// haakpatroon-ajax.js

jQuery(document).ready(function($) {
    const grid = $('.haakpatroon-grid');
    const chevronRight = $('<div class="chevron-right">&#8250;</div>');
    grid.after(chevronRight);

    function updateChevronVisibility() {
        const scrollWidth = grid[0].scrollWidth;
        const clientWidth = grid[0].clientWidth;
        const scrollLeft = grid[0].scrollLeft;

        if (scrollWidth > clientWidth && scrollLeft + clientWidth < scrollWidth) {
            chevronRight.removeClass('hidden');
        } else {
            chevronRight.addClass('hidden');
        }
    }

    chevronRight.on('click', function() {
        const scrollAmount = 280; // Adjust to match the card width + gap
        grid.animate({ scrollLeft: grid[0].scrollLeft + scrollAmount }, 300);
    });

    grid.on('scroll', updateChevronVisibility);
    $(window).on('resize', updateChevronVisibility);

    // Initial visibility check
    updateChevronVisibility();

    $(document).on('click', '#haakpatroon-load-more', function() {
        const button = $(this);
        let paged = button.data('paged');
        const maxPages = button.data('max-pages');

        // Increment the paged value for the next set of items
        paged++;
        button.data('paged', paged);

        // Make AJAX request to load more haakpatronen
        $.ajax({
            url: haakpatroon_ajax_params.ajax_url,
            type: 'POST',
            dataType: 'html', // Ensure we are expecting HTML as the response
            data: {
                action: 'haakpatroon_load_more',
                paged: paged
            },
            beforeSend: function() {
                button.prop('disabled', true).text('Laden...'); // Change button text to indicate loading and disable button
            },
            success: function(response) {
                if ($.trim(response)) {
                    grid.append(response);
                    button.prop('disabled', false).text('Bekijk Meer');

                    // Remove button if we've reached the last page
                    if (paged >= maxPages) {
                        button.remove();
                    }

                    // Update chevron visibility after new items are added
                    updateChevronVisibility();
                } else {
                    button.remove();
                }
            },
            error: function() {
                button.prop('disabled', false).text('Fout bij laden'); // Indicate an error and re-enable button
            }
        });
    });
});
