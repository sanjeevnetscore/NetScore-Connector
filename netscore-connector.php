<?php
/*
Plugin Name: NetScore Connector
Description: A WordPress plugin that adds a form with name, email, and comments fields in the admin dashboard and sends an email to the admin on submission.
Version: 1.4
Author: yourname
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Activation hook
function cuf_activate_plugin() {
    flush_rewrite_rules(); 
}
register_activation_hook(__FILE__, 'cuf_activate_plugin');

// Deactivation hook
function cuf_deactivate_plugin() {
    flush_rewrite_rules(); 
}
register_deactivation_hook(__FILE__, 'cuf_deactivate_plugin');

// Add menu item to the admin dashboard
function cuf_add_admin_menu() {
    add_menu_page(
        'NetScore Connector', 
        'NetScore Connector',        
        'manage_options',   
        'NetScore Connector', 
        'cuf_display_form_page', 
        'dashicons-admin-users', 
        80 
    );
}
add_action('admin_menu', 'cuf_add_admin_menu');

// Display the form page and handle submission
function cuf_display_form_page() {
    ?>
    <div class="wrap-cuf">
        <div class="cuf-form-container">
            <div> 
                <img src="https://www.netscoretech.com/wp-content/uploads/2024/08/netscore-logo-new.png.webp" 
                     alt="Netscore Logo" 
                     style="max-width:200px;display:block;margin-left:auto;margin-right:auto;margin-bottom:20px;" />
                <form method="post" action="">
                    <?php wp_nonce_field('cuf_form_action', 'cuf_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="cuf_name">Name</label></th>
                            <td><input type="text" name="cuf_name" id="cuf_name" required></td>
                        </tr>
                        <tr>
                            <th><label for="cuf_email">Email</label></th>
                            <td><input type="email" name="cuf_email" id="cuf_email" required></td>
                        </tr>
                        <tr>
                            <th><label for="cuf_comments">Comments</label></th>
                            <td><textarea name="cuf_comments" id="cuf_comments" rows="5" cols="40" placeholder="Enter your comments here..."></textarea></td>
                        </tr>
                    </table>
                    <?php submit_button('Submit'); ?>
                </form>
            </div>
        </div>
    </div>
    <?php
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cuf_nonce']) && wp_verify_nonce($_POST['cuf_nonce'], 'cuf_form_action')) {
        $name     = sanitize_text_field($_POST['cuf_name']);
        $email    = sanitize_email($_POST['cuf_email']);
        $comments = sanitize_textarea_field($_POST['cuf_comments']);

        // Prepare email content
        $admin_email = get_option('admin_email');
        $subject = 'New User Form Submission';
        $message = "A new form submission has been received:\n\n";
        $message .= "Name: " . esc_html($name) . "\n";
        $message .= "Email: " . esc_html($email) . "\n";
        $message .= "Comments: " . (!empty($comments) ? esc_html($comments) : "No comments provided") . "\n";
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        // Send email to admin
        $email_sent = wp_mail($admin_email, $subject, $message, $headers);

        // Display feedback
        if ($email_sent) {
            echo '<div class="updated"><p>Form submitted successfully! Admin has been notified.</p></div>';
        } else {
            echo '<div class="error"><p>Form submitted, but there was an error sending the email to the admin.</p></div>';
        }
    }
}

// Enqueue basic styles for the form
function cuf_enqueue_styles() {
    wp_enqueue_style('cuf-styles', plugin_dir_url(__FILE__) . 'css/cuf-styles.css');
}
add_action('admin_enqueue_scripts', 'cuf_enqueue_styles');
?>