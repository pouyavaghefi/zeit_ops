<div id="content">
    <div class="content-wrap">
        <div class="container clearfix">
            <div class="col_full center">
                <h2 style="line-height: 1.6;"><span>EzZeit</span> <br> Making server management easy.</h2>

                <a href="/register" class="simple-button"><i class="fa fa-user"></i>Get Started</a>
{{--                <a href="#pricing-signup" data-scrollto="#pricing-signup" class="simple-button"><i class="fa fa-tasks"></i>See Demo</a>--}}
{{--                <a href="#home-portfolio-list" data-scrollto="#home-portfolio-list" class="simple-button"><i class="fa fa-briefcase"></i>See Portfolio</a>--}}
            </div>

            <div class="dotted-divider"></div>

            @include('frontend.layouts.includes.sections.features')

            @include('frontend.layouts.includes.sections.register')

            <div class="dotted-divider"></div>

            <h2 id="pricing-signup" class="center">Choose your Plan</h2>

            @include('frontend.layouts.includes.sections.plans')

            <div class="dotted-divider"></div>

{{--            <h2 id="home-portfolio-list" class="center">Something we've <span>worked</span> on</h2>--}}

{{--            @include('frontend.layouts.includes.sections.slider')--}}

{{--            <div class="dotted-divider"></div>--}}

{{--            @include('frontend.layouts.includes.sections.blogs')--}}

            <script type="text/javascript">
                $(document).ready( function() {
                    tab_widget( '#tabwidget-1' );
                });
            </script>

            <div class="clear"></div>

{{--            @include('frontend.layouts.includes.sections.gallery')--}}
        </div>
    </div>
</div>


{{--@include('frontend.layouts.includes.sections.twitter')--}}
