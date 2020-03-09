<?php

/*
    Plugin Name: wp7_training
    Plugin URI: http://jadipesan.com/
    Description: custom role.
    Version: 1.0
    Author: Moch Mufiddin
    Author URI: http://jadipesan.com/
    License: GPLv2
*/

class wp7_training {

    function __construct()
    {
        #register custom role
        add_action( 'init', [$this, 'add_user_role'] );

        #register sortcode
        add_shortcode( 'wp7_users', [$this, 'all_user_view'] );
    }

    function add_user_role() 
    {
        $staff_previlage = array( 
            // lihat profile
            'read' => true, 
            
            'delete_others_posts' => true,
            'delete_posts' => true,
            'delete_private_posts' => true,
            'delete_published_posts' => true,
            'edit_others_posts' => true,
            'edit_posts' => true,
            'edit_private_posts' => true,
            'edit_published_posts' => true,
            'manage_categories' => true,
            'moderate_comments' => true,
            'publish_posts' => true,
            'read_private_posts' => true
        );

        $manager_previlage = array(
            // lihat member
            'list_users' => true,
            'edit_users' => true,  
            'delete_users' => true,
            'remove_users' => true,
            'add_users' => true,
            'create_users' => true
        );

        if ( get_option( 'custom_roles_version' ) < 1 ) 
        {
            add_role( 'staff', 'Staff', $staff_previlage);
            add_role( 'manager', 'Manager', array_combine($staff_previlage, $manager_previlage) );
            
            // prevent it fires twice
            update_option( 'custom_roles_version', 1 );
        }
    }

    # sortcode design
    function all_user_view($atts)
    {
        $attributes = shortcode_atts( 
            array(
                'type' => "all",
            ), $atts
        );

        if( $attributes['type']=="staff" )
        {
            $role = array("staff");

        }
        elseif( $attributes['type']=="manager" )
        {
            $role = array("manager");
        }
        else{
            $role = array("manager", 'staff');    
        }

        $this->_get_user( $role );
    }

    function _get_user( $role=array('manager', 'staff') )
    {
        $current_page = get_query_var('paged') ? (int) get_query_var('paged') : 1;
        $users_per_page = 2; 

        // WP_User_Query arguments
        $args = array (
            'role__in'  => $role,
            'order'     => 'ASC',
            'orderby'   => 'display_name',
            'number'    => $users_per_page,
            'paged'     => $current_page
        );

        // Create the WP_User_Query object
        $wp_user_query = new WP_User_Query( $args );
        $total_users = $wp_user_query->get_total(); // How many users we have in total (beyond the current page)
        $num_pages = ceil($total_users / $users_per_page); // How many pages of users we will need


        // Get the results
        $authors = $wp_user_query->get_results();

        // Check for results
        if ( ! empty( $authors ) ) {
            echo '<ul>';
            // loop through each author
            foreach ( $authors as $author ) {
                // get all the user's data
                $author_info = get_userdata( $author->ID );
                echo '<li>' . $author_info->first_name . ' ' . $author_info->last_name . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'No authors found';
        }

        // Previous page
        if ( $current_page > 1 ) {
            echo '<a href="'. add_query_arg(array('paged' => $current_page-1)) .'">Previous Page</a>';
        }

        // Next page
        if ( $current_page < $num_pages ) {
            echo '<a href="'. add_query_arg(array('paged' => $current_page+1)) .'">Next Page</a>';
        }
    }

}

$plugin = new wp7_training();

