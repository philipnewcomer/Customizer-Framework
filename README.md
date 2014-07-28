# Customizer-Framework

*A lightweight and easy-to-use framework for the WordPress Customizer*

## Features

Provides a simple and intuitive API for registering Customizer settings, including advanced control types. Automatically sanitizes settings based on the control type. Eliminates the tedious task of registering a setting, control, and sanitization function for each individual Customizer setting.

The framework may be used by both plugins and themes, although since at this time the settings are saved as theme mods, any plugin settings will be specific to the active theme. Support for option type settings is planned.

*This software is currently in beta, and may be subject to major changes as it matures.*

## Why a Framework for the Customizer?

The recent WordPress Customizer API suffers from some of the same issues afflicting the old Settings API. The Settings API was overcomplicated, unintuitive, and confusing. The result was a crop of theme option frameworks that have sprung up to make it easier for developers to create theme options. The Customizer API is a bit better, but it's still more complicated than necessary, and registering Customizer settings is still a convoluted mess of settings functions, controls functions, and sanitization functions. Now, the ease of use which the theme option frameworks have provided for the Settings API is available for the Customizer, in the Customizer Framework plugin.

The Customizer Framework aims to be a lightweight wrapper around the convoluted Customizer API, which makes it fun to be a WordPress developer again. Developers can now focus their time on creating great themes that utilize the Customizer, not on writing 500 lines of code in order to create 10 Customizer settings. Okay, so I might be exaggerating a bit. But not by much. Do you really want to spend your time registering a Customizer setting, then registering a control for that setting, and then writing a sanitization function for that setting? And that's only for one setting! And then there's the advanced controls, such as image or color, that require you to instantiate their own control class, requiring even more convoluted and unintuitive code. And why should you even have to care about the differece between a setting and a control? Don't you have better things to spend your time on, like creating great WordPress themes? I thought so.

## Installation

Install like any other plugin. If you're a theme developer, then you already know how to install a plugin, and I won't bore you with the details.

If you're creating a theme for public release, I recommend that you consider using the [TGM Plugin Activation](http://tgmpluginactivation.com/) library to make it easy for your users to install the Customizer Framework after activating your theme.

You could also `include` the plugin it in your theme, but remember to check for the existince of the plugin version first to avoid conflicts.

## Frequently Asked Questions

**Q: How do I retrieve the saved settings?**
A: The Customizer Framework saves its settings using the [Theme Modification API](http://codex.wordpress.org/Theme_Modification_API), so you would use [get_theme_mod()](http://codex.wordpress.org/Function_Reference/get_theme_mod) to retrive the saved value.

## Usage

Activate the plugin, or `include` it in your theme.

Before creating any settings, you need to create any new Customizer [sections](http://codex.wordpress.org/Class_Reference/WP_Customize_Manager/add_section) that you wish to use. Any custom sections need to exist before you can add settings to them.

Finally, initialize a new `CustomizerFramework` class, and add your settings:

	function mytheme_register_settings( $settings ) {
	
		$settings[] = array(
			'id'      => 'example_setting',
			'label'   => 'Example Setting Label',
			'section' => 'example-section',
			'type'    => 'text', // Optional, defaults to 'text'
			'default' => 'Example section default text', // Optional
		);
	
	}
	add_filter( 'customizer_framework_settings', 'mytheme_register_settings' );

## Setting Types

Here are the currently available setting types:

* `checkbox`
* `color`
* `dropdown-pages`
* `image`
* `radio`
* `select`
* `text`
* `textarea` (requires WordPress 4.0)

The `radio` and `select` types require an additional `choices` parameter, containing an array of the valid choices:

	'choices' => array(
		'choice_1' => 'Choice 1',
		'choice_2' => 'Choice 2',
		'choice_3' => 'Choice 3',
	),
	'default' => 'choice_1',

In addition, on WordPress 4.0, you can specify any additional HTML5 input types, such as `url` or `date`. You can also include an `atts` parameter, containing an array of additional input attributes which should be applied to the input:

	'type' => 'url',
	'atts' => array(
		'placeholder' => 'http://',
		'class'       => 'a-custom-css-class',
	),

## Sanitization

All settings are sanitized automatically, based on the setting type. If you wish to specify your own sanitization function for a setting, add a `sanitize_cb` parameter, containing the function name to be called, which should return the sanitized value.

	'sanitize_cb' => 'my_custom_example_setting_sanitization_function',

## Changelog

**0.1** - Initial beta release.

