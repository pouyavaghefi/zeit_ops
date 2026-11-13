<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    @include('frontend.layouts.includes.init.head')

    <title>
    @yield('pageTitle')
    </title>
    <style>
        ul li ul li a {
            white-space: nowrap;
        }

    </style>
</head>

<body class="stretched">

<div id="wrapper" class="clearfix">
    @include('frontend.langs.de.includes.overalls.topbar')

    @include('frontend.langs.de.includes.overalls.header')

{{--    @include('frontend.layouts.includes.gadgets.slider')--}}

    @yield('pageWrapper')

{{--    @include('frontend.layouts.includes.overalls.footer')--}}
    @include('frontend.langs.de.includes.overalls.footer2')

    <div class="clear"></div>

{{--    @include('frontend.layouts.includes.sections.copyright')--}}
</div>

@include('frontend.langs.de.includes.parsers.go-to-top')

@include('frontend.langs.de.includes.init.scripts')

</body>
</html>
