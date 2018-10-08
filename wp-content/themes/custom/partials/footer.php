        </main>

        <?php get_template_part('partials/footer_site' ); ?>

        <?php //get_template_part('partials/modals' ); ?>

        <?php //get_template_part('partials/viewport_label' ); ?>

        <script async defer src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyDTkjwJK80N7YCWoKjhKz8c3J1tNEbJpRg"></script>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-122335332-1"></script>
        <script>
        	window.dataLayer = window.dataLayer || [];
        	function gtag(){dataLayer.push(arguments);}
        	gtag('js', new Date());
        	gtag('config', 'UA-122335332-1');
        </script>

        <?php wp_footer(); ?>

    </body>
</html>
