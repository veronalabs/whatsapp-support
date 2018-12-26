<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    WhatsAppSupport
 * @subpackage WhatsAppSupport/admin
 */
class WhatsAppSupport_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The setings of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $settings The current settings of this plugin.
	 */
	private $settings;

	/**
	 * Use International Telephone Input library (https://intl-tel-input.com/)
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      boolean $enhanced_phone Use enhanced phone input.
	 */
	private $enhanced_phone;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param    string $plugin_name The name of this plugin.
	 * @param    string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name    = $plugin_name;
		$this->version        = $version;
		$this->enhanced_phone = true;
		$this->get_settings();

	}

	/**
	 * Get all settings or set defaults
	 *
	 * @since    1.0.0
	 */
	private function get_settings() {

		$this->settings = array(
			'telephone'     => '',
			'mobile_only'   => 'no',
			'message_text'  => '',
			'message_delay' => 10000,
			'message_send'  => '',
			'position'      => 'right',
		);

		$saved_settings = get_option( 'whatsappsupport' );

		if ( is_array( $saved_settings ) ) {
			// clean unused saved settings
			$saved_settings = array_intersect_key( $saved_settings, $this->settings );
			// merge defaults with saved settings
			$this->settings = array_merge( $this->settings, $saved_settings );
		}

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.2.0
	 */
	public function enqueue_styles( $hook ) {

		if ( 'toplevel_page_whatsapp-support' == $hook && $this->enhanced_phone ) {
			wp_enqueue_style( 'intl-tel-input', WHATSAPPSUPPORT_URL . 'admin/css/intlTelInput.css', array(), null, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.2.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_whatsapp-support' == $hook && $this->enhanced_phone ) {
			wp_enqueue_script( 'intl-tel-input', WHATSAPPSUPPORT_URL . 'admin/js/intlTelInput.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'whatsappsupport-admin', plugin_dir_url( __FILE__ ) . 'js/whatsappsupport.js', array( 'intl-tel-input' ), $this->version, true );
		}

	}

	/**
	 * Initialize the settings for wordpress admin
	 * From v1.2.0 also set filter to disable enhanced phone input
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function settings_init() {

		register_setting( 'whatsappsupport', 'whatsappsupport', array( $this, 'settings_validate' ) );
		add_settings_section( 'whatsappsupport_section', null, array( $this, 'section_text' ), 'whatsappsupport' );

		$field_names = array(
			'telephone'     => __( 'Telephone', 'whatsapp-support' ),
			'mobile_only'   => __( 'Mobile only', 'whatsapp-support' ),
			'message_text'  => __( 'Call to action', 'whatsapp-support' ),
			'message_delay' => __( 'Delay', 'whatsapp-support' ),
			'message_send'  => __( 'Message', 'whatsapp-support' ),
			'position'      => __( 'Position on screen', 'whatsapp-support' ),
		);

		foreach ( $this->settings as $key => $value ) {
			add_settings_field( 'whatsappsupport_' . $key, $field_names[ $key ], array( $this, 'field_' . $key ), 'whatsappsupport', 'whatsappsupport_section' );
		}

		$this->enhanced_phone = apply_filters( 'whatsappsupport_enhanced_phone', $this->enhanced_phone );
	}

	/**
	 * Validate settings, clean and set defaults before save
	 *
	 * @since    1.0.0
	 * @return   array
	 */
	public function settings_validate( $input ) {

		if ( ! array_key_exists( 'mobile_only', $input ) ) {
			$input['mobile_only'] = 'no';
		}
		$input['telephone']     = sanitize_text_field( $input['telephone'] );
		$input['message_text']  = trim( $input['message_text'] );
		$input['message_delay'] = intval( $input['message_delay'] );
		$input['message_send']  = trim( $input['message_send'] );
		$input['position']      = $input['position'] != 'left' ? 'right' : 'left';

		add_settings_error( 'whatsappsupport', 'settings_updated', __( 'Settings saved', 'whatsapp-support' ), 'updated' );

		return $input;
	}

	/**
	 * Section 'whatsappsupport_section' output
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function section_text() {
		echo '<p>' . __( 'From here you can configure the behavior of the WhatsApp button on your site.', 'whatsapp-support' ) . '</p>';
	}

	/**
	 * Field 'telephone' output
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function field_telephone() {
		$name = $this->enhanced_phone ? '' : 'whatsappsupport[telephone]';
		echo '<input id="whatsappsupport_phone" name="' . $name . '" value="' . $this->settings['telephone'] . '" type="text" style="width:15em;height:28px;line-height:1;">' .
		     '<p class="description">' . __( "Contact phone number. <strong>The button will not be shown if it's empty.</strong>", 'whatsapp-support' ) . '</p>';
	}

	/**
	 * Field 'message_text' output
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function field_message_text() {
		echo '<textarea name="whatsappsupport[message_text]" rows="3" class="regular-text" placeholder="' . esc_attr__( "Hello 👋\nCan we help you?", 'whatsapp-support' ) . '">' . $this->settings['message_text'] . '</textarea>' .
		     '<p class="description">' . __( '<strong>Optional.</strong> Text to invite the user to use the contact via WhatsApp.', 'whatsapp-support' ) . '</p>' .
		     '<p>' . __( 'You can use formatting styles like in WhatsApp: _<em>italic</em>_ *<strong>bold</strong>* ~<del>strikethrough</del>~', 'whatsapp-support' ) . '</p>';
	}

	/**
	 * Field 'message_delay' output
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function field_message_delay() {
		echo '<input name="whatsappsupport[message_delay]" value="' . $this->settings['message_delay'] . '" class="small-text" type="number" min="0"> ' . __( 'milliseconds', 'whatsapp-support' ) .
		     '<p class="description"> ' . __( 'The <strong>Call to action</strong> will only be displayed once when the user exceeds the estimated delay on a page. It will also be displayed when the user stops the cursor over the WhatsApp button.', 'whatsapp-support' ) . '</p>';
	}

	/**
	 * Field 'message_send' output
	 *
	 * @since    1.4.0
	 * @return   void
	 */
	public function field_message_send() {
		echo '<textarea name="whatsappsupport[message_send]" rows="3" class="regular-text" placeholder="' . esc_attr__( "Hi {SITE}! I need more info about {TITLE}", 'whatsapp-support' ) . '">' . $this->settings['message_send'] . '</textarea>' .
		     '<p class="description">' . __( '<strong>Optional.</strong> Default message to start the conversation.', 'whatsapp-support' ) . '</p>' .
		     '<p>' . __( 'You can use vars <code>{SITE} {URL} {TITLE}</code> that will be replaced with the values of the current page.', 'whatsapp-support' ) . '</p>';
	}

	/**
	 * Field 'mobile_only' output
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function field_mobile_only() {
		echo '<fieldset><legend class="screen-reader-text"><span>' . __( 'Mobile only', 'whatsapp-support' ) . '</span></legend>' .
		     '<label><input name="whatsappsupport[mobile_only]" value="yes" type="checkbox"' . checked( 'yes', $this->settings['mobile_only'], false ) . '> ' .
		     __( 'Only display the button on mobile devices', 'whatsapp-support' ) . '</label></fieldset>';
	}

	/**
	 * Field 'position' output
	 *
	 * @since    1.3.0
	 * @return   void
	 */
	public function field_position() {
		echo '<fieldset><legend class="screen-reader-text"><span>' . __( 'Position on screen', 'whatsapp-support' ) . '</span></legend>' .
		     '<p><label><input name="whatsappsupport[position]" value="right" type="radio"' . checked( 'right', $this->settings['position'], false ) . '> ' .
		     __( 'Right', 'whatsapp-support' ) . '</label>' .
		     '<br><label><input name="whatsappsupport[position]" value="left" type="radio"' . checked( 'left', $this->settings['position'], false ) . '> ' .
		     __( 'Left', 'whatsapp-support' ) . '</label></p></fieldset>';
	}

	/**
	 * Add menu to the options page in the wordpress admin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function add_menu() {
		add_menu_page( 'WhatsApp Support', 'WhatsApp', 'manage_options', 'whatsapp-support', array( $this, 'options_page' ), plugin_dir_url( __FILE__ ) . '/img/menu-icon.svg' );
		add_submenu_page( 'whatsapp-support', 'WhatsApp Support', 'WhatsApp', 'manage_options', 'whatsapp-support', array( $this, 'options_page' ) );
	}

	/**
	 * Add link to options page on plugins page
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function settings_link( $links ) {

		$settings_link = '<a href="options-general.php?page=' . $this->plugin_name . '">' . __( 'Settings', 'whatsapp-support' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;

	}

	/**
	 * Generate the options page in the wordpress admin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	function options_page() {
		?>
        <div class="wrap">
            <h1>WhatsApp Support</h1>

            <form method="post" id="whatsappsupport_form" action="options.php">
				<?php
				settings_fields( 'whatsappsupport' );
				do_settings_sections( 'whatsappsupport' );
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

	/**
	 * Add Meta Box for all the public post types
	 *
	 * @since    1.1.0
	 * @access   public
	 * @return   void
	 */
	public function add_meta_boxes() {
		// Default post types
		$builtin_post_types = array( 'post', 'page' );
		// Custom post types with public url
		$custom_post_types = array_keys( get_post_types( array( 'rewrite' => true ), 'names' ) );

		// Add/remove posts types for "WhatsApp Support" meta box
		$post_types = apply_filters( 'whatsappsupport_post_types_meta_box', array_merge( $builtin_post_types, $custom_post_types ) );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'whatsappsupport',
				__( 'WhatsApp Support', 'whatsapp-support' ),
				array( $this, 'add_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Generate Meta Box html
	 *
	 * @since    1.1.0
	 * @access   public
	 * @return   void
	 */
	public function add_meta_box( $post ) {

		$metadata = get_post_meta( $post->ID, '_whatsappsupport', true ) ?: array();
		$metadata = array_merge( array(
			'message_text' => '',
			'message_send' => '',
			'hide'         => false
		), $metadata );

		$post_type      = get_post_type_object( get_post_type( $post->ID ) );
		$post_type_name = function_exists( 'mb_strtolower' ) ?
			mb_strtolower( $post_type->labels->singular_name ) :
			strtolower( $post_type->labels->singular_name );

		wp_nonce_field( 'whatsappsupport_data', 'whatsappsupport_nonce' );
		?>
        <p>
            <label for="whatsappsupport_message"><?php _e( 'Call to action', 'whatsapp-support' ); ?></label><br>
            <textarea name="whatsappsupport_message" rows="2" class="large-text"><?php echo $metadata['message_text']; ?></textarea>
        </p>            <p>
            <label for="whatsappsupport_message_send"><?php _e( 'Message', 'whatsapp-support' ); ?></label><br>
            <textarea name="whatsappsupport_message_send" rows="2" class="large-text"><?php echo $metadata['message_send']; ?></textarea>
        </p>            <p>
            <input type="checkbox" name="whatsappsupport_hide" id="whatsappsupport_hide" value="1" <?php echo $metadata['hide'] ? 'checked' : ''; ?>>
            <label for="whatsappsupport_hide"><?php printf( __( 'Hide on this %s', 'whatsapp-support' ), $post_type_name ); ?></label>
        </p>
		<?php
	}

	/**
	 * Save meta data from "WhatsApp Support" Meta Box on post save
	 *
	 * @since    1.1.0
	 * @access   public
	 * @return   void
	 */
	public function save_post( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) ||
		     ! isset( $_POST['whatsappsupport_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['whatsappsupport_nonce'], 'whatsappsupport_data' ) ) {
			return;
		}

		// Delete empty/false fields
		$metadata = array_filter( array(
			'message_text' => trim( $_POST['whatsappsupport_message'] ),
			'message_send' => trim( $_POST['whatsappsupport_message_send'] ),
			'hide'         => isset( $_POST['whatsappsupport_hide'] ) ? 1 : 0,
		) );

		if ( count( $metadata ) ) {
			update_post_meta( $post_id, '_whatsappsupport', $metadata );
		} else {
			delete_post_meta( $post_id, '_whatsappsupport' );
		}
	}
}
