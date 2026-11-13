<div id="twitter-panel">
    <div class="container clearfix">
        <div id="twitter-panel-icon">
            <i class="fa fa-twitter"></i>
        </div>
        <div id="twitter-panel-content">
            <div class="fslider" data-animation="fade" data-pause="5000" data-arrows="false">
                <div class="flexslider">
                    <div class="slider-wrap">
                        <div class="slide"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="twitter-panel-follow">
            <a href="http://twitter.com/envato">Follow Us</a>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($){
                $.getJSON('functions/twitter/tweets.php?username=envato&count=3', function(tweets){
                    $("#twitter-panel-content .slider-wrap").html(sm_format_twitter2(tweets));
                });
            });
        </script>
    </div>
</div>
