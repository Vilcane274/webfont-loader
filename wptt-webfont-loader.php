<?php
/*
Plugin Name: Web Font Loader
Description: A plugin to load web fonts using the Web Font Loader library
Version: 1.0
Author: Your Name
*/

function wptt_webfont_loader() {
    $webfont_url = 'https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
    ?>
    <script type="text/javascript">
        WebFontConfig = {
            google: {
                families: ['Open+Sans', 'Roboto', 'Lato']
            }
        };
        (function() {
            var wf = document.createElement('script');
            wf.src = '<?php echo $webfont_url; ?>';
            wf.type = 'text/javascript';
            wf.async = 'true';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(wf, s);
        })();
    </script>
    <?php
}

add_action('wp_head', 'wptt_webfont_loader');

function wptt_custom_fonts_enqueue() {
    wp_enqueue_style('web-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto&family=Lato&display=swap', false);
}

add_action('wp_enqueue_scripts', 'wptt_custom_fonts_enqueue');
?>

