<?php

/**
 * GMW PT Shortcode - Shortcode display location of a single post, post type or a page.
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Single_Location {

    /**
     * __Constructor
     */
    function __construct() {

        add_shortcode( 'gmw_single_location', array( $this, 'get_single_location' ) );

    }

    public function get_single_location( $single ) {

        //default shortcode attributes
        extract(
                shortcode_atts(
                        array(
            'map'             => 1,
            'map_height'      => '250px',
            'map_width'       => '250px',
            'map_type'        => 'ROADMAP',
            'zoom_level'      => 13,
            'additional_info' => 'address,phone,fax,email,website',
            'post_id'         => 0,
            'directions'      => 1
                        ), $single )
        );

        $scID = rand( 5, 15 );

        /*
         * check if user entered post id
         */
        if ( $post_id == 0 ) :

            global $post;
            $post_id = $post->ID;

        endif;

        //get the post's info
        $post_info = gmw_get_post_location_from_db( $post_id );

        //if post has no location stop the function
        if ( $post_info == false )
            return;

        $location_info = '';
        $location_info .= '<div id="gmw-single-post-sc-' . $scID . '-wrapper" class="gmw-single-post-sc-wrapper">';

        if ( $map == 1 ) :

            $location_info .= '<div class="gmw-single-post-sc-map-wrapper">';
            $location_info .= '<div id="gmw-single-post-map-' . $scID . '" class="gmw-map" style="width:' . $map_width . '; height:' . $map_height . ';"></div>';
            $location_info .= '</div>';

        endif;

        if ( $directions == 1 ) :

            $location_info .= '<div class="gmw-single-post-sc-directions-wrapper">';
            $location_info .= '<div id="gmw-single-post-sc-form-' . $scID . '" class="gmw-single-post-sc-form" style="display:none;">';
            $location_info .= '<form action="http://maps.google.com/maps" method="get" target="_blank">';
            $location_info .= '<input type="text" size="35" name="saddr" value="" placeholder="Your location" />';
            $location_info .= '<input type="hidden" name="daddr" value="' . $post_info->address . '" />';
            $location_info .= '<input type="submit" class="button" value="' . __( 'GO', 'GMW' ) . '" />';
            $location_info .= '</form>';
            $location_info .= '</div>';
            $location_info .= '<a href="#" id="gmw-single-post-sc-directions-trigger-' . $scID . '"  class="gmw-single-post-sc-directions-trigger">' . __( 'Get Directions', 'GMW' ) . '</a>';
            $location_info .= '</div>';

        endif;

        //if we are showing additional information
        if ( isset( $additional_info ) || $additional_info != 0 ) :

            $additional_info = explode( ',', $additional_info );

            $location_info .= '<div class="gmw-single-post-sc-additional-info">';

            if ( in_array( 'address', $additional_info ) ) {
                $location_info .= '<div class="gmw-address"><span>' . __( 'Address: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->formatted_address ) ) ? $post_info->formatted_address : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'phone', $additional_info ) ) {
                $location_info .= '<div class="gmw-phone"><span>' . __( 'Phone: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->phone ) ) ? $post_info->phone : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'fax', $additional_info ) ) {
                $location_info .= '<div class="gmw-fax"><span>' . __( 'Fax: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->fax ) ) ? $post_info->fax : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'email', $additional_info ) ) {
                $location_info .= '<div class="gmw-email"><span>' . __( 'Email: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->email ) ) ? '<a href="mailto:' . $post_info->email . ' ">' . $post_info->email . '</a>' : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'website', $additional_info ) ) {
                $location_info .= '<div class="gmw-website"><span>' . __( 'Website: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->website ) ) ? '<a href="http://' . $post_info->website . '" target="_blank">' . $post_info->website . '</a>' : "N/A";
                $location_info .= '</div>';
            }

            $location_info .= '</div>';

        endif;

        $location_info .= '</div>';
        ?>
        <script>

            jQuery(document).ready(function($) {

                $('#gmw-single-post-sc-directions-trigger-<?php echo $scID; ?>').click(function(event) {
                    event.preventDefault();
                    $('#gmw-single-post-sc-form-<?php echo $scID; ?>').slideToggle();
                });

                if ('<?php echo $map; ?>' == 1) {

                    var gmwSinglePostMap = new google.maps.Map(document.getElementById('gmw-single-post-map-<?php echo $scID; ?>'), {
                        zoom: parseInt(<?php echo $zoom_level; ?>),
                        center: new google.maps.LatLng(<?php echo $post_info->lat; ?>, <?php echo $post_info->long; ?>),
                        mapTypeId: google.maps.MapTypeId['<?php echo $map_type; ?>'],
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                        }
                    });

                    var infowindow = new google.maps.InfoWindow();

                    if ('<?php echo $post_info->phone; ?>' != '')
                        gmwPhone = '<?php echo $post_info->phone; ?>';
                    else
                        gmwPhone = 'N/A';
                    if ('<?php echo $post_info->fax; ?>' != '')
                        gmwFax = '<?php echo $post_info->fax; ?>';
                    else
                        gmwFax = 'N/A';
                    if ('<?php echo $post_info->email; ?>' != '')
                        gmwEmail = '<?php echo $post_info->email; ?>';
                    else
                        gmwEmail = 'N/A';
                    if ('<?php echo $post_info->website; ?>' != '')
                        gmwWebsite = '<a href="http://<?php echo $post_info->website; ?>" target="_blank"><?php echo $post_info->website; ?></a>';
                    else
                        gmwWebsite = 'N/A';

                    gmwSinglePostInfoWindow =
                            '<div class="wppl-info-window" style="font-size: 13px;color: #555;line-height: 18px;font-family: arial;">' +
                            '<div class="map-info-title" style="color: #457085;text-transform: capitalize;font-size: 16px;margin-bottom: -10px;"><?php echo $post_info->post_title; ?></div>' +
                            '<br /> <span style="font-weight: bold;color: #333;">Address: </span><?php echo $post_info->address; ?>' +
                            '<br /> <span style="font-weight: bold;color: #333;">Phone: </span>' + gmwPhone +
                            '<br /> <span style="font-weight: bold;color: #333;">Fax: </span>' + gmwFax +
                            '<br /> <span style="font-weight: bold;color: #333;">Email: </span>' + gmwEmail +
                            '<br /> <span style="font-weight: bold;color: #333;">Website: </span>' + gmwWebsite;

                    gmwSinglePostMarker = new google.maps.Marker({
                        position: new google.maps.LatLng(<?php echo $post_info->lat; ?>, <?php echo $post_info->long; ?>),
                        map: gmwSinglePostMap,
                        shadow: 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow'
                    });

                    google.maps.event.addListener(gmwSinglePostMarker, 'click', function() {
                        infowindow.setContent(gmwSinglePostInfoWindow);
                        infowindow.open(gmwSinglePostMap, gmwSinglePostMarker);
                    });

                }
                ;

            });

        </script>
        <?php
        return $location_info;

    }

}

new GMW_Single_Location();
