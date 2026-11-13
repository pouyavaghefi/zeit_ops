<div style="position: relative;">
    <h4>Our <span>Clients</span></h4>
    <ul id="clients-scroller" class="our-clients clearfix">
        <li><a href="http://www.logospire.com/logos/3122"><img src="images/clients/3122.jpg" alt="Client 1" title="Client 1" /></a></li>
        <li><a href="http://www.logospire.com/logos/4044"><img src="images/clients/4044.png" alt="Client 2" title="Client 2" /></a></li>
        <li><a href="http://www.logospire.com/logos/4122"><img src="images/clients/4112.jpg" alt="Client 3" title="Client 3" /></a></li>
        <li><a href="http://www.logospire.com/logos/4147"><img src="images/clients/4147.jpg" alt="Client 4" title="Client 4" /></a></li>
        <li><a href="http://www.logospire.com/logos/4487"><img src="images/clients/4487.png" alt="Client 5" title="Client 5" /></a></li>
        <li><a href="http://www.logospire.com/logos/4999"><img src="images/clients/4999.jpg" alt="Client 6" title="Client 6" /></a></li>
        <li><a href="http://www.logospire.com/logos/5055"><img src="images/clients/5055.jpg" alt="Client 7" title="Client 7" /></a></li>
        <li><a href="http://www.logospire.com/logos/5393"><img src="images/clients/5393.jpg" alt="Client 8" title="Client 8" /></a></li>
        <li><a href="http://www.logospire.com/logos/5909"><img src="images/clients/5909.png" alt="Client 7" title="Client 7" /></a></li>
        <li><a href="http://www.logospire.com/logos/6002"><img src="images/clients/6002.png" alt="Client 8" title="Client 8" /></a></li>
    </ul>

    <div class="widget-scroll-prev" id="ourclients_prev"></div>
    <div class="widget-scroll-next" id="ourclients_next"></div>

    <script type="text/javascript">

        jQuery(document).ready(function($) {

            var clientsCarousel = $("#clients-scroller");

            clientsCarousel.carouFredSel({
                width : "100%",
                height : "auto",
                circular : false,
                responsive : true,
                infinite : false,
                auto : false,
                items : {
                    width : 160,
                    visible: {
                        min: 1,
                        max: 6
                    }
                },
                scroll : {
                    wipe : true
                },
                prev : {
                    button : "#ourclients_prev",
                    key : "left"
                },
                next : {
                    button : "#ourclients_next",
                    key : "right"
                },
                onCreate : function () {
                    $(window).on('resize', function(){
                        clientsCarousel.parent().add(clientsCarousel).css('height', clientsCarousel.children().first().outerHeight() + 'px');
                    }).trigger('resize');
                }
            });

        });
    </script>
</div>
