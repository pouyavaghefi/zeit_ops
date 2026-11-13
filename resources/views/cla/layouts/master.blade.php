<!DOCTYPE html>
<html lang="en">
@include('cla.layouts.head')
<body>
<div class="form-body">
    <div class="website-logo">
        <a href="{{ env('APP_URL') }}">
            <div class="logo">
                <img class="logo-size" src="/cla/images/logo-svg.png" alt="">
            </div>
        </a>
    </div>
    <div class="iofrm-layout">
        <div class="img-holder">
            <div class="bg"></div>
            <div class="info-holder">

            </div>
        </div>
        <div class="form-holder">
            <div class="form-content">
                <div class="form-items">
                    <h3>@yield('page-title')</h3>
                    <p>@yield('page-para')</p>

                    <!-- Display Success Message -->
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Display Error Messages -->
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any()) <div class="alert alert-danger"> <ul> @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul> </div> @endif

                    <div class="page-links">
                        @yield('class-active')
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
@include('cla.layouts.scripts')
</body>
</html>
