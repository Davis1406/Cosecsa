<?php
// Copy to config/course.php. Match these to your existing MIS layout.
return [
    'parent_layout'   => 'learning.examiner.coded-shell', // the examiner player shell (sidebar + page); it extends layout.app
    'content_section' => 'course-shell', // the @yield name for the page body in that shell
    'styles_stack'    => 'styles',      // the @stack name in <head>  (add @stack('styles') if missing)
    'scripts_stack'   => 'scripts',     // the @stack name before </body> (add @stack('scripts') if missing)
];
