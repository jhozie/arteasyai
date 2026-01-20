<?php
/**
 * Plugin Name: Arteasy AI Test
 * Description: Simple test plugin for debugging
 * Version: 1.0.0
 * Author: AI Developer
 */

if (!defined("ABSPATH")) {
    exit;
}

add_action("admin_menu", "arteasy_test_menu");

function arteasy_test_menu() {
    add_menu_page("Arteasy Test", "AI Test", "manage_options", "arteasy-test", "arteasy_test_page");
}

function arteasy_test_page() {
    echo "<h1>Arteasy AI Test Plugin</h1>";
    echo "<p>This is a simple test plugin to verify WordPress installation works.</p>";
    echo "<p>If you can see this, the plugin structure is correct!</p>";
}




