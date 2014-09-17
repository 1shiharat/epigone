<?php
/**
 * Contains methods for customizing the theme customization screen.
 * @author ishihara takashi
 * @link http://codex.wordpress.org/Theme_Customization_API
 * @since 1.1.0
 */

class Epigone_Theme_Customize {

	private $slug = '';

	private $capability;

	private $theme_supports;

	private $transport;

	private $support_fields;

	private $settings;
	/**
		* This hooks into 'customize_register' (available as of WP 3.4) and allows
		* you to add new sections and controls to the Theme Customize screen.
		*
		* Note: To enable instant preview, we have to actually write a bit of custom
		* javascript. See live_preview() for more.
		*
		* @see add_action('customize_register',$func)
		* @param \WP_Customize_Manager $wp_customize
		* @link http://ottopress.com/2012/how-to-leverage-the-theme-customizer-in-your-own-themes/
		* @since epigone 1.1.0
		*/
	public function __construct(){

		$this->slug = get_stylesheet();

		$this->capability = 'edit_theme_options';

		$this->theme_supports = '';

		$this->transport = 'postMessage';

		$this->setting_type = 'option';

		$this->settings = $this->settings_init();


		add_action( 'init', array( $this, 'register_fields' ) );
		add_action( 'customize_register', array( $this, 'register' ) );
		add_action( 'customize_preview_init', array( $this, 'live_preview' ) );

		// output css
		add_action( 'wp_head', array( $this, 'wphead_css_output' ), 10 );

	}

	/**
	 * Theme Customizer Settings
	 */

	public function settings_init(){

		/**
		 * 01. logo
		 */
		$settings['epigone_logo'] = array(
			'title' => __( 'Logo Settings', $this->slug ), // Panel title
			'description' => __( 'Please have a set of headers.', $this->slug ),
			'section' => array(
				'epigone_logo' => array(
					'title' => __( 'Logo', $this->slug ),
					'description' => __( '', $this->slug ),
					'setting' => array(
						'logo_font_size' => array(
							'label' => __( 'Font Size', $this->slug ),
							'default' => 12,
							'type' => 'slider',
							'sanitaize_call_back' => '',
							'input_attrs' => array(
								'min'   => 0,
								'max'   => 40,
								'step'  => 1,
								'class' => 'test-class test',
							),
						),
						'logo_color' => array(
							'label' => __( 'Color', $this->slug ),
							'default' => 12,
							'type' => 'color',
							'sanitaize_call_back' => '',
						)
					)
				)
			)
		);

		/**
		 * 02. Header
		 */
		$settings['epigone_header'] = array(
			'title' => __( 'Header Settings', $this->slug ), // Panel title
			'description' => __( 'Please have a set of headers.', $this->slug ),
			'section' => array(
				'epigone_header' => array(
					'title' => __( 'Background', $this->slug ),
					'description' => __( '', $this->slug ),
					'setting' => array(
						'background_image' => array(
							'label' => __( 'Background Image', $this->slug ),
							'default' => 12,
							'type' => 'multi-image',
							'sanitaize_call_back' => '',
							'output' => array(
								'#masthead' => 'background-image',
							)
						),
						'background_color' => array(
							'label' => __( 'Background Color', $this->slug ),
							'type' => 'date-picker',
							'sanitaize_call_back' => '',
							'output' => array(
								'body' => 'background-color',
							)
						),
					)
				)
			)
		);

		return $settings;

	}

	/**
	 * Registration of the input type that theme customizer support.
	 * @return void
	 */
	public function register_fields(){

		// Require Fields File
		$support_fields = array(
			'date' => array(
				'Date_Picker_Custom_Control' => 'date-picker',
			),
			'image' => array(
				'Multi_Image_Custom_Control' => 'multi-image',
			),
			'layout' => array(
				'Layout_Picker_Custom_Control' => 'layout-picker',
			),
			'select' => array(
				'Category_Dropdown_Custom_Control'    => 'category-dropdown',
				'Google_Font_Dropdown_Custom_Control' => 'google-font-dropdown',
				'Menu_Dropdown_Custom_Control'        => 'menu-dropdown',
				'Post_Dropdown_Custom_Control'        => 'post-dropdown',
				'Post_Type_Dropdown_Custom_Control'   => 'post-type-dropdown',
				'Tags_Dropdown_Custom_Control'        => 'tags-dropdown',
				'Taxonomy_Dropdown_Custom_Control'    => 'taxonomy-dropdown',
				'User_Dropdown_Custom_Control'        => 'user-dropdown',
			),
			'text' => array(
				'Text_Editor_Custom_Control' => 'text-editor',
				'Textarea_Custom_Control'    => 'textarea',
			),
			'slider' => array(
				'Slider_Custom_Control' => 'slider',
			),
		);

		$this->support_fields = $support_fields;

		foreach ( $support_fields as $dir => $field_slugs ) {
			foreach ( $field_slugs as $slug ) {
				$filename = __DIR__ . '/fields/' . $dir . '/' . 'class-' . $slug . '-custom-control.php';
				if ( file_exists( $filename ) ) {
					require_once( $filename );
				}
			}
		}

	}

	/**
	 * Register
	 * @param  object $wp_customize
	 */

	public function register( $wp_customize ) {

		$this->wp_customize = $wp_customize;

		if ( ! $this->settings ) {
			return false;
		}

		$this->add_customize( $this->settings );

		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
		$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';

	}

	/**
	 * Registration of the theme customizer.
	 *
	 * @param array $customizer_settings
	 *
	 * `` EXAMPLE SETTINGS
	 *	$customizer_settings = array(
	 * 		'panel_id' => array(
	 * 			'title' => '',
	 * 			'description' => '',
	 * 			'section' => array(
	 * 				'section_id' => array(
	 * 					'title' => '',
	 * 					'description' => '',
	 * 					'setting' => array(
	 * 						'setting_id' => array(
	 * 							'label' => '',
	 * 							'default' => '',
	 * 							'type' => '',
	 * 							'sanitaize_call_back' => '',
	 * 							'input_attrs' => array(),
	 * 						)
	 * 					)
	 * 				)
	 * 			)
	 * 		),
	 */

	public function add_customize( $customizer_settings ){

		if ( ! $this->wp_customize
				 || ( ! $customizer_settings && is_array( $customizer_settings ) ) ) {
			return false;
		}


		foreach ( $customizer_settings as $panel_id => $panel ) {

			$this->add_panel( $panel_id, $panel['title'], $panel['description'] );

			if ( is_array( $panel['section'] ) ) {

				foreach ( $panel['section'] as $section_id => $section ) {

					$section_title       = isset( $section['title'] ) ? $section['title'] : '';
					$section_description = isset( $section['description'] ) ? $section['description'] : '';
					$this->add_section( $section_id, $section_title, $section_description, $panel_id );

					if ( $section['setting'] ) {

						foreach ( $section['setting'] as $setting_id => $setting ) {

							$setting_default             = isset( $setting['default'] ) ? $setting['default'] : '';
							$setting_sanitaize_call_back = isset( $setting['sanitaize_call_back'] ) ? $setting['sanitaize_call_back'] : '';
							$setting_label               = isset( $setting['label'] ) ? $setting['label'] : '';
							$setting_type                = isset( $setting['type'] ) ? $setting['type'] : '';
							$setting_input_attrs         = isset( $setting['input_attrs'] ) ? $setting['input_attrs'] : '';

							$this->add_setting( $setting_id, $setting_default, $setting_sanitaize_call_back );
							$this->add_control( $setting_id, $setting_type, $setting_label, $section_id,  $setting_input_attrs );

						}
					}
				}
			}
		}

	}

	/**
	 * add_panel
	 * @param object $wp_customize
	 * @param string $panel_id
	 * @param string $title
	 * @param string $description
	 */
	public function add_panel( $panel_id, $title = '', $description = '' ){

		$this->wp_customize->add_panel(
			$panel_id,
			array(
				'priority'       => 10,
				'capability'     => $this->capability,
				'theme_supports' => $this->theme_supports,
				'title'          => $title,
				'description'    => $description,
			)
		);

	}

	/**
	 * add_section
	 * @param string $section_id
	 * @param string $title
	 * @param string $description
	 * @param string $panel_id
	 */
	public function add_section( $section_id, $title = '', $description = '', $panel_id = '' ){

		$section_settings = array(
			'title'          => $title,
			'priority'       => 10,
			'capability'     => $this->capability,
			'theme_supports' => $this->theme_supports,
			'description'    => $description,
		);

		if ( $panel_id ) {
			$section_settings['panel'] = $panel_id;
		}

		$this->wp_customize->add_section(
			$section_id,
			$section_settings
		);


	}

	/**
	 * add_section
	 * @param object $wp_customize
	 * @param string $setting_id
	 * @param string $title
	 * @param string $description
	 * @param string $panel_id
	 */
	public function add_setting( $setting_id, $default = '', $sanitaize_call_back = '' ){

		$setting_settings = array(
			'default'    => $default,
			'type'       => $this->setting_type,
			'capability' => $this->capability,
			'transport'  => $this->transport,
		);

		$this->wp_customize->add_setting(
			'theme_mods_' . $this->slug . '[' . $setting_id . ']',
			$setting_settings
		);

	}

	/**
	 * Add Control
	 * @param object  $wp_customize
	 * @param string  $control_id
	 * @param string  $type
	 * @param string  $label
	 * @param string  $section
	 * @param boolean $input_attrs
	 */
	public function add_control( $control_id, $type, $label, $section, $input_attrs = false ){

		$extend_field_classname = false;

		$control_settings = array(
			'type'     => $type,
			'label'    => $label,
			'section'  => $section,
			'settings' => 'theme_mods_' . $this->slug . '[' . $control_id . ']',
			'priority' => 10,
		);

		if ( $input_attrs ) {
			$control_settings['input_attrs'] = $input_attrs;
		}

		/**
		 * Check Extend Customizer field
		 */
		foreach ( $this->support_fields as $fields ) {
			foreach ( $fields as $class_name => $field ) {

				if ( $type === $field ) {
						$extend_field_classname = $class_name;
				} else {
					continue;
				}
			}
		}

		if ( $type === 'color' ) {
			$extend_field_classname = 'WP_Customize_Color_Control';
		}

		if ( $extend_field_classname ) {

			$instance = new $extend_field_classname( $this->wp_customize, $control_id, $control_settings );

			$this->wp_customize->add_control( $instance );

		} else {

			$this->wp_customize->add_control(
				$control_id,
				$control_settings
			);

		}

	}

	 /**
		* This outputs the javascript needed to automate the live settings preview.
		* Also keep in mind that this function isn't necessary unless your settings
		* are using 'transport'=>'postMessage' instead of the default 'transport'
		* => 'refresh'
		*
		* Used by hook: 'customize_preview_init'
		*
		* @see add_action('customize_preview_init',$func)
		* @since epigone 1.0
		*/
	public static function live_preview(){
		wp_enqueue_script(
			'epigone-themecustomizer',
			get_template_directory_uri() . '/assets/js/customizer.js',
			array(  'jquery', 'customize-preview' ),
			'',
			true
		);
	}

	/**
	 * Output CSS to wp_head
	 * @return void
	 */
	public function wphead_css_output() {

		$css = $this->css_parser();

		if ( $css  ) {
			echo '<style>' . $css .'</style>';
		}

	}

	/**
	 * CSS Parser
	 * @return string $style
	 */
	public function css_parser() {

		$style = '';

		if ( ! $this->settings ) {
			return;
		}

		foreach ( $this->settings as $panel_id => $panel ) {

			foreach ( $panel['section'] as $section_id => $section ) {

				if ( ! $section['setting'] ) {
					continue;
				}

				foreach ( $section['setting'] as $setting_id => $setting ) {
					if ( isset( $setting['output'] ) && is_array( $setting['output'] ) ) {
						$style .= $this->generate_to_css( $setting, $setting_id );
					}
				}
			}
		}

		return $style;

	}

	public function generate_to_css( $setting, $customizer_key ){

		$css = '';

		foreach ( $setting['output'] as $selector => $priority ) {

			if ( ! $customizer_key ) {
				continue;
			}

			if ( 'color' === $setting['type'] ) {
				$css = $selector . '{' . $priority . ' : #' . get_theme_mod( $customizer_key, '' ) . ';}';
			} elseif ( 'multi-image' === $setting['type'] ) {
				$css = $selector . '{' . $priority . ' : url(' . get_theme_mod( $customizer_key, '' ) . ' );}';
			} else {
				$css = $selector . '{' . $priority . ' : ' . get_theme_mod( $customizer_key, '' ) . ';}';
			}
		}

		return $css;

	}

}

$epigone_theme_customize = new Epigone_Theme_Customize();
