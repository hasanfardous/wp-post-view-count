(function ($) {
    // Loads while the document is ready
    $(document).ready(function () {
        // Copy shortcode to clipboard
        let wpvcCopyButton = $('td.post_views_shortcode.column-post_views_shortcode span.wpvc-copy-button');
        $(wpvcCopyButton).on('click', function (e) {
            e.preventDefault();
            let wpvcShortcode = $(this).prev('span.wpvc-shortcode');
            let tempTextarea = $('<textarea>');
            $('body').append(tempTextarea);
            tempTextarea.val(wpvcShortcode.text()).select();
            document.execCommand('copy');
            tempTextarea.remove();
            $(this).text('Copied!');
        });
    });
}(jQuery));