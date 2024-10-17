<?php
/*
Plugin Name: WP-Paginate
Plugin URI: https://wordpress.org/plugins/wp-paginate/
Description: A simple and flexible pagination plugin for WordPress posts and comments.
Version: 2.2.2
Author: Max Foundry
Author URI: http://maxfoundry.com
Text Domain: 'wp-paginate'
*/

/*  Copyright 2016 Max Foundry (http://www.maxfoundry.com)

    Plugin originally created by Eric Martin (http://www.ericmmartin.com) and
    and improved upon by Studio Fuels.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// plugin links
if(!defined('WPP_MAXBUTTONS_LINK'))
	define('WPP_MAXBUTTONS_LINK', 'https://maxbuttons.com/?utm_source=wpp-descv2&utm_medium=wpp-plugin&utm_content=wpp-footer&utm_campaign=wpp-descv2');
if(!defined('WPP_MAXGALLERIA_LINK'))
	define('WPP_MAXGALLERIA_LINK', 'https://maxgalleria.com/?utm_source=wpp-descv2&utm_medium=wpp-plugin&utm_content=wpp-footer&utm_campaign=wpp-descv2');
if(!defined('WPP_MEDIA_LIBRARY_PLUS_PRO_LINK'))
	define('WPP_MEDIA_LIBRARY_PLUS_PRO_LINK', 'https://maxgalleria.com/downloads/media-library-plus-pro/?utm_source=wpp-descv2&utm_medium=wpp-plugin&utm_content=wpp-footer&utm_campaign=wpp-descv2');
if(!defined('WPP_WP_PAGINATE_PRO_LINK'))
  define("WPP_WP_PAGINATE_PRO_LINK", "https://maxgalleria.com/downloads/wp-paginate-pro/");

  define("WPP_REVIEW_NOTICE", "wpp_review_notice");
	if(!defined("WP_PAGINATE_NONCE"))
    define("WP_PAGINATE_NONCE", "wpp_js_nonce");

/**
 * Set the wp-content and plugin urls/paths
 */
if (!defined('WP_CONTENT_URL'))
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL') )
    define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
if (!defined('WP_PLUGIN_DIR') )
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

if (!class_exists('WPPaginate')) {
    class WPPaginate {
        /**
         * @var string The plugin version
         */
        public $version = '2.2.2';

        /**
         * @var string The options string name for this plugin
         */
        public $optionsName = 'wp_paginate_options';

        /**
         * @var string $localizationDomain Domain used for localization
         */
        public $localizationDomain = 'wp-paginate';

        /**
         * @var string $pluginurl The url to this plugin
         */
        public $pluginurl = '';
        /**
         * @var string $pluginpath The path to this plugin
         */
        public $pluginpath = '';

        /**
         * @var array $options Stores the options for this plugin
         */
        public $options = array();

        public $type = 'posts';
				
				public $positions = array();

				public $fonts = array();
				
				public $presets = array();
        
				/**
         * Constructor
         */
        function __construct() {
            $name = dirname(plugin_basename(__FILE__));
						$this->set_activation_hooks();
						
            //Language Setup
            load_plugin_textdomain('wp-paginate', false, "$name/I18n/");

            //"Constants" setup
            $this->pluginurl = plugins_url($name) . "/";
            $this->pluginpath = WP_PLUGIN_DIR . "/$name/";

            //Initialize the options
            $this->get_options();

            //Actions
            add_action( 'init', array(&$this, 'init_pagination'));						
            add_action('admin_menu', array(&$this, 'admin_menu_link'));
            add_action('admin_enqueue_scripts', array($this, 'wpp_admin_head'));
						add_action('wp_enqueue_scripts', array($this, 'wpp_enqueue_custom_css'), 20);
						
						
            add_action('admin_notices', array($this, 'show_wpp_admin_notice'));
						
						add_action('wp_ajax_nopriv_wpp_dismiss_notice', array($this, 'wpp_dismiss_notice'));
						add_action('wp_ajax_wpp_dismiss_notice', array($this, 'wpp_dismiss_notice'));				
						
						add_action('wp_ajax_nopriv_wpp_set_review_later', array($this, 'wpp_set_review_later'));
						add_action('wp_ajax_wpp_set_review_later', array($this, 'wpp_set_review_later'));																					
						
						$this->positions = array(
							'none' => esc_html__('Function only', 'wp-paginate'),
							'below' => esc_html__('Below the Content', 'wp-paginate'),
							'above' => esc_html__('Above the Content', 'wp-paginate'),
							'both' => esc_html__('Above and Below the Content', 'wp-paginate')
						);
						
						$this->fonts = array(
							'font-inherit' => esc_html__('inherit', 'wp-paginate'),
							'font-initial' => esc_html__('initial', 'wp-paginate'),
							'font-arial' => esc_html__('Arial', 'wp-paginate'),
							'font-georgia' => esc_html__('Georgia', 'wp-paginate'),
							'font-tahoma' => esc_html__('Tahoma', 'wp-paginate'),
							'font-times' => esc_html__('Times New Roman', 'wp-paginate'),
							'font-trebuchet' => esc_html__('Trebuchet MS', 'wp-paginate'),
							'font-verdana' => esc_html__('Verdana', 'wp-paginate')
						);
						
						$this->presets = array(
							array('default', esc_html__('Grey Buttons', 'wp-paginate'), 'default.jpg'),
							array('wpp-blue-cta', esc_html__('Blue Buttons', 'wp-paginate'), 'blue-cta-buttons.jpg'),
							array('wpp-modern-grey', esc_html__('Modern Grey Buttons', 'wp-paginate'), 'modern-grey-buttons.jpg'),
							array('wpp-neon-pink', esc_html__('Neon Pink Buttons', 'wp-paginate'), 'neon-pink-buttons.png'),
						);
												
            if ($this->options['css'])
                add_action('wp_print_styles', array(&$this, 'wp_paginate_css'));
        }
				
					public function set_activation_hooks() {
						register_activation_hook(__FILE__, array($this, 'do_activation'));
						register_deactivation_hook(__FILE__, array($this, 'do_deactivation'));
					}

					public function do_activation() {
						$this->activate();
					}

					public function do_deactivation() {	
						$this->deactivate();
					}
										
					public function activate() {
						$current_user_id = get_current_user_id(); 

						if(get_user_meta( $current_user_id, WPP_REVIEW_NOTICE, true ) !== "off" ) {
						  $review_date = date('Y-m-d', strtotime("+2 days"));
							update_user_meta( $current_user_id, WPP_REVIEW_NOTICE, $review_date );
						}	
					}
				
					public function deactivate() {
					}
				
					public function init_pagination() {						

						if(isset($this->options['everywhere']) && isset($this->options['position'])) {
						
							if ($this->options['position'] === 'above' || $this->options['position'] === 'both')
								add_filter( 'loop_start', array($this, 'add_pagination_to_page_top'));

							if ($this->options['position'] === 'below' || $this->options['position'] === 'both')
							add_filter( 'loop_end', array($this, 'add_pagination_to_page_bottom'));

							if ($this->options['everywhere'] && ($this->options['position'] === 'below' || $this->options['position'] === 'both'))
								add_filter( 'wp_link_pages', array($this, 'add_pagination_to_page_bottom'), 10, 2 );

							if ($this->options['everywhere'] && ($this->options['position'] === 'above' || $this->options['position'] === 'both'))
								add_filter( 'wp_link_pages', array($this, 'add_pagination_to_page_top'), 10, 2 );
						
						}
																		
					}
										
					function add_pagination_to_page_bottom($content) {
						
						$this->get_options();
						if(defined('WPP-DEBUG')) {
						  $output = print_r($this->options, true);
						  error_log($output);
						}
						if( ($this->options['home-page'] && is_front_page()) || 
								($this->options['blog-page'] && is_home()) ||
								($this->options['search-page'] && is_search()) ||		
								($this->options['category-page'] && is_category()) ||
								($this->options['archive-page'] && is_archive()) ||
								($this->options['everywhere'])												
							) {
													
							if ( is_feed() )
									return $content;
							global $wp_query, $wp_paginate_display_bottom;
							if ( is_main_query() && $content === $wp_query && ! $wp_paginate_display_bottom ) { 
								$wp_paginate_display_bottom = true;
								wp_paginate();
							}						
						}
					}
					
					function add_pagination_to_page_top($content) {
						
						$this->get_options();
						if(defined('WPP-DEBUG')) {
						  $output = print_r($this->options, true);
						  error_log($output);
						}
						if( ($this->options['home-page'] && is_front_page()) || 
								($this->options['blog-page'] && is_home()) ||
								($this->options['search-page'] && is_search()) ||		
								($this->options['category-page'] && is_category()) ||
								($this->options['archive-page'] && is_archive()) ||
								($this->options['everywhere'])												
							) {
													
							if ( is_feed() )
									return $content;
							global $wp_query, $wp_paginate_display_top;
							if ( is_main_query() && $content === $wp_query && ! $wp_paginate_display_top ) { 
								$wp_paginate_display_top = true;
								wp_paginate();
							}						
						}
					}
													
				function wpp_admin_head($hook) {
					if ( isset( $_REQUEST['page'] ) && 'wp-paginate.php' == $_REQUEST['page'] ) {
            //wp_enqueue_style( 'bootstrap', plugins_url( '/css/bootstrap/bootstrap.min.css', __FILE__ ));
						if ( isset( $_GET['action'] ) && 'custom_css' == $_GET['action'] )
							$this->wpp_include_codemirror();
					}
          wp_enqueue_style( 'wp-paginate-admin', plugins_url( '/css/wp-paginate-admin.css', __FILE__ ));
					
					wp_enqueue_script('jquery');
          wp_enqueue_script('jquery-migrate', ABSPATH . WPINC . '/js/jquery/jquery-migrate.min.js', array('jquery'));                      
					wp_register_script( 'wpp-script', $this->pluginurl .'js/wp-paginate.js', array( 'jquery' ), '', true );

					wp_localize_script( 'wpp-script', 'wpp_ajax', 
								array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
											 'nonce'=> wp_create_nonce(WP_PAGINATE_NONCE))
										 ); 

					wp_enqueue_script('wpp-script');
					
				}
				
				function wpp_enqueue_custom_css() {
		      $wpp_custom_css_active = get_option('wpp_custom_css_active', 'off');
					if($wpp_custom_css_active === 'on') {						
						$upload_dir = wp_upload_dir();
		        $wpp_css_file = $upload_dir['baseurl'] . '/wpp-custom-code/wpp-custom-code.css';						
				    wp_enqueue_style( 'wpp-custom-style', $wpp_css_file,  array('wp-paginate') );					
					}	
				}
				
        /**
         * Pagination based on options/args
         */
        function paginate($args = false) {
          
          
            if ($this->type === 'comments' && !get_option('page_comments'))
                return;

            $r = wp_parse_args($args, $this->options);
            extract($r, EXTR_SKIP);
            //error_log("r " . print_r($r, true));
						
						if(isset($this->options['preset']))
						  $preset = $this->options['preset'];
						else
							$preset = 'default';
						
						if(isset($this->options['font']))
						  $font = $this->options['font'];
						else
						  $font = 'font-inherit';
						
						if(isset($before))
							$before_option = $before;
						else if(isset($this->options['before']))
							$before_option = $this->options['before'];
						else
						  $before_option = '<div class="navigation">';
						
						if(isset($after))
							$after_option = $after;
						else if(isset($this->options['after']))
							$after_option = $this->options['after'];
						else
						  $after_option = '</div>';						
            
						if(isset($this->options['slash']))
							$slash_option = $this->options['slash'];
						else
						  $slash_option = false;
            												
						$before = stripslashes(html_entity_decode($before_option));
						$after = stripslashes(html_entity_decode($after_option));

            if (!isset($page) && !isset($pages)) {
                global $wp_query;

                if ($this->type === 'posts') {
                    $page = get_query_var('paged');
                    $posts_per_page = intval(get_query_var('posts_per_page'));
                    $pages = intval(ceil($wp_query->found_posts / $posts_per_page));
                }
                else {
                    $page = get_query_var('cpage');
                    $comments_per_page = get_option('comments_per_page');
                    $pages = get_comment_pages_count();
                }
                $page = !empty($page) ? intval($page) : 1;
            }

            $prevlink = ($this->type === 'posts')
                ? rtrim(esc_url(get_pagenum_link($page - 1)), '/')
                : get_comments_pagenum_link($page - 1);
            $nextlink = ($this->type === 'posts')
                ? rtrim(esc_url(get_pagenum_link($page + 1)), '/')
                : get_comments_pagenum_link($page + 1);
            
            //if($slash_option == true) {
            //   $prevlink . '/';
            //   $nextlink . '/';
            //}
						
            $output = stripslashes(wp_kses_decode_entities($before));
            if ($pages > 1) {
							
							if($preset === 'default')
                $output .= sprintf('<ol class="wp-paginate ' . $font . '%s">', ($this->type === 'posts') ? '' : ' wp-paginate-comments');
							else
                $output .= sprintf('<ol class="wp-paginate ' . $preset . ' ' . $font . '%s">', ($this->type === 'posts') ? '' : ' wp-paginate-comments');
							
                if (strlen(stripslashes($title)) > 0) {
                    $output .= sprintf('<li><span class="title">%s</span></li>', stripslashes($title));
                }
                
                if(isset($this->options['hide-ellipses']) && $this->options['hide-ellipses'] == true )
                  $ellipsis = "<li class='ellipse-gap'></li>";
                else
                  $ellipsis = "<li><span class='gap'>...</span></li>";

                if ($page > 1 && !empty($previouspage)) {
                  $prevlink = rtrim($prevlink, '/');      
                  $contains_param = (strpos($prevlink, '?') !== false) ? true : false;                        
                  if($slash_option && !$contains_param)
                    $output .= sprintf('<li><a href="%s/" class="prev" aria-label="' . esc_html__('Go to previous page', 'wp-paginate') . '">%s</a></li>', $prevlink, stripslashes($previouspage));
                  else
                    $output .= sprintf('<li><a href="%s" class="prev" aria-label="' . esc_html__('Go to previous page', 'wp-paginate') . '">%s</a></li>', $prevlink, stripslashes($previouspage));
                }

                $min_links = $range * 2 + 1;
                $block_min = min($page - $range, $pages - $min_links);
                $block_high = max($page + $range, $min_links);
                $left_gap = (($block_min - $anchor - $gap) > 0) ? true : false;
                $right_gap = (($block_high + $anchor + $gap) < $pages) ? true : false;

                if ($left_gap && !$right_gap) {
                    $output .= sprintf('%s%s%s',
                        $this->paginate_loop(1, $anchor, 0, 0, $slash_option),
                        $ellipsis,
                        $this->paginate_loop($block_min, $pages, $page, $page, $slash_option)
                    );
                }
                else if ($left_gap && $right_gap) {
                    $output .= sprintf('%s%s%s%s%s',
                        $this->paginate_loop(1, $anchor, 0, 0, $slash_option),
                        $ellipsis,
                        $this->paginate_loop($block_min, $block_high, $page, $page, $slash_option),
                        $ellipsis,
                        $this->paginate_loop(($pages - $anchor + 1), $pages, 0, 0, $slash_option)
                    );
                }
                else if ($right_gap && !$left_gap) {
                    $output .= sprintf('%s%s%s',
                        $this->paginate_loop(1, $block_high, $page, $page, $slash_option),
                        $ellipsis,
                        $this->paginate_loop(($pages - $anchor + 1), $pages, 0, 0, $slash_option)
                    );
                }
                else {
                    $output .= $this->paginate_loop(1, $pages, $page, $page, $slash_option);
                }
                
                if ($page < $pages && !empty($nextpage)) {
                  $nextlink = rtrim($nextlink, '/');      
                  $contains_param = (strpos($nextlink, '?') !== false) ? true : false;      
                  if($slash_option && !$contains_param)
                    $output .= sprintf('<li><a href="%s/" class="next" aria-label="' . esc_html__('Go to next page', 'wp-paginate') . '">%s</a></li>', $nextlink, stripslashes($nextpage));
                  else
                    $output .= sprintf('<li><a href="%s" class="next" aria-label="' . esc_html__('Go to next page', 'wp-paginate') . '">%s</a></li>', $nextlink, stripslashes($nextpage));
                }
                $output .= "</ol>";
            }
            
            $output .= stripslashes(wp_kses_decode_entities($after));

            if ($pages > 1 || $empty) {
                echo $output;
            }
        }

        /**
         * Helper function for pagination which builds the page links.
         */
        function paginate_loop($start, $max, $page = 0, $slash = false) {
            $output = "";
            for ($i = $start; $i <= $max; $i++) {
              if($slash)
                $p = ($this->type === 'posts') ? esc_url(get_pagenum_link($i)) : get_comments_pagenum_link($i);
              else
                $p = ($this->type === 'posts') ? rtrim(esc_url(get_pagenum_link($i)), '/') : get_comments_pagenum_link($i);
              $output .= ($page == intval($i))
                  ? "<li><span class='page current'>$i</span></li>"
                  : "<li><a href='$p' title='$i' aria-label='" . esc_html__('Go to page', 'wp-paginate') . " $i' class='page'>$i</a></li>";
                  //: "<li><a href='$p' title='$i' aria-label='Go to page $i' class='page'>$i</a></li>";
            }     
            return $output;
        }

        function wp_paginate_css() {
            $name = "css/wp-paginate.css";

            if (false !== @file_exists(STYLESHEETPATH . "/$name")) {
                $css = get_stylesheet_directory_uri() . "/$name";
            }
            else {
                $css = $this->pluginurl . $name;
            }
            wp_enqueue_style('wp-paginate', $css, false, $this->version, 'screen');

            if (function_exists('is_rtl') && is_rtl()) {
                $name = "css/wp-paginate-rtl.css";
                if (false !== @file_exists(STYLESHEETPATH . "/$name")) {
                    $css = get_stylesheet_directory_uri() . "/$name";
                }
                else {
                    $css = $this->pluginurl . $name;
                }
                wp_enqueue_style('wp-paginate-rtl', $css, false, $this->version, 'screen');
            }
						
		        wp_enqueue_style( 'wpp-advanced-styles', $this->wpp_print_style() );
						
        }

        /**
         * Retrieves the plugin options from the database.
         * @return array
         */
        function get_options() {
            if (!$options = get_option($this->optionsName)) {
                $options = array(
                    'title' => 'Pages:',
                    'nextpage' => '&raquo;',
                    'previouspage' => '&laquo;',
                    'css' => true,
                    'slash' => false,
                    'before' => '<div class="navigation">',
                    'after' => '</div>',
                    'empty' => true,
                    'range' => 3,
                    'anchor' => 1,
                    'gap' => 3,
										'everywhere' => false,
										'home-page' => false,
										'blog-page' => false,
										'search-page' => false,
										'category-page' => false,
										'archive-page' => false,
										'position' => 'none',
										'hide-standard-pagination' => false,
                    'hide-ellipses'  => false,
										'font' => 'font-inherit',
										'preset' => 'default'
                );
                update_option($this->optionsName, $options);
            }
            $this->options = $options;
        }
        /**
         * Saves the admin options to the database.
         */
        function save_admin_options(){
					
						if(defined('WPP-DEBUG')) {
						  $output = print_r($this->options, true);
						  error_log($output);
						}
					
            return update_option($this->optionsName, $this->options);
        }

        /**
         * @desc Adds the options subpanel
         */
        function admin_menu_link() {
            add_options_page('WP-Paginate', 'WP-Paginate', 'manage_options', basename(__FILE__), array(&$this, 'admin_options_page'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
        }

        /**
         * @desc Adds the Settings link to the plugin activate/deactivate page
         */
        function filter_plugin_actions($links, $file) {
            $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . esc_html__('Settings', 'wp-paginate') . '</a>';
            array_unshift($links, $settings_link); // before other links

            return $links;
        }

        /**
         * Adds settings/options page
         */
        function admin_options_page() {
          
            $allowed_html = array(
                  'div' => array(
                      'class' => array(),
                      'id' => array()
                  ),                
                  'nav' => array(
                      'class' => array(),
                      'id' => array()                      
                  ),
                  'em' => array(),
                  'strong' => array()
              );          
            
            $allowed_presets = array('default', 'wpp-blue-cta', 'wpp-modern-grey', 'wpp-neon-pink');
          
            if (isset($_POST['wp_paginate_save'])) {
                if (wp_verify_nonce($_POST['_wpnonce'], 'wp-paginate-update-options')) {
									
									if(defined('WPP-DEBUG')) {
										error_log("wp_paginate_save");
									}	
									
										$this->options['title'] = trim(sanitize_text_field($_POST['title']));
										$this->options['previouspage'] = trim(sanitize_text_field($_POST['previouspage']));
										$this->options['nextpage'] = trim(sanitize_text_field($_POST['nextpage']));
                    $this->options['before'] = wp_kses($_POST['before'], $allowed_html);
                    $this->options['after'] = wp_kses($_POST['after'], $allowed_html);
                    $this->options['empty'] = (isset($_POST['empty']) && $_POST['empty'] === 'on') ? true : false;
                    $this->options['css'] = (isset($_POST['css']) && $_POST['css'] === 'on') ? true : false;
                    $this->options['slash'] = (isset($_POST['slash']) && $_POST['slash'] === 'on') ? true : false;
                    $this->options['range'] = intval($_POST['range']);
                    $this->options['anchor'] = intval($_POST['anchor']);
                    $this->options['gap'] = intval($_POST['gap']);
                    $this->options['everywhere'] = (isset($_POST['everywhere']) && $_POST['everywhere'] === 'on') ? true : false;
                    $this->options['home-page'] = (isset($_POST['home-page']) && $_POST['home-page'] === 'on') ? true : false;
                    $this->options['blog-page'] = (isset($_POST['blog-page']) && $_POST['blog-page'] === 'on') ? true : false;
                    $this->options['search-page'] = (isset($_POST['search-page']) && $_POST['search-page'] === 'on') ? true : false;
                    $this->options['category-page'] = (isset($_POST['category-page']) && $_POST['category-page'] === 'on') ? true : false;
                    $this->options['archive-page'] = (isset($_POST['archive-page']) && $_POST['archive-page'] === 'on') ? true : false;
										if(isset($_POST['position']))
                      $this->options['position'] = sanitize_text_field($_POST['position']);
                    $this->options['hide-standard-pagination'] = (isset($_POST['hide-standard-pagination']) && $_POST['hide-standard-pagination'] === 'on') ? true : false;
                    $this->options['hide-ellipses'] = (isset($_POST['hide-ellipses']) && $_POST['hide-ellipses'] === 'on') ? true : false;
										if(isset($_POST['font']))
                      $this->options['font'] = sanitize_text_field($_POST['font']);	
										if(isset($_POST['preset'])) {
                      $preset_value = trim(sanitize_text_field($_POST['preset']));
                      // only allow predefined values
                      if(in_array($preset_value, $allowed_presets))
                        $this->options['preset'] = $preset_value;
                      else
                        $this->options['preset'] = 'default';
                    }  
										
                    $this->save_admin_options();

                    echo '<div class="updated"><p>' . esc_html__('Success! Your changes were successfully saved!', 'wp-paginate') . '</p></div>';
                }
                else {
                    echo '<div class="error"><p>' . esc_html__('Whoops! There was a problem with the data you posted. Please try again.', 'wp-paginate') . '</p></div>';
                }
            }
												
?>

<div class="wrap">
<div class="icon32" id="icon-options-general"><br/></div>
<h1>WP-Paginate</h1>
<h2 class="nav-tab-wrapper">
	<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="options-general.php?page=wp-paginate.php"><?php esc_html_e( 'Settings', 'wp-paginate' ); ?></a>
	<a class="nav-tab <?php if ( isset( $_GET['action'] ) && 'custom_css' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="options-general.php?page=wp-paginate.php&amp;action=custom_css"><?php esc_html_e( 'Custom CSS', 'wp-paginate' ); ?></a>
	<a class="nav-tab <?php if ( isset( $_GET['action'] ) && 'upgrade_to_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="options-general.php?page=wp-paginate.php&amp;action=upgrade_to_pro"><?php esc_html_e( 'Upgrade to Pro', 'wp-paginate' ); ?></a>
</h2>

<?php if ( ! isset( $_GET['action'] ) || $_GET['action'] == 'appearance' ) { ?>

<form method="post" id="wp_paginate_options">
<?php wp_nonce_field('wp-paginate-update-options'); ?>
	
    <h3><?php esc_html_e('General', 'wp-paginate'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Pagination Label:', 'wp-paginate'); ?></th>
            <td><input name="title" type="text" id="title" size="40" value="<?php echo esc_attr(stripslashes(htmlspecialchars($this->options['title']))) ?>"/>							
            <span class="description"><?php esc_html_e('The optional text/HTML to display before the list of pages.', 'wp-paginate'); ?></span></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Previous Page:', 'wp-paginate'); ?></th>
            <td><input name="previouspage" type="text" id="previouspage" size="40" value="<?php echo esc_attr(stripslashes(htmlspecialchars($this->options['previouspage']))) ?>"/>
            <span class="description"><?php esc_html_e('The text/HTML to display for the previous page link.', 'wp-paginate'); ?></span></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Next Page:', 'wp-paginate'); ?></th>
            <td><input name="nextpage" type="text" id="nextpage" size="40" value="<?php echo esc_attr(stripslashes(htmlspecialchars($this->options['nextpage']))) ?>"/>
            <span class="description"><?php esc_html_e('The text/HTML to display for the next page link.', 'wp-paginate'); ?></span></td>
        </tr>
    </table>
    <p>&nbsp;</p>
    <h3><?php esc_html_e('Location &amp; Position', 'wp-paginate'); ?></h3>
    <table class="form-table">		
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Everywhere:', 'wp-paginate'); ?></th>
            <td><label for="everywhere">
						<?php 
						  if(isset($this->options['everywhere']))
						    $everywhere = $this->options['everywhere'];
							else
								$everywhere = false;
						?>
                <input class="everywhere-cb" type="checkbox" id="everywhere" name="everywhere" <?php echo esc_attr(($everywhere === true) ? "checked='checked'" : ""); ?>/> <?php esc_html_e('Display pagination everywhere. (Comments pagination not included.)', 'wp-paginate') ?></label></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Home Page:', 'wp-paginate'); ?></th>
            <td><label for="home-page">
						<?php 
						  if(isset($this->options['home-page']))
						    $home_page = $this->options['home-page'];
							else
								$home_page = false;
						?>																
                <input class="not-everywhere-cb" type="checkbox" id="home-page" name="home-page" <?php echo esc_attr(($home_page === true) ? "checked='checked'" : "") ?>/> <?php esc_html_e('Display pagination on the home page.', 'wp-paginate') ?></label></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Blog Page:', 'wp-paginate'); ?></th>
            <td><label for="blog-page">
						<?php 
						  if(isset($this->options['blog-page']))
						    $blog_page = $this->options['blog-page'];
							else
								$blog_page = false;
						?>																								
            <input class="not-everywhere-cb" type="checkbox" id="blog-page" name="blog-page" <?php echo esc_attr(($blog_page === true)) ? "checked='checked'" : ""; ?>/> <?php esc_html_e('Display pagination on the blog page.', 'wp-paginate') ?></label></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Search Page:', 'wp-paginate'); ?></th>
            <td><label for="search-page">								
						<?php 
						  if(isset($this->options['search-page']))
						    $search_page = $this->options['search-page'];
							else
								$search_page = false;
						?>																																
                <input class="not-everywhere-cb" type="checkbox" id="search-page" name="search-page" <?php echo esc_attr(($search_page === true) ? "checked='checked'" : "") ?>/> <?php esc_html_e('Display pagination on the search page.', 'wp-paginate') ?></label></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Category Pages:', 'wp-paginate'); ?></th>
            <td><label for="category-page">
						<?php 
						  if(isset($this->options['category-page']))
						    $category_page = $this->options['category-page'];
							else
								$category_page = false;
						?>																																
                <input class="not-everywhere-cb" type="checkbox" id="search-page" name="category-page" <?php echo esc_attr(($category_page === true) ? "checked='checked'" : "") ?>/> <?php esc_html_e('Display pagination on category pages.', 'wp-paginate') ?></label></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Archive Pages:', 'wp-paginate'); ?></th>
            <td><label for="archive-page">
						<?php 
						  if(isset($this->options['archive-page']))
						    $archive_page = $this->options['archive-page'];
							else
								$archive_page = false;
						?>																																								
                <input class="not-everywhere-cb" type="checkbox" id="archive-page" name="archive-page" <?php echo esc_attr(($archive_page === true) ? "checked='checked'" : "") ?>/> <?php esc_html_e('Display pagination on archive pages.', 'wp-paginate') ?></label></td>
        </tr>
				
				<script>
					jQuery(document).ready(function(){
						
		        jQuery(".everywhere-cb").change(function() {
							if(jQuery(".everywhere-cb").is(':checked')) {
								jQuery(".not-everywhere-cb").prop('disabled', true);
                jQuery(".not-everywhere-cb").prop("checked", false);
							} else {
								jQuery(".not-everywhere-cb").prop('disabled', false);								
							}
					  });  
						
						if(jQuery(".everywhere-cb").is(':checked')) {
							jQuery(".not-everywhere-cb").prop('disabled', true);
						} else {
							jQuery(".not-everywhere-cb").prop('disabled', false);								
						}

					});  
				</script>  
				
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Position:', 'wp-paginate'); ?></th>
            <td>							
								<?php 
									if(isset($this->options['position']))
										$position = $this->options['position'];
									else
										$position = 'none';
								?>																																															
                <select name="position" id="range">
								<?php foreach ($this->positions as $key => $name) { ?>
									<?php $selected = ($position == $key) ? 'selected="selected"' : ''; ?>
									<option value="<?php echo esc_attr($key) ?>" <?php echo esc_attr($selected) ?>><?php echo esc_html($name) ?></option>
								<?php } ?>									
                </select>
                <span class="description"><?php esc_html_e('Where to display the pagination on the page.', 'wp-paginate'); ?></span></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Hide Standard Pagination:', 'wp-paginate'); ?></th>
            <td><label for="hide-standard-pagination">
								<?php 
									if(isset($this->options['hide-standard-pagination']))
										$hide_standard_pagination = $this->options['hide-standard-pagination'];
									else
										$hide_standard_pagination = 'none';
								?>																																															
                <input type="checkbox" id="hide-standard-pagination" name="hide-standard-pagination" <?php echo esc_attr(($hide_standard_pagination === true) ? "checked='checked'" : "") ?>/> <?php esc_html_e('Hide the standard theme default pagination.', 'wp-paginate'); ?></label></td>
        </tr>
    </table>		
    <p>&nbsp;</p>
    <h3><?php esc_html_e('Appearance', 'wp-paginate'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Pagination Font:', 'wp-paginate'); ?></th>
            <td>
								<?php 
									if(isset($this->options['font']))
										$font = $this->options['font'];
									else
										$font = 'font-inherit';
								?>																																																						
                <select name="font" id="font">
								<?php foreach ($this->fonts as $key => $name) { ?>
									<?php $selected = ($font == $key) ? 'selected="selected"' : ''; ?>
									<option value="<?php echo esc_attr($key) ?>" <?php echo esc_attr($selected) ?>><?php echo esc_html($name) ?></option>
								<?php } ?>									
                </select>
                <span class="description"><?php esc_html_e('Select the font to use with the pagination.', 'wp-paginate'); ?></span></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Hide Ellipses:', 'wp-paginate'); ?></th>
            <td><label for="hide-ellipses">
								<?php 
									if(isset($this->options['hide-ellipses']))
										$hide_ellipses = $this->options['hide-ellipses'];
									else
										$hide_ellipses = 'none';
								?>																																															
                <input type="checkbox" id="hide-ellipses" name="hide-ellipses" <?php echo esc_attr(($hide_ellipses === true) ? "checked='checked'" : "") ?>/> <?php esc_html_e('Remove ellipses from the pagination links.', 'wp-paginate'); ?></label></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Button Style:', 'wp-paginate'); ?></th>
            <?php 
            if(isset($this->options['preset'])) {
              $preset_option = $this->options['preset'];
            }	else {
              $preset_option = 'default';
            }										
            ?>
				    <input type='hidden' value='<?php echo esc_attr($preset_option) ?>' name='preset' id='preset'>
						<td>
            <p><?php esc_html_e('Choose a preset style from the list below.', 'wp-paginate'); ?></p>
							<table class="button-styles">
								<?php foreach($this->presets as $preset ) { ?>
								<tr>
									<td>
										<?php echo esc_html($preset[1]) ?>
									</td>
									<td>
										<input type="radio" name="preset" id="preset" value="<?php echo esc_attr($preset[0]) ?>" <?php echo esc_attr(($preset_option === $preset[0]) ? 'checked' : '') ?>>
									</td>										
									<td>
										<img alt="<?php echo esc_attr($preset[1]) ?> image" src="<?php echo esc_url($this->pluginurl . "images/" . $preset[2]) ?>" width="326" height="70">
									</td>
								</tr>
								<?php } ?>
							</table>							
						</td>
        </tr>
    </table>		
    <p>&nbsp;</p>
    <h3><?php esc_html_e('Advanced Settings', 'wp-paginate'); ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Before Markup:', 'wp-paginate'); ?></th>
            <td><input name="before" type="text" id="before" size="40" value="<?php echo esc_attr(stripslashes(wp_kses_decode_entities($this->options['before']))); ?>"/>
            <span class="description"><?php esc_html_e('The HTML markup to display before the pagination code.', 'wp-paginate'); ?></span></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('After Markup:', 'wp-paginate'); ?></th>
            <td><input name="after" type="text" id="after" size="40" value="<?php echo esc_attr(stripslashes(wp_kses_decode_entities($this->options['after']))); ?>"/>
            <span class="description"><?php esc_html_e('The HTML markup to display after the pagination code.', 'wp-paginate'); ?></span></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Markup Display:', 'wp-paginate'); ?></th>
            <td><label for="empty">
                <input type="checkbox" id="empty" name="empty" <?php echo esc_attr(($this->options['empty'] === true) ? "checked='checked'" : "") ?>/> <?php esc_html_e('Show Before Markup and After Markup, even if the page list is empty?', 'wp-paginate'); ?></label></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('WP-Paginate CSS File:', 'wp-paginate'); ?></th>
            <td><label for="css">
                <input type="checkbox" id="css" name="css" <?php echo esc_attr(($this->options['css'] === true) ? "checked='checked'" : "") ?>/> <?php printf(esc_html__('Include the default stylesheet wp-paginate.css? WP-Paginate will first look for <code>wp-paginate.css</code> in your theme directory (<code>themes/%s</code>).', 'wp-paginate'), get_template()); ?></label></td>
        </tr>
        <?php
        if(!isset($this->options['slash']))
          $this->options['slash'] = false;
        ?>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Add trailing slash:', 'wp-paginate'); ?></th>
            <td><label for="slash">
                <input type="checkbox" id="css" name="slash" <?php echo esc_attr(($this->options['slash'] === true) ? "checked='checked'" : "") ?>/> <?php printf(esc_html__('Adds a trailing slash to the end of pagination links.', 'wp-paginate'), get_template()); ?></label></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Page Range:', 'wp-paginate'); ?></th>
            <td>
                <select name="range" id="range">
                <?php for ($i=1; $i<=10; $i++) : ?>
                    <option value="<?php echo esc_attr($i) ?>" <?php echo esc_attr(($i == $this->options['range']) ? "selected='selected'" : "") ?>><?php echo esc_html($i) ?></option>
                <?php endfor; ?>
                </select>
                <span class="description"><?php esc_html_e('The number of page links to show before and after the current page. Recommended value: 3', 'wp-paginate'); ?></span></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Page Anchors:', 'wp-paginate'); ?></th>
            <td>
                <select name="anchor" id="anchor">
                <?php for ($i=0; $i<=10; $i++) : ?>
                    <option value="<?php echo esc_attr($i) ?>" <?php echo esc_attr(($i == $this->options['anchor']) ? "selected='selected'" : "") ?>><?php echo esc_html($i) ?></option>
                <?php endfor; ?>
                </select>
                <span class="description"><?php esc_html_e('The number of links to always show at beginning and end of pagination. Recommended value: 1', 'wp-paginate'); ?></span></td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Page Gap:', 'wp-paginate'); ?></th>
            <td>
                <select name="gap" id="gap">
                <?php for ($i=1; $i<=10; $i++) : ?>
                    <option value="<?php echo esc_attr($i) ?>" <?php echo esc_attr(($i == $this->options['gap']) ? "selected='selected'" : "") ?>><?php echo esc_html($i) ?></option>
                <?php endfor; ?>
                </select>
                <span class="description"><?php esc_html_e('The minimum number of pages in a gap before an ellipsis (...) is added. Recommended value: 3', 'wp-paginate'); ?></span></td>
        </tr>
    </table>
    <p class="submit">
      <input type="submit" id="wpp-save-changes" value="<?php esc_html_e('Save Changes', 'wp-paginate'); ?>" name="wp_paginate_save" class="button-primary" />
    </p>
</form>
  <p>&nbsp;</p>
  <hr>
<script>
	jQuery(document).ready(function(){

    jQuery("#wpp-save-changes").click(function(){
			unsaved = false;
    });

		var unsaved = false;

		jQuery(":input").change(function(){ //trigers change in all input fields including text type
			unsaved = true;
		});

		function unloadPage(){ 
			if(unsaved){
				return "Do you want to leave this page and discard your changes or stay on this page?";
			}
		}

		window.onbeforeunload = unloadPage;

	});


</script>  
<?php } elseif ( 'upgrade_to_pro' == $_GET['action'] ) { ?>
	<?php $this->wpp_upgrade_to_pro_tab(); ?>
  <p>&nbsp;</p>
  <hr>
<?php } elseif ( 'custom_css' == $_GET['action'] ) { ?>
  <h2>Custom CSS</h2>
	<?php $this->wpp_custom_code_tab(); ?>
  <p>&nbsp;</p>
  <hr>
<?php } ?>

<h2><?php esc_html_e('Need Support?', 'wp-paginate'); ?></h2>
<p><?php printf(esc_html__('For questions, issues or feature requests, please post them in the %s and make sure to tag the post with wp-paginate.', 'wp-paginate'), '<a href="https://wordpress.org/support/plugin/wp-paginate">WordPress Forum</a>'); ?></p>
<p>&nbsp;</p>
<div id="wpp-plugins">

		<div class="paginate-info"><?php esc_html_e( 'WP Paginate is Maintained and Upgraded by Max Foundry', 'wp-paginate' ); ?></div>   
		<div class="paginate-info"><?php esc_html_e( 'Developed Originally by Eric Martin', 'wp-paginate' ); ?></div>
		<div class="mf-plugins"><?php esc_html_e( 'Max Foundry Produces These Other Fine Plugins', 'wp-paginate' ); ?></div>			

		<div class="column-left">
			<a href="<?php echo esc_url_raw(WPP_MAXBUTTONS_LINK) ?>" target="_blank">
				<img alt="maxbuttons logo" src="<?php echo $this->pluginurl ?>images/MBP.png" width="283" height="142">	
			</a>
			<a href="<?php echo esc_url_raw(WPP_MAXBUTTONS_LINK) ?>" target="_blank">
				<div class="wpp-plugin-title"><?php esc_html_e( 'WordPress Button Plugin', 'wp-paginate' ); ?></div>
			</a>
		</div>
		<div class="column-center">
			<a href="<?php echo esc_url_raw(WPP_MAXGALLERIA_LINK) ?>" target="_blank">
				<img alt="maxgalleria logo" src="<?php echo esc_url($this->pluginurl . "images/MG_logo.png") ?>" width="283" height="142">				
			</a>
			<a href="<?php echo esc_url_raw(WPP_MAXGALLERIA_LINK) ?>" target="_blank">
				<div class="wpp-plugin-title"><?php esc_html_e( 'WordPress Gallery Plugin', 'wp-paginate' ); ?></div>
			</a>
		</div>
		<div class="column-right">
			<a href="<?php echo esc_url_raw(WPP_MEDIA_LIBRARY_PLUS_PRO_LINK) ?>" target="_blank">
				<img alt="media library plus pro logo" src="<?php echo esc_url($this->pluginurl . "images/MLPP-card-red.png") ?>" width="283" height="142">							
			</a>
			<a href="<?php echo esc_url_raw(WPP_MEDIA_LIBRARY_PLUS_PRO_LINK) ?>" target="_blank">
				<div class="wpp-plugin-title"><?php esc_html_e( 'WordPress Media Library Folders', 'wp-paginate' ); ?></div>
			</a>
		</div>		
</div>

</div>

<?php
}

	function wpp_include_codemirror() {
		wp_enqueue_style( 'codemirror.css', plugins_url( 'lib/codemirror.css', __FILE__ ) );
		wp_enqueue_script( 'codemirror.js', plugins_url( 'lib/codemirror.js', __FILE__ ), array( 'jquery' ) );
  }

	function wpp_custom_code_tab() {
		if ( ! current_user_can( 'edit_plugins' ) )
			wp_die( esc_html__( 'You do not have sufficient permissions to edit plugins for this site.', 'wp-paginate' ) );

		$message = $content = '';
		$extension = 'css';
		$wpp_custom_css_active = get_option('wpp_custom_css_active', 'off');

		$upload_dir = wp_upload_dir();
		$folder = $upload_dir['basedir'] . '/wpp-custom-code';
		if ( ! $upload_dir["error"] ) {
			if ( ! is_dir( $folder ) )
				wp_mkdir_p( $folder, 0755 );
			
			$index_file = $upload_dir['basedir'] . '/wpp-custom-code/index.php';
			if ( ! file_exists( $index_file ) ) {
				if ( $f = fopen( $index_file, 'w+' ) )
					fclose( $f );
			}							
		}

		$css_file = 'wpp-custom-code.css';
		$real_css_file = $folder . '/' . $css_file;

		if ( isset( $_REQUEST['wpp_update_custom_code'] ) && check_admin_referer( 'wpp_update_nonce' . $css_file ) ) {

			/* CSS */
			$newcontent_css = wp_kses_post(wp_unslash( $_POST['wpp_newcontent_css'] ));	
			if ( $f = fopen( $real_css_file, 'w+' ) ) {
				fwrite( $f, $newcontent_css );
				fclose( $f );
				$message .= sprintf( esc_html__( 'File %s edited successfully.', 'wp-paginate' ), '<i>' . $css_file . '</i>' ) . ' ';
			} else {
				$error .= esc_html__( 'Not enough permissions to create or update the file', 'wp-paginate' ) . ' ' . $real_css_file . '. ';
			}
			
			if(isset($_REQUEST['wpp_custom_css_active']))
				$wpp_custom_css_active = 'on';
			else
				$wpp_custom_css_active = 'off';
			
			update_option('wpp_custom_css_active', $wpp_custom_css_active, true );
			
			if ( ! empty( $error ) )
				$error .= ' <a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">' . esc_html__( 'Learn more', 'wp-paginate' ) . '</a>';
		}

		if ( file_exists( $real_css_file ) ) {
			update_recently_edited( $real_css_file );
			$content_css = esc_textarea( file_get_contents( $real_css_file ) );
				//$is_css_active = true;
		}
		else 
			$content_css = "";

		if ( ! empty( $message ) ) { ?>
			<div id="message" class="below-h2 updated notice is-dismissible"><p><?php echo esc_html($message) ?></p></div>
		<?php } ?>		
		<form action="" method="post">
				<p>
					<?php esc_html_e( 'These styles will be added to the header on all pages of your site.', 'wp-paginate' ); ?>
				</p>
				<p><big>
					<?php if ( ! file_exists( $real_css_file ) || ( is_writeable( $real_css_file ) ) ) {
						echo esc_html__( 'Editing', 'wp-paginate' ) . ' <strong>' . $css_file . '</strong>';
					} else {
						echo esc_html__( 'Browsing', 'wp-paginate' ) . ' <strong>' . $css_file . '</strong>';
					} ?>
				</big></p>
				<p><label><input type="checkbox" name="wpp_custom_css_active" value="1" <?php checked( $wpp_custom_css_active, 'on'); ?> />	<?php _e( 'Activate', 'wp-paginate' ); ?></label></p>
				<textarea cols="70" rows="25" name="wpp_newcontent_css" id="wpp_newcontent_css"><?php if ( isset( $content_css ) ) echo $content_css; ?></textarea>
			<?php if ( ! file_exists( $real_css_file ) || is_writeable( $real_css_file )) { ?>
				<p class="submit">
					<input type="hidden" name="wpp_update_custom_code" value="submit" />					
					<?php submit_button( esc_html__( 'Save Changes', 'wp-paginate' ), 'primary', 'submit', false ); 
					wp_nonce_field( 'wpp_update_nonce' . $css_file ); ?>
				</p>
			<?php } else { ?>
				<p><em><?php printf( esc_html__( 'You need to make this files writable before you can save your changes. See %s the Codex %s for more information.', 'wp-paginate' ),
				'<a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">',
				'</a>' ); ?></em></p>
			<?php }	?>
		</form>
		<script>
	  jQuery(document).ready(function(){				
			
			if ( typeof CodeMirror == 'function' ) {
				if ( jQuery( '#wpp_newcontent_css' ).length > 0 ) {
					var editor = CodeMirror.fromTextArea( document.getElementById( 'wpp_newcontent_css' ), {
						mode: "css",
						theme: "default",
						styleActiveLine: true,
						matchBrackets: true,
						lineNumbers: true,	
					});	
				}			
			}
			
			jQuery("#submit").click(function(){
				unsaved = false;
			});

			var unsaved = false;

			jQuery(":input").change(function(){ //trigers change in all input fields including text type
				unsaved = true;
			});

			function unloadPage(){ 
				if(unsaved){
					return "Do you want to leave this page and discard your changes or stay on this page?";
				}
			}

			window.onbeforeunload = unloadPage;

	  });
		
		
		</script>			
	<?php }
	
	function wpp_print_style() { 
		$this->get_options();
			$classes = ''; 
			if(isset($this->options['hide-standard-pagination']) && $this->options['hide-standard-pagination']) {
				//$hide_comments  
				$classes .=  
					'.archive #nav-above,
					.archive #nav-below,
					.search #nav-above,
					.search #nav-below,
					.blog #nav-below, 
					.blog #nav-above, 
					.navigation.paging-navigation, 
					.navigation.pagination,
					.pagination.paging-pagination, 
					.pagination.pagination, 
					.pagination.loop-pagination, 
					.bicubic-nav-link, 
					#page-nav, 
					.camp-paging, 
					#reposter_nav-pages, 
					.unity-post-pagination, 
					.wordpost_content .nav_post_link';
			} 
			if ( ( ! empty( $pgntn_options['additional_pagination_style'] ) ) && '1' == $pgntn_options['display_custom_pagination'] ) {	
				$classes .= ! empty( $classes ) ? ',' : '';
				$classes .= $pgntn_options['additional_pagination_style']; 
			} 
			if ( ! empty( $classes ) ) {
        echo '<style type="text/css">' . PHP_EOL;
				echo $classes . ' { 
						display: none !important; 
					}
					.single-gallery .pagination.gllrpr_pagination {
						display: block !important; 
					}';
        echo '</style>' . PHP_EOL;
			} ?>		
	<?php }
	
	public function show_wpp_admin_notice() {
    $current_user_id = get_current_user_id(); 
    
    $review = get_user_meta( $current_user_id, WPP_REVIEW_NOTICE, true );
    if( $review !== 'off') {
      if($review === false) {
				$this->wpp_show_review_notice();
		  } else {
        $now = date("Y-m-d"); 
        $review_time = strtotime($review);
        $now_time = strtotime($now);
        if($now_time > $review_time) {
					$this->wpp_show_review_notice();
				}	
      }
    }          
	}
			
	public function wpp_show_review_notice() {
    if( current_user_can( 'manage_options' ) ) {  ?>
      <div class="updated notice wpp-notice">         
        <div id='wp-paginate-logo'></div>
        <div id='wpp-notice-1'><p id='wpp-notice-title'><?php esc_html_e( 'Love WP-Paginate?', 'wp-paginate' ); ?></p>
        <p><?php esc_html_e( 'Your rating is a big help! We really appreciate it!', 'wp-paginate' ); ?></p>

        <ul id="wpp-review-notice-links">
          <li> <span class="dashicons dashicons-smiley"></span><a id="wpp-review-already"><?php esc_html_e( "I've already left a review", 'wp-paginate' ); ?></a></li>
          <li><span class="dashicons dashicons-calendar-alt"></span><a id="wpp-review-later"><?php esc_html_e( "Maybe Later", 'wp-paginate' ); ?></a></li>
          <li><span class="dashicons dashicons-external"></span><a id="wpp-write-review" target="_blank" href="https://wordpress.org/support/plugin/wp-paginate/reviews/?filter=5"><?php esc_html_e( "Sure! I'd love to!", 'wp-paginate' ); ?></a></li>
        </ul>
        </div>
        <a class="dashicons dashicons-dismiss close-wpp-notice" id="wpp-dismiss"></a>          
      </div>
    <?php     
    }
  }
	
  public function wpp_dismiss_notice() {
		
    if ( !wp_verify_nonce( $_POST['nonce'], WP_PAGINATE_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh the page.', 'wp-paginate'));
    }
		    
    $current_user_id = get_current_user_id(); 
    
    update_user_meta( $current_user_id, WPP_REVIEW_NOTICE, "off" );
		
		exit();
        
	}
  
	public function wpp_set_review_later() {
		
    if ( !wp_verify_nonce( $_POST['nonce'], WP_PAGINATE_NONCE)) {
      exit(esc_html__('missing nonce! Please refresh the page.', 'wp-paginate'));
    }
		    
    $current_user_id = get_current_user_id(); 
    
    $review_date = date('Y-m-d', strtotime("+7 days"));

		
    update_user_meta( $current_user_id, WPP_REVIEW_NOTICE, $review_date );
    
		exit();
    
	}
	
	function wpp_upgrade_to_pro_tab() {
		?>
		<div id="utp-content">
			<div id="mf-logo">
				<img alt="<?php esc_html_e('maxfoundry logo', 'wp-paginate'); ?>" src="<?php echo esc_url($this->pluginurl . '/images/max-foundry.png') ?>" width="172" height="32">
			</div>
			<div style="clear:both"></div>
			<div id="utp-banner">
				<div id="utp-title-wrap">
				  <div id="utp-title"><?php esc_html_e('WP PAGINATE PRO', 'wp-paginate'); ?></div>
				</div>
				<a href="<?php echo esc_url_raw(WPP_WP_PAGINATE_PRO_LINK) ?>" target="_blank">
				  <img class="buy-now-button" alt="<?php esc_html_e('buy now button', 'wp-paginate'); ?>" src="<?php echo esc_url($this->pluginurl . "/images/buy-now-btn.png") ?>" width="205" height="68">
				</a>
			</div>
			<img id='wppp-logo' alt="<?php esc_html_e('wp-pagination pro logo', 'wp-paginate'); ?>" src="<?php echo esc_url($this->pluginurl . '/images/wpp-pro-logo-2.png') ?>" width="169" height="61">
			
			<div class="utp-text">
				<?php esc_html_e('WP-Paginate Pro come with 11 Beautiful<br>Preset Layouts and Multi Site support!', 'wp-paginate'); ?>
			</div>
			
			<div id="wppp-buttons">
				<img alt="<?php esc_html_e('WP Pagination Pro buttons styles', 'wp-paginate'); ?>" src="<?php echo esc_url($this->pluginurl . '/images/wppp-buttons.png') ?>" width="646" height="713">	
			</div>
			
			<div class="utp-text">
				<?php esc_html_e('Use These Customizer Settings to Get the<br>Exact Look and Feel You Want', 'wp-paginate'); ?>
			</div>
			
			<div id="wppp-customizer">
				<img alt="<?php esc_html_e('WP Pagination Por button Style customizer', 'wp-paginate'); ?>" src="<?php echo esc_url($this->pluginurl . '/images/wppp-customizer.png') ?>" width="650" height="380">					
			</div>
			
			<a href="<?php echo esc_url_raw(WPP_WP_PAGINATE_PRO_LINK) ?>" target="_blank">
				<img class="buy-now-button" alt="<?php esc_html_e('buy now button', 'wp-paginate'); ?>" src="<?php echo esc_url($this->pluginurl . "/images/buy-now-btn.png") ?>" width="205" height="68">
			</a>
			
		</div>
		
		
		<div>
			
		</div>
		
		
		<?php
	}
	
			
  }
}

//instantiate the class
if (class_exists('WPPaginate')) {
    $wp_paginate = new WPPaginate();
}

/**
 * Pagination function to use for posts
 */
function wp_paginate($args = false) {
	
	if(!is_active_widget()) {
    global $wp_paginate;
    $wp_paginate->type = 'posts';
    return $wp_paginate->paginate($args);
	}	
}

/**
 * Pagination function to use for post comments
 */
function wp_paginate_comments($args = false) {
    global $wp_paginate;
    $wp_paginate->type = 'comments';
    return $wp_paginate->paginate($args);
}


/*
 * The format of this plugin is based on the following plugin template:
 * http://pressography.com/plugins/wordpress-plugin-template/
 */
