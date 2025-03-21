<?php
/*
Plugin Name: My Google Login Plugin
Plugin URI: https://www.promely.com/
Description: Handles Google login functionality.
Version: 1.0
Author: Abid
Author URI: https://www.promely.com
License: GPL2
*/

require_once __DIR__ . '/vendor/autoload.php'; // Correct placement

// Register a REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('my-plugin/v1', '/google-login', array(
        'methods' => 'POST',
        'callback' => 'handle_google_login',
    ));
});

// Handle the Google login request
function handle_google_login($request)
{
    $token = $request->get_param('token');

    // Verify the token with Google's API
    $client = new Google_Client(['client_id' => '131627924349-a61c9lm29m2f8jh5mb0e4q2rllo68hq6.apps.googleusercontent.com']); // Replace with your Client ID
    try {
        $payload = $client->verifyIdToken($token);
    } catch (Exception $e) {
        error_log('Google token verification failed: ' . $e->getMessage());
        return new WP_REST_Response(array('success' => false, 'message' => 'Invalid token'), 401);
    }

    if ($payload) {
        $userid = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'];

        // Check if the user exists in your database
        $user = get_user_by('email', $email);

        if ($user) {
            // Log in the existing user
            wp_set_current_user($user->ID, $user->user_login);
            if (!is_user_logged_in()) {
                error_log('Error setting current user');
                return new WP_REST_Response(array('success' => false, 'message' => 'Error setting current user'), 500);
            }
            wp_set_auth_cookie($user->ID);
            error_log(!is_user_logged_in());
            do_action('wp_login', $user->user_login, $user);
        } else {
            // Create a new user
            $userdata = array(
                'user_login' => $email,
                'user_email' => $email,
                'user_pass' => wp_generate_password(), // Generate a random password
                'display_name' => $name,
            );
            $user_id = wp_insert_user($userdata);

            if (is_wp_error($user_id)) {
                error_log('Error creating user: ' . print_r($user_id->get_error_messages(), true));
                return new WP_REST_Response(array('success' => false, 'message' => 'Error creating user'), 500);
            } else {
                // Log in the new user
                wp_set_current_user($user_id, $email);
                if (!is_user_logged_in()) {
                    error_log('Error setting current user');
                    return new WP_REST_Response(array('success' => false, 'message' => 'Error setting current user'), 500);
                }
                wp_set_auth_cookie($user_id);
                error_log(!is_user_logged_in());
                do_action('wp_login', $email, get_user_by('ID', $user_id));
            }
        }

        return new WP_REST_Response(array('success' => true, 'message' => 'Login successful'), 200);
    } else {
        error_log('Google token verification failed: ' . print_r($client->getAuth()->getLastError(), true));
        return new WP_REST_Response(array('success' => false, 'message' => 'Invalid token'), 401);
    }
}
?>x`

<button class="td-login-google">Log in With Google</button>



/* Google Login Button */
.td-login-google.ai-style-change-1 {
display: flex;
justify-content: center;    
align-items: center;
width: 36%; /* Set width to 40% of the parent */
height: 50px;
padding: 10px 20px;
margin: 10px;
background-color: #4285F4; /* Google blue */
color: white;
border: none;
border-radius: 5px;
font-size: 16px;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
cursor: pointer;
}

/* Parent Container */
.td-fix-index-google-login {
display: flex;
justify-content: center; /* Center the button horizontally */
width: 100%; /* Make the parent take full width */
/* You can add other styles for the parent here if needed */
}

/* Media Query for Mobile Devices */
@media (max-width: 768px) {
.td-login-google.ai-style-change-1 {
width: 90%; /* Make the button wider on smaller screens */
font-size: 14px; /* Reduce font size on smaller screens */
padding: 8px 16px; /* Reduce padding on smaller screens */
}
}