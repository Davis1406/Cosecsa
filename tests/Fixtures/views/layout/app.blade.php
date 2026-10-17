{{-- Minimal stand-in for layout.app so a page's own markup can be rendered in tests without the sidebar's queries. --}}
@yield('content')
@stack('scripts')
