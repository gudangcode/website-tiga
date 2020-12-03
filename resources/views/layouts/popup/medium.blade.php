<div class="popup-container medium">
    <div class="popup-header">
        <i class="lnr lnr-arrow-left back"></i>
        <h2 class="title">@yield('title')</h2>
        <i class="lnr lnr-cross close"></i>
    </div>
    <div class="popup-body scrollbar-inner">
        <div class="popup-content">
            <!-- display flash message -->
            @include('common.errors')

            <!-- main inner content -->
            @yield('content')
        </div>
    </div>
</div>
