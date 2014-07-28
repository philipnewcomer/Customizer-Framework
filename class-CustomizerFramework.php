<?php
/**
 * Copyright (C) 2014 Philip Newcomer
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class CustomizerFramework {

	public $registered_settings;
	public $legacy_valid_control_types;

	public function __construct() {

		$this->registered_settings        = array();
		$this->legacy_valid_control_types = array(
			'checkbox',
			'color',
			'dropdown-pages',
			'file',
			'image',
			'radio',
			'select',
			'text',
		);

		add_action( 'customize_register', array( $this, 'register_settings' ) );
		add_action( 'customize_register', array( $this, 'enqueue_sanitization' ) );

	}

	/**
	 * Enqueues the sanitization function.
	 */
	public function enqueue_sanitization() {

		add_filter( 'sanitize_option_theme_mods_' . get_option( 'stylesheet' ), array( $this, 'sanitize' ) );

	}

	/**
	 * Registers with WordPress all registered framework settings
	 */
	public function register_settings( $customizer ) {

		foreach( $this->registered_settings as $setting_id => $setting ) :

			extract( $setting );

			$customizer->add_setting(
				$setting_id,
				array(
					'default' => $default,
				)
			);

			switch( $type ) {

				case 'color':
					$customizer->add_control(
						new WP_Customize_Color_Control(
							$customizer,
							$setting_id,
							array(
								'active_callback' => $active_callback,
								'description'     => $description,
								'input_attrs'     => $input_attrs,
								'label'           => $label,
								'priority'        => $weight,
								'section'         => $section,
								'settings'        => $setting_id,
							)
						)
					);
					break;

				case 'file':
					$customizer->add_control(
						new WP_Customize_Upload_Control(
							$customizer,
							$setting_id,
							array(
								'active_callback' => $active_callback,
								'description'     => $description,
								'input_attrs'     => $input_attrs,
								'label'           => $label,
								'priority'        => $weight,
								'section'         => $section,
								'settings'        => $setting_id,
							)
						)
					);
					break;

				case 'image':
					$customizer->add_control(
						new WP_Customize_Image_Control(
							$customizer,
							$setting_id,
							array(
								'active_callback' => $active_callback,
								'description'     => $description,
								'input_attrs'     => $input_attrs,
								'label'           => $label,
								'priority'        => $weight,
								'section'         => $section,
								'settings'        => $setting_id,
							)
						)
					);
					break;

				default:
					$customizer->add_control( $setting_id, array(
						'active_callback' => $active_callback,
						'choices'         => $choices,
						'description'     => $description,
						'input_attrs'     => $input_attrs,
						'label'           => $label,
						'priority'        => $weight,
						'section'         => $section,
						'type'            => $type,
					) );

			}

		endforeach;

	}

	/**
	 * Sanitizes the setting values based on the setting type, and optionally the choices defined for the setting.
	 */
	public function sanitize( $data ) {

		foreach( $this->registered_settings as $setting_id => $setting ) :

			if ( ! array_key_exists( $setting_id, $data ) ) {
				continue;
			}

			$choices   = isset( $setting['choices'] ) ? $setting['choices'] : array();
			$input     = $data[ $setting_id ];
			$sanitized = null;

			if ( isset( $setting['sanitize_cb'] ) && is_callable( $setting['sanitize_cb'] ) ) :

				$sanitized = call_user_func( $setting['sanitize_cb'], $input );

			else :

				switch( $setting['type'] ) {

					case 'checkbox':
						$sanitized = ( 1 == $input ? 1 : '' );
						break;

					case 'color':
						$sanitized = sanitize_hex_color( $input );
						break;

					case 'dropdown-pages':
						$sanitized = intval( $input );
						break;

					case 'file':
					case 'image':
						$sanitized = esc_url( $input );
						break;

					case 'radio':
					case 'select':
						$sanitized = array_key_exists( $input, $choices ) ? $input : null;
						break;

					case 'text':
					case 'textarea':
						$sanitized = wp_kses_post( force_balance_tags( $input ) );
						break;

					case 'url':
						$sanitized = esc_url( $input );
						break;

					default:
						$sanitized = sanitize_text_field( $input );
						break;
				}

			endif;

			$data[ $setting_id ] = $sanitized;

		endforeach;

		return $data;

	}

	/**
	 * Adds a setting to the $registered_settings array.
	 */
	public function add_setting( $setting ) {

		// Make sure the basic requirements for registering a setting are included.
		if ( ! ( isset( $setting['id'] ) && isset( $setting['label'] ) && isset( $setting['section'] ) ) ) {
			return;
		}

		// Default to 'text' if no setting type is specified.
		if ( ! isset( $setting['type'] ) ) {
			$setting['type'] = 'text';
		}

		// If we're not running WordPress 4.0, change any unrecognized control types to "text".
		if ( ! version_compare( get_bloginfo( 'version' ), 4.0, '>=' ) && ! in_array( $setting['type'], $this->legacy_valid_control_types ) ) {
			$setting['type'] = 'text';
		}

		// If this is a radio or select setting, make sure there are choices specified.
		if ( ( 'radio' == $setting['type'] || 'select' == $setting['type'] ) && empty( $setting['choices'] ) ) {
			return;
		}

		$this->registered_settings[ $setting['id'] ] = array(
			'label'           => $setting['label'],
			'section'         => $setting['section'],
			'type'            => $setting['type'],
			'active_callback' => isset( $setting['active_cb'] )   ? $setting['active_cb']   : null,
			'choices'         => isset( $setting['choices'] )     ? $setting['choices']     : array(),
			'default'         => isset( $setting['default'] )     ? $setting['default']     : null,
			'description'     => isset( $setting['description'] ) ? $setting['description'] : null,
			'input_attrs'     => isset( $setting['atts'] )        ? $setting['atts']        : array(),
			'weight'          => isset( $setting['weight'] )      ? $setting['weight']      : null,
		);

	}

}
