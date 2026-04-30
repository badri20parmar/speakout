<?php

// register shortcode to display signatures count
add_shortcode( 'signaturecount', 'dk_speakout_signaturescount_shortcode' );

function dk_speakout_signaturescount_shortcode( $attr ) {
    include_once( 'class.petition.php' );
    $petition = new dk_speakout_Petition();
    $id       = dk_speakout_resolve_petition_id_from_shortcode_atts( $attr, 1 );
    if ( ! $id ) {
        return '';
    }

    $petition_exists = $petition->retrieve( $id );
    if ( $petition_exists ) {
        return "<span class='signatureCount'>" . $petition->signatures . "</span>";
    } else {
        return '';
    }
}

// register shortcode to display total number of signatures
// optional paramater hideuncofirmed="true" will only display the count of confirmed signatures
add_shortcode( 'signaturestotal', 'dk_speakout_signaturestotal_shortcode' );

function dk_speakout_signaturestotal_shortcode( $atts ) {
    include_once( 'class.signature.php' );
    $signatures = new dk_speakout_Signature();

    $attr = isset($atts["hideunconfirmed"]) && $atts["hideunconfirmed"] == "true" ? "true" : ""; 
    $sigsTotal = $signatures->count( "","", $attr );

        return "<span class='signatureTotal'>" . $sigsTotal . "</span>";
}

// register shortcode to display signatures goal
add_shortcode( 'signaturegoal', 'dk_speakout_signaturesgoal_shortcode' );

function dk_speakout_signaturesgoal_shortcode( $attr ) {
    include_once( 'class.petition.php' );
    $petition = new dk_speakout_Petition();

    $id = dk_speakout_resolve_petition_id_from_shortcode_atts( $attr, 1 );
    if ( ! $id ) {
        return '';
    }

    $petition_exists = $petition->retrieve( $id );
    if ( $petition_exists ) {
        return "<span class='signatureGoal'>" . $petition->goal . "</span>";
    } else {
        return '';
    }
}

// register shortcode to display petition title
add_shortcode( 'petitiontitle', 'dk_speakout_petitiontitle_shortcode' );

function dk_speakout_petitiontitle_shortcode( $attr ) {
    $attr = is_array( $attr ) ? $attr : array();

    include_once( 'class.petition.php' );
    $petition = new dk_speakout_Petition();

    $id = dk_speakout_resolve_petition_id_from_shortcode_atts( $attr, 1 );
    if ( ! $id ) {
        return '';
    }

    $petition_exists = $petition->retrieve( $id );
    if ( $petition_exists ) {
        return "<span class='petitionTitle'>" . $petition->title . "</span>";
    } else {
        return '';
    }
}

// register shortcode to display petition message
add_shortcode( 'petitionmessage', 'dk_speakout_petitionmessage_shortcode' );

function dk_speakout_petitionmessage_shortcode( $attr ) {
    $attr = is_array( $attr ) ? $attr : array();

    include_once( 'class.petition.php' );
    $petition = new dk_speakout_Petition();

    $id = dk_speakout_resolve_petition_id_from_shortcode_atts( $attr, 1 );
    if ( ! $id ) {
        return '';
    }

    $petition_exists = $petition->retrieve( $id );
    if ( $petition_exists ) {
        if ( !class_exists( 'Parsedown' ) ) {
            include_once( 'parsedown.php' );
        }
        $Parsedown = new Parsedown();
        return "<span class='petitionMessage'>" . $Parsedown->text( $petition->petition_message ). "</span>";
    } else {
        return '';
    }
}

/**
 * Enqueue SpeakOut front-end CSS/JS (for Elementor shortcodes that are not in post_content).
 */
function dk_speakout_enqueue_public_petition_assets() {
    static $done = false;
    if ( $done ) {
        return;
    }
    if ( wp_style_is( 'dk_speakout_css', 'enqueued' ) || wp_style_is( 'dk_speakout_css', 'done' ) ) {
        $done = true;
        return;
    }
    $done = true;

    $options = get_option( 'dk_speakout_options' );
    $theme   = isset( $options['petition_theme'] ) ? $options['petition_theme'] : 'basic';

    switch ( $theme ) {
        case 'default':
            wp_enqueue_style( 'dk_speakout_css', plugins_url( 'css/theme-default.css', dk_speakout_plugin_file() ), array(), dk_speakout_asset_version() );
            break;
        case 'basic':
            wp_enqueue_style( 'dk_speakout_css', plugins_url( 'css/theme-basic.css', dk_speakout_plugin_file() ), array(), dk_speakout_asset_version() );
            break;
        case 'none':
            $parent_dir                = get_template_directory_uri();
            $parent_petition_theme_url = $parent_dir . '/petition.css';
            if ( is_child_theme() ) {
                $child_petition_theme_url = get_stylesheet_directory_uri() . '/petition.css';
                $child_petition_theme_path = get_stylesheet_directory() . '/petition.css';
                if ( file_exists( $child_petition_theme_path ) ) {
                    wp_enqueue_style( 'dk_speakout_css', $child_petition_theme_url, array(), dk_speakout_asset_version() );
                } else {
                    wp_enqueue_style( 'dk_speakout_css', $parent_petition_theme_url, array(), dk_speakout_asset_version() );
                }
            } else {
                wp_enqueue_style( 'dk_speakout_css', $parent_petition_theme_url, array(), dk_speakout_asset_version() );
            }
            break;
        default:
            wp_enqueue_style( 'dk_speakout_css', plugins_url( 'css/theme-basic.css', dk_speakout_plugin_file() ), array(), dk_speakout_asset_version() );
            break;
    }

    $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
    $params   = array( 'ajaxurl' => admin_url( 'admin-ajax.php', $protocol ) );
    $params['manage_nonce'] = wp_create_nonce( 'dk_speakout_manage_signature' );
    $params['i18n']         = array(
        'thanks_title'     => __( 'Thank you for signing!', 'speakout' ),
        'update_signature' => __( 'Update signature', 'speakout' ),
        'remove_signature' => __( 'Remove signature', 'speakout' ),
        'change_comment'   => __( 'Change your comment', 'speakout' ),
        'copy_link'        => __( 'Copy link', 'speakout' ),
        'copied'           => __( 'Copied!', 'speakout' ),
        'close'            => __( 'Close', 'speakout' ),
        'confirm_remove'   => __( 'Remove your signature from this petition?', 'speakout' ),
    );
    if ( isset( $options['g_recaptcha_status'] ) && $options['g_recaptcha_status'] == 'on' ) {
        wp_enqueue_script( 'dk_speakout_js', plugins_url( 'js/public-gr.js', dk_speakout_plugin_file() ), array( 'jquery' ), dk_speakout_asset_version() );
    } elseif ( isset( $options['hcaptcha_status'] ) && $options['hcaptcha_status'] == 'on' ) {
        wp_enqueue_script( 'dk_speakout_js', plugins_url( 'js/public-h.js', dk_speakout_plugin_file() ), array( 'jquery' ), dk_speakout_asset_version() );
    } else {
        wp_enqueue_script( 'dk_speakout_js', plugins_url( 'js/public.js', dk_speakout_plugin_file() ), array( 'jquery' ), dk_speakout_asset_version() );
    }
    wp_enqueue_script( 'jquery-effects-highlight' );
    wp_localize_script( 'dk_speakout_js', 'dk_speakout_js', $params );
}

// Hub link for Elementor loops / excerpts (uses petition linked on the post).
add_shortcode( 'speakout_read_more', 'dk_speakout_read_more_shortcode' );

function dk_speakout_read_more_shortcode( $attr ) {
    $attr = is_array( $attr ) ? $attr : array();
    $text = isset( $attr['text'] ) ? $attr['text'] : __( 'Read more', 'speakout' );
    $pid  = dk_speakout_resolve_petition_id_from_shortcode_atts( $attr, null );
    if ( ! $pid ) {
        return '';
    }
    dk_speakout_enqueue_public_petition_assets();
    if ( ! function_exists( 'dk_speakout_petition_hub_url' ) ) {
        return '';
    }
    $url     = dk_speakout_petition_hub_url( array( 'petition' => $pid ) );
    $classes = 'dk-speakout-readme dk-speakout-readmore-loop';
    if ( ! empty( $attr['class'] ) ) {
        $classes .= ' ' . sanitize_text_field( $attr['class'] );
    }
    return '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $url ) . '"><span>' . esc_html( $text ) . '</span></a>';
}

add_shortcode( 'speakout_petition_progress', 'dk_speakout_petition_progress_shortcode' );

function dk_speakout_petition_progress_shortcode( $attr ) {
    $attr = is_array( $attr ) ? $attr : array();
    $id   = dk_speakout_resolve_petition_id_from_shortcode_atts( $attr, 1 );
    if ( ! $id ) {
        return '';
    }
    dk_speakout_enqueue_public_petition_assets();
    include_once( 'class.speakout.php' );
    include_once( 'class.petition.php' );
    $options  = get_option( 'dk_speakout_options' );
    $petition = new dk_speakout_Petition();
    if ( ! $petition->retrieve( $id ) ) {
        return '';
    }
    if ( empty( $options['display_count'] ) || (int) $options['display_count'] !== 1 ) {
        return '';
    }
    $progress_width = ( isset( $options['petition_theme'] ) && $options['petition_theme'] == 'basic' ) ? 300 : 200;
    if ( isset( $attr['progresswidth'] ) && is_numeric( $attr['progresswidth'] ) ) {
        $progress_width = absint( $attr['progresswidth'] );
    }
    $out = '<div class="dk-speakout-progress-wrap dk-speakout-progress-wrap-top dk-speakout-progress-loop">';
    if ( $petition->goal != 0 ) {
        $sig_fmt  = number_format( $petition->signatures, 0, $options['decimal_separator'], $options['thousands_separator'] );
        $goal_fmt = number_format( $petition->goal, 0, $options['decimal_separator'], $options['thousands_separator'] );
        $out     .= '<div class="dk-speakout-signature-count dk-speakout-signature-goal-line">' . sprintf(
            /* translators: 1: current signature count, 2: signature goal */
            __( '%1$s of a %2$s signature goal', 'speakout' ),
            '<span>' . $sig_fmt . '</span>',
            $goal_fmt
        ) . '</div>';
        $out .= '<div class="dk-speakout-count">0' . dk_speakout_SpeakOut::progress_bar( $petition->goal, $petition->signatures, $progress_width ) . ' ' . $goal_fmt . '</div>';
    } else {
        $out .= '<div class="dk-speakout-signature-count"><span>' . number_format( $petition->signatures, 0, $options['decimal_separator'], $options['thousands_separator'] ) . '</span> ' . __( 'signatures', 'speakout' ) . '</div>';
    }
    $out .= '</div>';
    return $out;
}

add_shortcode( 'speakout_card_teaser', 'dk_speakout_card_teaser_shortcode' );

function dk_speakout_card_teaser_shortcode( $attr ) {
    $attr  = is_array( $attr ) ? $attr : array();
    $inner = dk_speakout_petition_progress_shortcode( $attr );
    $link  = dk_speakout_read_more_shortcode( $attr );
    if ( $inner === '' && $link === '' ) {
        return '';
    }
    return '<div class="dk-speakout-elementor-card-teaser">' . $inner . $link . '</div>';
}

// register shortcode to display petition form
add_shortcode( 'emailpetition', 'dk_speakout_emailpetition_shortcode' );

function dk_speakout_emailpetition_shortcode( $attr ) {

    // Check if we have a form preslected
    if ( array_key_exists( 'petition', $_GET ) ) {
        $attr[ 'id' ] = $_GET[ 'petition' ];
    }

    // only query a petition if the "id" attribute has been set
    if ( isset( $attr[ 'id' ] ) && is_numeric( $attr[ 'id' ] ) ) {

        global $dk_speakout_version;
        include_once( 'class.speakout.php' );
        include_once( 'class.petition.php' );
        include_once( 'class.wpml.php' );
        $petition = new dk_speakout_Petition();
        $wpml = new dk_speakout_WPML();
        $options = get_option( 'dk_speakout_options' );
        
        // get petition data from database
        $id = absint( $attr[ 'id' ] );
        $petition_exists = $petition->retrieve( $id );

        // attempt to translate with WPML
        $wpml->translate_petition( $petition );
        $options = $wpml->translate_options( $options );
        $wpml_lang = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';

        if ( $petition_exists ) {
            
            //array of allowable HTML in petition message display
            $kses_array = array(
                'a' => array(
                    'href' => array(),
                    'title' => array()
                ),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
                'p' => array(),
            );

            $expired = ( $petition->expires == 1 && current_time( 'timestamp' ) >= strtotime( $petition->expiration_date ) ) ? 1 : 0;

            // shortcode attributes
            $width = isset( $attr[ 'width' ] ) ? 'style="width: ' . $attr[ 'width' ] . ';"': '';
            $height = isset( $attr[ 'height' ] ) ? 'style="height: ' . $attr[ 'height' ] . ' !important;"': '';
            $css_classes = isset( $attr[ 'class' ] ) ? $css_classes = $attr[ 'class' ] : '';
            $progress_width = ( $options[ 'petition_theme' ] == 'basic' ) ? 300 : 200; // defaults
            $progress_width = isset( $attr[ 'progresswidth' ] ) ? $attr[ 'progresswidth' ] : $progress_width;

            if ( !$expired ) {
                $userdata = dk_speakout_SpeakOut::userinfo();

                // compose the petition form
                $petitionReadTitle = $petition->is_editable ? $petition->open_editable_message_button : $petition->open_message_button;

                $petition_form = "";
                //if we are displaying recaptcha include javascript			
                if ( isset( $options[ 'g_recaptcha_status' ] ) && $options[ 'g_recaptcha_status' ] == "on" ) {
                    //render based on version 2 or 3
                    if ( $options[ 'g_recaptcha_version' ] == 2 || $options[ 'g_recaptcha_version' ] == 0 ) {
                        $petition_form = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
                    } elseif ( $options[ 'g_recaptcha_version' ] == 3 ) {
                        $petition_form = '<script src="https://www.google.com/recaptcha/api.js?render=' . $options[ 'g_recaptcha_site_key' ] . '"></script>';
                        $petition_form .= "<input type='hidden' id='dk-speakout-recaptcha-key' value='" . $options[ 'g_recaptcha_site_key' ] . "'>";
                    }
                }
                
                //if we are displaying hCaptcha include javascript			
                if ( isset( $options[ 'hcaptcha_status' ] ) && $options[ 'hcaptcha_status' ] == "on" ) {
                        $petition_form = '<script src="https://js.hcaptcha.com/1/api.js" async defer></script>';
                }

                //get the language
                list( $lang ) = explode( '-', get_bloginfo( 'language' ) );
                $petition_form .= '<!-- Custom SpeakOut ' . $dk_speakout_version . ' : ' . ucfirst( $lang ) . ' -->' . "\n";

                //prepare for required custom fields
                if ( $petition->custom_field_required == 1 ) {
                    $custom_field_required = " REQUIRED ";
                    $petition_form .= '<p id="dk-speakout-custom-required" style="display:none;" />';
                } else {
                    $custom_field_required = "";
                }
                
                if ( $petition->custom_field2_required == 1 ) {
                    $custom_field2_required = " REQUIRED ";
                    $petition_form .= '<p id="dk-speakout-custom2-required" style="display:none;" />';
                } else {
                    $custom_field2_required = "";
                }
                if ( $petition->custom_field3_required == 1 ) {
                    $custom_field3_required = " REQUIRED ";
                    $petition_form .= '<p id="dk-speakout-custom3-required" style="display:none;" />';
                } else {
                    $custom_field3_required = "";
                }
                if ( $petition->custom_field4_required == 1 ) {
                    $custom_field4_required = " REQUIRED ";
                    $petition_form .= '<p id="dk-speakout-custom4-required" style="display:none;" />';
                } else {
                    $custom_field4_required = "";
                }
                if ( $petition->custom_field5_required == 1 ) {
                    $custom_field5_required = ' required="required" ';
                    $petition_form .= '<p id="dk-speakout-custom5-required" style="display:none;" />';
                } else {
                    $custom_field5_required = "";
                }
                if ( $petition->custom_field6_required == 1 ) {
                    $custom_field6_required = " REQUIRED ";
                    $petition_form .= '<p id="dk-speakout-custom6-required" style="display:none;" />';
                } else {
                    $custom_field6_required = "";
                }
                if ( $petition->custom_field7_required == 1 ) {
                    $custom_field7_required = " REQUIRED ";
                    $petition_form .= '<p id="dk-speakout-custom7-required" style="display:none;" />';
                } else {
                    $custom_field7_required = "";
                }
                if ( $petition->custom_field8_required == 1 ) {
                    $custom_field8_required = " REQUIRED ";
                    $petition_form .= '<p id="dk-speakout-custom8-required" style="display:none;" />';
                } else {
                    $custom_field8_required = "";
                }
                if ( $petition->custom_field9_required == 1 ) {
                    $custom_field9_required = " REQUIRED ";
                    $petition_form .= '<p id="dk-speakout-custom9-required" style="display:none;" />';
                } else {
                    $custom_field9_required = "";
                }


                $petition_form .= '<div id="dk-speakout-windowshade"></div>
					<div class="dk-speakout-petition-wrap ' . $css_classes . '" id="dk-speakout-petition-' . $petition->id . '" ' . $width . '>
						<h3>' . stripslashes( esc_html( $petition->title ) ) . '</h3>';

                if ( $options[ 'display_count' ] == 1 ) {
                    $petition_form .= '<div class="dk-speakout-progress-wrap dk-speakout-progress-wrap-top">';
                    if ( $petition->goal != 0 ) {
                        $sig_fmt = number_format( $petition->signatures, 0, $options['decimal_separator'], $options['thousands_separator'] );
                        $goal_fmt = number_format( $petition->goal, 0, $options['decimal_separator'], $options['thousands_separator'] );
                        $petition_form .= '<div class="dk-speakout-signature-count dk-speakout-signature-goal-line">' . sprintf(
                            /* translators: 1: current signature count, 2: signature goal */
                            __( '%1$s of a %2$s signature goal', 'speakout' ),
                            '<span>' . $sig_fmt . '</span>',
                            $goal_fmt
                        ) . '</div>';
                        $petition_form .= '<div class="dk-speakout-count">0' . dk_speakout_SpeakOut::progress_bar( $petition->goal, $petition->signatures, $progress_width ) . ' ' . $goal_fmt . '</div>';
                    } else {
                        $petition_form .= '<div class="dk-speakout-signature-count"><span>' . number_format( $petition->signatures, 0, $options['decimal_separator'], $options['thousands_separator'] ) . '</span> ' . __( 'signatures', 'speakout' ) . '</div>';
                    }
                    $petition_form .= '</div>';
                }

                $petition_form .= '<div id="dk-speakout-form-wrap">
    				            <form class="dk-speakout-petition">';
                $petition_form .= '<input type="hidden" id="dk-speakout-posttitle-' . $petition->id . '" value="' . esc_attr( urlencode( stripslashes( $petition->title ) ) ) . '" />' . "\n";
                $petition_form .= '<input type="hidden" id="dk-speakout-tweet-' . $petition->id . '" value="' . dk_speakout_SpeakOut::x_encode( $petition->x_message ) . '" />' . "\n";
                // show language in HTML comment for support
                $petition_form .= '<input type="hidden" id="dk-speakout-lang-' . $petition->id . '" value="' . $wpml_lang . '" />' . "\n";
                $petition_form .= '<input type="hidden" id="dk-speakout-textval-' . $petition->id . '" value="val" />' . "\n";
                // Is petition fading out?
                $petition_form .= '<input type="hidden" id="dk-speakout-petition-fade-' . $petition->id . '" value="' . $options[ "petition_fade" ] . '" />' . "\n";
                // Are we confirming signatures?	
                $petition_form .= '<input type="hidden" id="dk-speakout-requires_confirmation-' . $petition->id . '" value="' . $petition->requires_confirmation . '" />' . "\n";
                // Are we collecting email address?
                $petition_form .= '<input type="hidden" id="dk-speakout-hide-email-field-' . $petition->id . '" value="' . $petition->hide_email_field . '" />' . "\n";
                //default these checkboxes to off
                //$petition_form .= '<input type="hidden" id="dk-speakout-custom-field6-' . $petition->id . '" value="0" />' . "\n";
                //$petition_form .= '<input type="hidden" id="dk-speakout-custom-field7-' . $petition->id . '" value="0" />' . "\n";

                //prepare for redirect URL
                if ( $petition->redirect_url_option == 1 && $petition->redirect_url > "" ) {
                    $petition_form .= '<input type="hidden" id="dk-speakout-url-target-' . $petition->id . '" value="' . $petition->url_target . '" />' . "\n";
                    $petition_form .= '<input type="hidden" id="dk-speakout-redirect-url-' . $petition->id . '" value="' . $petition->redirect_url . '" />' . "\n";
                    $petition_form .= '<input type="hidden" id="dk-speakout-redirect-delay-' . $petition->id . '" value="' . $petition->redirect_delay . '" />' . "\n";
                }

                //display custom fields at top
                if ( $petition->displays_custom_field == 1 && $petition->custom_field_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field" id="dk-speakout-custom-field-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field_label ) ) . '"' . $custom_field_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field2 == 1 && $petition->custom_field2_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field2" id="dk-speakout-custom-field2-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field2_label ) ) . '"' . $custom_field2_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field3 == 1 && $petition->custom_field3_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field3" id="dk-speakout-custom-field3-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field3_label ) ) . '"' . $custom_field3_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field4 == 1 && $petition->custom_field4_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field4" id="dk-speakout-custom-field4-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field4_label ) ) . '"' . $custom_field4_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field5 == 1 && $petition->custom_field5_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    						<select name="dk-speakout-custom-field5" id="dk-speakout-custom-field5-' . $petition->id . '"' . $custom_field5_required . ' >
    						<option value="">' . $petition->custom_field5_label . '</option>';
    						$arrFieldValues = explode("|",$petition->custom_field5_values);
    						foreach($arrFieldValues as $fieldValue ){
    						    $petition_form .= '<option value="' . $fieldValue . '">' . $fieldValue . '</option>';
    						}
    					
    								
    				$petition_form .= '</select>
    				</div>';
                }
                if ( $petition->displays_custom_field6 == 1 && $petition->custom_field6_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field6" id="dk-speakout-custom-field6-' . $petition->id . '" type="checkbox" ' . $custom_field6_required . '  /> <label for="dk-speakout-custom-field6-' . $petition->id . '">' . stripslashes( $petition->custom_field6_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field7 == 1 && $petition->custom_field7_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field7" id="dk-speakout-custom-field7-' . $petition->id . '" type="checkbox" ' . $custom_field7_required . '  /> <label for="dk-speakout-custom-field7-' . $petition->id . '">' .stripslashes( $petition->custom_field7_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field8 == 1 && $petition->custom_field8_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field8" id="dk-speakout-custom-field8-' . $petition->id . '" type="checkbox" ' . $custom_field8_required . '  /> <label for="dk-speakout-custom-field8-' . $petition->id . '">' .stripslashes( $petition->custom_field8_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field9 == 1 && $petition->custom_field9_location < 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field9" id="dk-speakout-custom-field9-' . $petition->id . '" type="checkbox" ' . $custom_field9_required . '  /> <label for="dk-speakout-custom-field9-' . $petition->id . '">' .stripslashes( $petition->custom_field9_label ) . '</label>
    							</div>';
                }
                // do we display honorific?
                if ( $options[ 'display_honorific' ] == 'enabled' ) {
                    $honorifics = "";
                    

                    // if custom honorifics file exists use that else fall back to included honorifics list
                    $custom_file_name = file_exists( plugin_dir_path( __DIR__ ) . "custom/honorifics.txt") ? plugin_dir_path( __DIR__ ) . "custom/honorifics.txt" : plugin_dir_path( __DIR__ ) . "includes/honorifics.txt";
                    // open honorifics list
                    if ( $file = fopen( $custom_file_name, "r" ) ) {
                        //loop through the file
                        while ( !feof( $file ) ) {
                            // grab the custom title
                            $theName = fgets( $file );
                            //build our string but leave out blank lines
                            if($theName > ""){
                                $honorifics .= '<option value="' . $theName . '">' . $theName . '</option>' . PHP_EOL;
                            }
                        }

                        //close the file
                        fclose( $file );
                    }
                    $petition_form .= '    <div class="dk-speakout-full">
                                <select name="dk-speakout-honorific" id="dk-speakout-honorific-' . $petition->id . '">'
                    . $honorifics .
                    '</select>
                                </div>';
                }

                $petition_form .= '	<div class="dk-speakout-full">
    								<input autocomplete="given-name" name="dk-speakout-first-name" id="dk-speakout-first-name-' . $petition->id . '" value="' . $userdata[ 'firstname' ] . '" type="text" placeholder="' . __( 'First Name', 'speakout' ) . '" required="required"  />
    							</div>
    							
    							<div class="dk-speakout-full">
    								<input autocomplete="family-name" name="dk-speakout-last-name" id="dk-speakout-last-name-' . $petition->id . '" value="' . $userdata[ 'lastname' ] . '" type="text" placeholder="' . __( 'Last Name', 'speakout' ) . '" required="required"  />
    							</div>';

                //display custom fields in middle
                if ( $petition->displays_custom_field == 1 && $petition->custom_field_location == 2 ) {
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field" id="dk-speakout-custom-field-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field_label ) ) . '"' . $custom_field_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field2 == 1 && $petition->custom_field2_location == 2 ) {
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field2" id="dk-speakout-custom-field2-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field2_label ) ) . '"' . $custom_field2_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field3 == 1 && $petition->custom_field3_location == 2 ) {
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field3" id="dk-speakout-custom-field3-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field3_label ) ) . '"' . $custom_field3_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field4 == 1 && $petition->custom_field4_location == 2 ) {
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field4" id="dk-speakout-custom-field4-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field4_label ) ) . '"' . $custom_field4_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field5 == 1 && $petition->custom_field5_location == 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    						<select name="dk-speakout-custom-field5" id="dk-speakout-custom-field5-' . $petition->id . '"' . $custom_field5_required . ' >
    						<option value="">' . $petition->custom_field5_label . '</option>';
    						$arrFieldValues = explode("|",$petition->custom_field5_values);
    						foreach($arrFieldValues as $fieldValue ){
    						    $petition_form .= '<option value="' . $fieldValue . '">' . $fieldValue . '</option>';
    						}
    					
    								
    				$petition_form .= '</select>
    				</div>';
                }
                if ( $petition->displays_custom_field6 == 1 && $petition->custom_field6_location == 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field6" id="dk-speakout-custom-field6-' . $petition->id . '" type="checkbox" ' . $custom_field6_required . ' value="1" /> <label for="dk-speakout-custom-field6-' . $petition->id . '">' .stripslashes( $petition->custom_field6_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field7 == 1 && $petition->custom_field7_location == 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field7" id="dk-speakout-custom-field7-' . $petition->id . '" type="checkbox" ' . $custom_field7_required . ' /> <label for="dk-speakout-custom-field7-' . $petition->id . '">' .stripslashes( $petition->custom_field7_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field8 == 1 && $petition->custom_field8_location == 2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field8" id="dk-speakout-custom-field8-' . $petition->id . '" type="checkbox" ' . $custom_field8_required . '  /> <label for="dk-speakout-custom-field8-' . $petition->id . '">' .stripslashes( $petition->custom_field8_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field9 == 1 && $petition->custom_field9_location ==2 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field9" id="dk-speakout-custom-field9-' . $petition->id . '" type="checkbox" ' . $custom_field9_required . '  /> <label for="dk-speakout-custom-field9-' . $petition->id . '">' .stripslashes( $petition->custom_field9_label ) . '</label>
    							</div>';
                }

                // if only collecting signatures it is possible to hide email field.
                if ( $petition->hide_email_field != 1 ) {
                    $petition_form .= '<div class="dk-speakout-full">
    								<input autocomplete="email" name="dk-speakout-email" id="dk-speakout-email-' . $petition->id . '" value="' . $userdata[ 'email' ] . '" type="email"  placeholder="' . __( 'Email', 'speakout' ) . '" required="required"  />
    							</div>';
                }

                if ( in_array( 'street', $petition->address_fields ) ) {
                    $required = $petition->street_required == 1 ? " required='required' " : "";
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input  autocomplete="address-line1" name="dk-speakout-street" id="dk-speakout-street-' . $petition->id . '" maxlength="200" type="text"  placeholder="' . __( 'Street', 'speakout' ) . '" ' . $required . ' />
    							</div>';
                }
                $petition_form .= '<div>'; // need this div to give half-width fields a new parent - so we can style their margins differently by :nth-child

                // option allows for EU postal code position before city
                if ( in_array( 'postcode', $petition->address_fields ) && $options[ 'eu_postalcode' ] == 'enabled' ) {
                    $required = $petition->postcode_required == 1 ? ' required="required" ' : '';
                    $petition_form .= '
    							<div class="dk-speakout-half">
    								<input  autocomplete="postal-code" name="dk-speakout-postcode" id="dk-speakout-postcode-' . $petition->id . '" maxlength="200" type="text"  placeholder="' . __( 'Postal Code', 'speakout' ) . '" ' . $required . '/>
    							</div>';
                }
                if ( in_array( 'city', $petition->address_fields ) ) {
                    $required = $petition->city_required == 1 ? ' required="required" ' : '';
                    $petition_form .= '
    							<div class="dk-speakout-half">
    								<input  autocomplete="address-level2" name="dk-speakout-city" id="dk-speakout-city-' . $petition->id . '" maxlength="200" type="text" placeholder="' . __( 'City', 'speakout' ) . '" ' . $required . ' />
    							</div>';
                }
                if ( in_array( 'state', $petition->address_fields ) ) {
                    $required = $petition->state_required == 1 ? ' required="required" ' : '';
                    $petition_form .= '
    							<div class="dk-speakout-half">
    								<input  autocomplete="address-level1" name="dk-speakout-state" id="dk-speakout-state-' . $petition->id . '" maxlength="200" type="text" list="dk-speakout-states"  placeholder="' . __( 'State / Province', 'speakout' ) . '" ' . $required . ' />
    							
    							</div>';
                }
                // non EU postal code position
                if ( in_array( 'postcode', $petition->address_fields ) && $options[ 'eu_postalcode' ] != 'enabled' ) {
                    $required = $petition->postcode_required == 1 ? ' required="required" ' : '';
                    $petition_form .= '
    							<div class="dk-speakout-half">
    								<input  autocomplete="postal-code" name="dk-speakout-postcode" id="dk-speakout-postcode-' . $petition->id . '" maxlength="200" type="text"  placeholder="' . __( 'Postal Code', 'speakout' ) . '" ' . $required . '/>
    							</div>';
                }

                if ( in_array( 'country', $petition->address_fields ) ) {
                    $required = $petition->country_required == 1 ? ' required="required" ' : '';
                    $countries = "";
                    
                    // if custom country file exists use that else fall back to included country list
                    $custom_file_name = file_exists(plugin_dir_path( __DIR__ ) . "custom/countries.txt") ? plugin_dir_path( __DIR__ ) . "custom/countries.txt" : plugin_dir_path( __DIR__ ) . "includes/countries.txt";
                    
                    // open coutires list
                    if ( $file = fopen( $custom_file_name, "r" ) ) {
                        //loop through the file
                        while ( !feof( $file ) ) {
                            // grab the custom title
                            $theName = fgets( $file );

                            if($theName > ""){
                                //country has two components so split them
                                $arrCountry = explode( "|", $theName );
                                //build our string
                                $countries .= '<option value="' . $arrCountry[ 0 ] . '">' . $arrCountry[ 1 ] . '</option>' . PHP_EOL;
                            }
                        }

                        fclose( $file );
                    }

                    $petition_form .= '
    							<div class="dk-speakout-half">
    								<select name="dk-speakout-country"   id="dk-speakout-country-' . $petition->id . '" ' . $required . ' />
    								    <option value="">' . __( 'Country', 'speakout' ) . '</option>'
                    . $countries .

                    '</select>
    							</div>';
                }
                //display custom fields at bottom
                if ( $petition->displays_custom_field == 1 && $petition->custom_field_location == 3 ) {
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field" id="dk-speakout-custom-field-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field_label ) ) . '"' . $custom_field_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field2 == 1 && $petition->custom_field2_location == 3 ) {
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field2" id="dk-speakout-custom-field2-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field2_label ) ) . '"' . $custom_field2_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field3 == 1 && $petition->custom_field3_location == 3 ) {
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field3" id="dk-speakout-custom-field3-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field3_label ) ) . '"' . $custom_field3_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field4 == 1 && $petition->custom_field4_location == 3 ) {
                    $petition_form .= '
    							<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field4" id="dk-speakout-custom-field4-' . $petition->id . '" maxlength="400" type="text"  placeholder="' . stripslashes( esc_html( $petition->custom_field4_label ) ) . '"' . $custom_field4_required . ' />
    							</div>';
                }
                if ( $petition->displays_custom_field5 == 1 && $petition->custom_field5_location == 3 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    						<select name="dk-speakout-custom-field5" id="dk-speakout-custom-field5-' . $petition->id . '"' . $custom_field5_required . ' >
    						<option value="">' . $petition->custom_field5_label . '</option>';
    						$arrFieldValues = explode("|",$petition->custom_field5_values);
    						foreach($arrFieldValues as $fieldValue ){
    						    $petition_form .= '<option value="' . $fieldValue . '">' . $fieldValue . '</option>';
    						}
    					
    								
    				$petition_form .= '</select>
    				</div>';
                }
                if ( $petition->displays_custom_field6 == 1 && $petition->custom_field6_location == 3 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field6" id="dk-speakout-custom-field6-' . $petition->id . '" type="checkbox" ' . $custom_field6_required . ' /> <label for="dk-speakout-custom-field6-' . $petition->id . '">' .stripslashes( $petition->custom_field6_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field7 == 1 && $petition->custom_field7_location == 3 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field7" id="dk-speakout-custom-field7-' . $petition->id . '" type="checkbox" ' . $custom_field7_required . ' /> <label for="dk-speakout-custom-field7-' . $petition->id . '">' .stripslashes( $petition->custom_field7_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field8 == 1 && $petition->custom_field8_location == 3 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field8" id="dk-speakout-custom-field8-' . $petition->id . '" type="checkbox" ' . $custom_field8_required . '  /> <label for="dk-speakout-custom-field8-' . $petition->id . '">' .stripslashes( $petition->custom_field8_label ) . '</label>
    							</div>';
                }
                
                if ( $petition->displays_custom_field9 == 1 && $petition->custom_field9_location == 3 ) {
                    $petition_form .= '
    						<div class="dk-speakout-full">
    								<input name="dk-speakout-custom-field9" id="dk-speakout-custom-field9-' . $petition->id . '" type="checkbox" ' . $custom_field9_required . '  /> <label for="dk-speakout-custom-field9-' . $petition->id . '">' .stripslashes( $petition->custom_field9_label ) . '</label>
    							</div>';
                }
                $petition_form .= '</div>';
                
                // if we are displaying message and parsedown isn't available yet
                if ( !class_exists( 'Parsedown' ) ) {
                    include_once( 'parsedown.php' );
                }
                $Parsedown = new Parsedown();

                if ( $petition->is_editable == 1 && $petition->display_petition_message == 1 ) {
                   //to avoid clashing with other resources using parsedown
                        
                    $petition_form .= '
    							<div class="dk-speakout-full dk-speakout-message-editable" id="dk-speakout-message-editable-' . $petition->id . '">
    								<p class="dk-speakout-greeting">' . $petition->greeting . '</p>
    								<textarea name="dk-speakout-message" class="dk-speakout-message-' . $petition->id . '" ' . $height . ' rows="8">' .  wp_kses( $petition->petition_message, $kses_array )   . '</textarea>';
                    $petition_form .= "<div id='dk_speakout_markdown'>" . __( 'You can add formatting using markdown syntax' ) . " - <a href='https://www.markdownguide.org/basic-syntax/' target='_blank'>" . __( "read more" ) . "</a></div>";


                    //if there is a petition footer, show it as part of petition view
                    if ( $petition->petition_footer != '' ) {
                        $petition_form .= '<br><br>' . $petition->petition_footer;
                    }
                    $petition_form .= '</div>';
                } elseif ( $petition->display_petition_message == 1 ) {
                        //to avoid clashing with other resources using parsedown
                        
                        $petition_form .= '
    							<div class="dk-speakout-full dk-speakout-message" ' . $height . ' id="dk-speakout-message-' . $petition->id . '">
    								<p class="dk-speakout-greeting">' . $petition->greeting . '</p>
    								' .  $Parsedown->text( wp_kses( $petition->petition_message, $kses_array ) ) . '
    								<p class="dk-speakout-caps">%%' . __( 'your signature', 'speakout' ) . '%%</p>';


                        //if there is a petition footer, show it as part of petition view
                        if ( $petition->petition_footer != '' ) {
                            $petition_form .= '<br><br>' . $petition->petition_footer;
                        }
                        $petition_form .= '</div>';
                    }
                    // if not collecting email address, there is nothing to opt into even if enabled
                if ( $petition->displays_optin == 1 && $petition->hide_email_field != 1 ) {
                    $optin_default = ( $options[ 'optin_default' ] == 'checked' ) ? ' checked="checked"' : '';
                    $petition_form .= '
    							<div class="dk-speakout-optin-wrap" >
    								<div class="dk-speakout-optin-checkbox">
    								    <input type="checkbox" name="dk-speakout-optin"  id="dk-speakout-optin-' . $petition->id . '"' . $optin_default . ' />
    								    <label for="dk-speakout-optin-' . $petition->id . '" class="dk-speakout-options">' . stripslashes( esc_html( $petition->optin_label ) ) . '</label>
    							    </div>
    							</div>';
                }

                // if not collecting email address, there is nowhere to BCC even if enabled
                if ( $options[ 'display_bcc' ] == 'enabled' && $petition->hide_email_field != 1 ) {

                    $petition_form .= '
    							<div class="dk-speakout-bcc-wrap">
    								<div class="dk-speakout-options-checkbox">
    								    <input type="checkbox" name="dk-speakout-bcc" id="dk-speakout-bcc-' . $petition->id . '" checked="checked" />
    								    <label for="dk-speakout-bcc-' . $petition->id . '" class="dk-speakout-options">' . __( 'BCC yourself', 'speakout' ) . ' </label>
    								
    							    </div>
    							</div>';
                }


                // if publicly anonymous allowed, display option
                if ( $petition->allow_anonymous == 1 ) {
                    $petition_form .= '
    							<div class="dk-speakout-anonymise-wrap">
    								<div class="dk-speakout-options-checkbox">
    								    <input type="checkbox" name="dk-speakout-anonymise" id="dk-speakout-anonymise-' . $petition->id . '" value="1" />
    								    <label for="dk-speakout-anonymise-' . $petition->id . '" class="dk-speakout-options">' . __( 'Hide name from public', 'speakout' ) . ' </label>
    								
    							    </div>
    							</div>';
                }

                if ( $options[ 'display_privacypolicy' ] == 'enabled' ) {
                    $petition_form .= '
    							<div class="dk-speakout-privacypolicy-wrap">
    								<div class="dk-speakout-options-checkbox">
    								    <input type="checkbox" name="dk-speakout-privacypolicy" id="dk-speakout-privacypolicy-' . $petition->id . '" class="required" />
    								    <label for="dk-speakout-privacypolicy-' . $petition->id . '" class="required dk-speakout-options">' . __( 'Yes, I accept your ', 'speakout' ) . '<a href="' . $options[ 'privacypolicy_url' ] . '" target="_blank">' . __( 'privacy policy', 'speakout' ) . '</a></label>
    								</div>
    							</div>';
                }

                //recaptcha but not for version 3
                if ( isset( $options[ 'g_recaptcha_status' ] ) && $options[ 'g_recaptcha_status' ] == 'on' && $options[ 'g_recaptcha_version' ] != 3 ) {
                    $petition_form .= '<div class="dk-speakout-recaptcha">
                            <div class="g-recaptcha" data-sitekey="' . $options[ "g_recaptcha_site_key" ] . '"></div>
                                <br/></div>';
                }
                
                //hCaptcha
                if ( isset( $options[ 'hcaptcha_status' ] ) && $options[ 'hcaptcha_status' ] == 'on' ) {
                    $petition_form .= '<div class="dk-speakout-hcaptcha">
                            <div class="h-captcha" data-sitekey="' . $options[ "hcaptcha_site_key" ] . '"></div>
                                <br/></div>';
                }
                
                if ( !is_user_logged_in() ) {
                    $petition_form .= '<p class="dk-speakout-login-hint">' . sprintf( __( 'Have an account? %s for faster signing next time.', 'speakout' ), '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . __( 'Sign in', 'speakout' ) . '</a>' ) . '</p>';
                }

                if ( $petition->display_petition_message == 1 ) {
                    $read_href = dk_speakout_petition_hub_url( array( 'petition' => $petition->id ) );
                    $petition_form .= '<div class="dk-speakout-readme-before-submit"><a id="dk-speakout-readme-' . $petition->id . '" class="dk-speakout-readme" href="' . esc_url( $read_href ) . '"><span>' . esc_html( __( $petitionReadTitle, 'speakout' ) ) . '</span></a></div>';
                }

                $petition_form .= '
                            <div class="dk-speakout-submit-wrap">
                                <div id="dk-speakout-ajaxloader-' . $petition->id . '" class="dk-speakout-ajaxloader" style="visibility: hidden;">&nbsp;</div>
                                <button name="' . $petition->id . '" class="dk-speakout-submit">' . __( 'Sign Petition', 'speakout' ) . '</button>
                            </div>';

                $petition_form .= ' </form>';
                $petition_form .= '</div>';
                $petition_form .= '<div class="dk-speakout-response"></div>';

                //display or hide sharing icons
                if ( $options[ 'display_sharing' ] == "enabled" || $options[ 'display_sharing' ] == "on") {
                    $petition_form .= '
						<div class="dk-speakout-share">
							<div>
                                 <p>' . stripslashes( esc_html( $options[ 'share_message' ] ) ) . '</p>
							     <p>
                                    <a class="dk-speakout-facebook" href="#" title="Facebook" rel="' . $petition->id . '"></a>
                                    <a class="dk-speakout-email"  target="_blank" href="mailto:?subject=Petition: ' . esc_html( $petition->title ) .'&amp;body=Hi there, I want to share this petition titled %22' .esc_html( $petition->title )  . '%22 with you: https://'  .  $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] .  '" title="Share by Email"></a>
                                    <a class="dk-speakout-x" href="#" title="X" rel="' . $petition->id . '"></a>
                                    <a class="dk-speakout-copy-link" href="#" title="' . esc_attr__( 'Copy link', 'speakout' ) . '" rel="' . $petition->id . '">' . esc_html__( 'Copy link', 'speakout' ) . '</a>
							     </p>
						    </div>
							<div class="dk-speakout-clear"></div>
						</div>';
                }
                $petition_form .= '</div>';
            }
            // petition has expired
            else {
                $goal_text = ( $petition->goal != 0 ) ? '<p><strong>' . __( 'Signature goal', 'speakout' ) . ':</strong> ' . $petition->goal . '</p>': '';
                $petition_form = '
					<div class="dk-speakout-petition-wrap dk-speakout-expired" id="dk-speakout-petition-' . $petition->id . '">
						<h3>' . stripslashes( esc_html( $petition->title ) ) . '</h3>
						<p>' . stripslashes( esc_html( $options[ 'expiration_message' ] ) ) . '</p>
						<p><strong>' . __( 'End date', 'speakout' ) . ':</strong> ' . date( 'M d, Y', strtotime( $petition->expiration_date ) ) . '</p>
						<p><strong>' . __( 'Signatures collected', 'speakout' ) . ':</strong> ' . number_format(  $petition->signatures, 0 , $options[ 'decimal_separator' ] , $options[ 'thousands_separator' ] )  . '</p>
						' . $goal_text . '
						<div class="dk-speakout-progress-wrap">
							<div class="dk-speakout-signature-count">';
                $petition_form .= '<span>' . number_format(  $petition->signatures, 0 , $options[ 'decimal_separator' ] , $options[ 'thousands_separator' ] )  . '</span> ';
                $petition_form .= _n( 'signature', 'signatures', number_format(  $petition->signatures, 0 , $options[ 'decimal_separator' ] , $options[ 'thousands_separator' ] ) , 'speakout' ) . $goal_text;
                $petition_form .= '</div>' .
                dk_speakout_SpeakOut::progress_bar( $petition->goal, $petition->signatures, $progress_width ) . '
                        </div>
					</div>';
            }

        }
        // petition doesn't exist
        else {
            $petition_form = '';
        }
    }

    // id attribute was left out, as in [emailpetition]
    else {
        $petition_form = '
			<div class="dk-speakout-petition-wrap dk-speakout-expired">
				<h3>' . __( 'Petition', 'speakout' ) . '</h3>
				<div class="dk-speakout-notice">
					<p>' . __( 'Error: The site administrator must include a valid petition id  in the shortcode.', 'speakout' ) . '</p>
				</div>
			</div>';
    }

    return $petition_form;
}

add_shortcode( 'signaturemanage', 'dk_speakout_signaturemanage_shortcode' );
function dk_speakout_signaturemanage_shortcode() {
    $code = isset( $_GET['dkspeakoutmanage'] ) ? sanitize_text_field( $_GET['dkspeakoutmanage'] ) : '';
    $email = isset( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';
    $html  = '<div class="dk-speakout-signature-manage">';
    $html .= '<h3>' . __( 'Manage your signature', 'speakout' ) . '</h3>';
    $html .= '<p>' . __( 'Use this form to update your comment or remove your signature.', 'speakout' ) . '</p>';
    $html .= '<input type="email" id="dk-speakout-manage-email" placeholder="' . esc_attr__( 'Your email', 'speakout' ) . '" value="' . esc_attr( $email ) . '" />';
    $html .= '<input type="text" id="dk-speakout-manage-code" placeholder="' . esc_attr__( 'Manage code', 'speakout' ) . '" value="' . esc_attr( $code ) . '" />';
    $html .= '<textarea id="dk-speakout-manage-message" rows="5" placeholder="' . esc_attr__( 'Updated comment', 'speakout' ) . '"></textarea>';
    $html .= '<div class="dk-speakout-signature-manage-actions"><button type="button" class="dk-speakout-manage-update">' . esc_html__( 'Update signature', 'speakout' ) . '</button> <button type="button" class="dk-speakout-manage-delete">' . esc_html__( 'Remove signature', 'speakout' ) . '</button></div>';
    $html .= '<div class="dk-speakout-manage-response"></div></div>';
    return $html;
}

/**
 * Detect SpeakOut shortcodes in post body, manual excerpt, or Elementor JSON.
 *
 * @param WP_Post $post Post object.
 * @return bool
 */
function dk_speakout_post_may_use_speakout_shortcodes( $post ) {
    if ( ! $post instanceof WP_Post ) {
        return false;
    }
    $chunks   = array( $post->post_content, $post->post_excerpt );
    $el       = get_post_meta( $post->ID, '_elementor_data', true );
    if ( is_string( $el ) && $el !== '' ) {
        $chunks[] = $el;
    }
    $needles = array( '[emailpetition', '[signaturemanage', '[speakout_', '[signaturecount', '[signaturegoal', '[petitiontitle', '[petitionmessage' );
    foreach ( $chunks as $chunk ) {
        if ( ! is_string( $chunk ) || $chunk === '' ) {
            continue;
        }
        foreach ( $needles as $n ) {
            if ( strpos( $chunk, $n ) !== false ) {
                return true;
            }
        }
    }
    return false;
}

// load public CSS/JS on pages that use SpeakOut shortcodes (including inside Elementor data).
add_filter( 'the_posts', 'dk_speakout_public_css_js' );

function dk_speakout_public_css_js( $posts ) {
    if ( empty( $posts ) ) {
        return $posts;
    }

    foreach ( $posts as $post ) {
        if ( dk_speakout_post_may_use_speakout_shortcodes( $post ) ) {
            dk_speakout_enqueue_public_petition_assets();
            break;
        }
    }

    return $posts;
}

?>