<?php
/**
 * Cartflows Pro Helper.
 *
 * @package Cartflows Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Pro_Gb_Helper' ) ) {

	/**
	 * Class Cartflows_Pro_Gb_Helper.
	 */
	final class Cartflows_Pro_Gb_Helper {


		/**
		 * Member Variable
		 *
		 * @since 1.6.13
		 * @var instance
		 */
		private static $instance;

		/**
		 * Member Variable
		 *
		 * @since 1.6.13
		 * @var instance
		 */
		public static $block_list;

		/**
		 * Current Block List
		 *
		 * @since 1.6.13
		 * @var current_block_list
		 */
		public static $current_block_list = array();

		/**
		 * Page Blocks Variable
		 *
		 * @since 1.6.13
		 * @var instance
		 */
		public static $page_blocks;

		/**
		 * Stylesheet
		 *
		 * @since 1.6.13
		 * @var stylesheet
		 */
		public static $stylesheet;

		/**
		 * Script
		 *
		 * @since 1.6.13
		 * @var script
		 */
		public static $script;

		/**
		 * Cartflows Pro Block Flag
		 *
		 * @since 1.6.13
		 * @var cfp_flag
		 */
		public static $cfp_flag = false;

		/**
		 * Google fonts to enqueue
		 *
		 * @var gfonts
		 */
		public static $gfonts = array();

		/**
		 *  Initiator
		 *
		 * @since 1.6.13
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			require CARTFLOWS_PRO_DIR . 'modules/gutenberg/classes/class-cartflows-pro-block-config.php';
			require CARTFLOWS_PRO_DIR . 'modules/gutenberg/classes/class-cartflows-pro-block-helper.php';
			require CARTFLOWS_PRO_DIR . 'modules/gutenberg/classes/class-cartflows-pro-block-js.php';

			self::$block_list = Cartflows_Pro_Block_Config::get_block_attributes();

			add_action( 'wp', array( $this, 'wp_actions' ), 10 );
		}

		/**
		 * WP Actions.
		 */
		public function wp_actions() {

			if ( wcf()->utils->is_step_post_type() ) {

				$this->generate_assets();
				add_action( 'wp_enqueue_scripts', array( $this, 'block_assets' ), 10 );
				add_action( 'wp_head', array( $this, 'frontend_gfonts' ), 120 );
				add_action( 'wp_head', array( $this, 'print_stylesheet' ), 80 );
				add_action( 'wp_footer', array( $this, 'print_script' ), 1000 );
			}

		}
		/**
		 * Load the front end Google Fonts.
		 */
		public function frontend_gfonts() {

			$show_google_fonts = apply_filters( 'cfp_blocks_show_google_fonts', true );

			if ( empty( self::$gfonts ) || ! $show_google_fonts ) {
				return;
			}
			$link    = '';
			$subsets = array();
			foreach ( self::$gfonts as $key => $gfont_values ) {
				if ( ! empty( $link ) ) {
					$link .= '%7C'; // Append a new font to the string.
				}
				$link .= $gfont_values['fontfamily'];
				if ( ! empty( $gfont_values['fontvariants'] ) ) {
					$link .= ':';
					$link .= implode( ',', $gfont_values['fontvariants'] );
				}
				if ( ! empty( $gfont_values['fontsubsets'] ) ) {
					foreach ( $gfont_values['fontsubsets'] as $subset ) {
						if ( ! in_array( $subset, $subsets, true ) ) {
							array_push( $subsets, $subset );
						}
					}
				}
			}
			if ( ! empty( $subsets ) ) {
				$link .= '&amp;subset=' . implode( ',', $subsets );
			}

			if ( isset( $link ) && ! empty( $link ) ) {
				echo '<link id="cfp_show_google_fonts" href="//fonts.googleapis.com/css?family=' . esc_attr( str_replace( '|', '%7C', $link ) ) . '" rel="stylesheet">'; //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			}
		}

		/**
		 * Print the Script in footer.
		 */
		public function print_script() {

			if ( is_null( self::$script ) || '' === self::$script ) {
				return;
			}

			ob_start();
			?>
			<script type="text/javascript" id="cfp-script-frontend"><?php echo self::$script; //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?></script>
			<?php
			ob_end_flush();
		}

		/**
		 * Print the Stylesheet in header.
		 */
		public function print_stylesheet() {

			if ( is_null( self::$stylesheet ) || '' === self::$stylesheet ) {
				return;
			}

			ob_start();
			?>
			<style id="cpf-style-frontend"><?php echo self::$stylesheet; //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?></style>
			<?php
			ob_end_flush();
		}


		/**
		 * Generates stylesheet and appends in head tag.
		 *
		 * @since 1.6.13
		 */
		public function generate_assets() {

			$this_post = array();

			global $post;
			$this_post = $post;

			if ( ! is_object( $this_post ) ) {
				return;
			}

			/**
			 * Filters the post to build stylesheet for.
			 *
			 * @param \WP_Post $this_post The global post.
			 */
			$this_post = apply_filters( 'cpf_post_for_stylesheet', $this_post );

			$this->get_generated_stylesheet( $this_post );
		}

		/**
		 * Generates stylesheet in loop.
		 *
		 * @param object $this_post Current Post Object.
		 * @since 1.6.13
		 */
		public function get_generated_stylesheet( $this_post ) {

			if ( is_object( $this_post ) && isset( $this_post->ID ) && has_blocks( $this_post->ID ) && isset( $this_post->post_content ) ) {

				$blocks = $this->parse( $this_post->post_content );

				self::$page_blocks = $blocks;

				if ( ! is_array( $blocks ) || empty( $blocks ) ) {
					return;
				}

				$assets = $this->get_assets( $blocks );

				self::$stylesheet .= $assets['css'];
				self::$script     .= $assets['js'];
			}
		}

		/**
		 * Enqueue Gutenberg block assets for both frontend + backend.
		 *
		 * @since 1.6.13
		 */
		public function block_assets() {

			$block_list_for_assets = self::$current_block_list;

			$blocks = Cartflows_Pro_Block_Config::get_block_attributes();

			foreach ( $block_list_for_assets as $key => $curr_block_name ) {

				$js_assets = ( isset( $blocks[ $curr_block_name ]['js_assets'] ) ) ? $blocks[ $curr_block_name ]['js_assets'] : array();

				$css_assets = ( isset( $blocks[ $curr_block_name ]['css_assets'] ) ) ? $blocks[ $curr_block_name ]['css_assets'] : array();

				foreach ( $js_assets as $asset_handle => $val ) {
					// Scripts.
					wp_enqueue_script( $val );
				}

				foreach ( $css_assets as $asset_handle => $val ) {
					// Styles.
					wp_enqueue_style( $val );
				}
			}

		}

		/**
		 * Parse Guten Block.
		 *
		 * @param string $content the content string.
		 * @since 1.6.13
		 */
		public function parse( $content ) {

			global $wp_version;

			return ( version_compare( $wp_version, '5', '>=' ) ) ? parse_blocks( $content ) : gutenberg_parse_blocks( $content );
		}

		/**
		 * Generates stylesheet for reusable blocks.
		 *
		 * @param array $blocks Blocks array.
		 * @since 1.6.13
		 */
		public function get_assets( $blocks ) {

			$desktop = '';
			$tablet  = '';
			$mobile  = '';

			$tab_styling_css = '';
			$mob_styling_css = '';

			$js = '';

			foreach ( $blocks as $i => $block ) {

				if ( is_array( $block ) ) {

					if ( '' === $block['blockName'] ) {
						continue;
					}
					if ( 'core/block' === $block['blockName'] ) {
						$id = ( isset( $block['attrs']['ref'] ) ) ? $block['attrs']['ref'] : 0;

						if ( $id ) {
							$content = get_post_field( 'post_content', $id );

							$reusable_blocks = $this->parse( $content );

							$assets = $this->get_assets( $reusable_blocks );

							self::$stylesheet .= $assets['css'];
							self::$script     .= $assets['js'];

						}
					} else {

						$block_assets = $this->get_block_css_and_js( $block );
						// Get CSS for the Block.
						$css = $block_assets['css'];

						if ( isset( $css['desktop'] ) ) {
							$desktop .= $css['desktop'];
							$tablet  .= $css['tablet'];
							$mobile  .= $css['mobile'];
						}

						$js .= $block_assets['js'];
					}
				}
			}

			if ( ! empty( $tablet ) ) {
				$tab_styling_css .= '@media only screen and (max-width: ' . CFP_TABLET_BREAKPOINT . 'px) {';
				$tab_styling_css .= $tablet;
				$tab_styling_css .= '}';
			}

			if ( ! empty( $mobile ) ) {
				$mob_styling_css .= '@media only screen and (max-width: ' . CFP_MOBILE_BREAKPOINT . 'px) {';
				$mob_styling_css .= $mobile;
				$mob_styling_css .= '}';
			}

			return array(
				'css' => $desktop . $tab_styling_css . $mob_styling_css,
				'js'  => $js,
			);
		}

		/**
		 * Get Typography Dynamic CSS.
		 *
		 * @param  array  $attr The Attribute array.
		 * @param  string $slug The field slug.
		 * @param  string $selector The selector array.
		 * @param  array  $combined_selectors The combined selector array.
		 * @since  1.6.13
		 * @return bool|string
		 */
		public static function get_typography_css( $attr, $slug, $selector, $combined_selectors ) {

			$typo_css_desktop = array();
			$typo_css_tablet  = array();
			$typo_css_mobile  = array();

			$already_selectors_desktop = ( isset( $combined_selectors['desktop'][ $selector ] ) ) ? $combined_selectors['desktop'][ $selector ] : array();
			$already_selectors_tablet  = ( isset( $combined_selectors['tablet'][ $selector ] ) ) ? $combined_selectors['tablet'][ $selector ] : array();
			$already_selectors_mobile  = ( isset( $combined_selectors['mobile'][ $selector ] ) ) ? $combined_selectors['mobile'][ $selector ] : array();

			$family_slug     = ( '' === $slug ) ? 'fontFamily' : $slug . 'FontFamily';
			$weight_slug     = ( '' === $slug ) ? 'fontWeight' : $slug . 'FontWeight';
			$transform_slug  = ( '' === $slug ) ? 'fontTransform' : $slug . 'Transform';
			$decoration_slug = ( '' === $slug ) ? 'fontDecoration' : $slug . 'Decoration';
			$style_slug      = ( '' === $slug ) ? 'fontStyle' : $slug . 'FontStyle';

			$l_ht_slug      = ( '' === $slug ) ? 'lineHeight' : $slug . 'LineHeight';
			$f_sz_slug      = ( '' === $slug ) ? 'fontSize' : $slug . 'FontSize';
			$l_ht_type_slug = ( '' === $slug ) ? 'lineHeightType' : $slug . 'LineHeightType';
			$f_sz_type_slug = ( '' === $slug ) ? 'fontSizeType' : $slug . 'FontSizeType';
			$l_sp_slug      = ( '' === $slug ) ? 'letterSpacing' : $slug . 'LetterSpacing';
			$l_sp_type_slug = ( '' === $slug ) ? 'letterSpacingType' : $slug . 'LetterSpacingType';

			$text_transform  = isset( $attr[ $transform_slug ] ) ? $attr[ $transform_slug ] : 'normal';
			$text_decoration = isset( $attr[ $decoration_slug ] ) ? $attr[ $decoration_slug ] : 'none';
			$font_style      = isset( $attr[ $style_slug ] ) ? $attr[ $style_slug ] : 'normal';

			$typo_css_desktop[ $selector ] = array(
				'font-family'     => $attr[ $family_slug ],
				'text-transform'  => $text_transform,
				'text-decoration' => $text_decoration,
				'font-style'      => $font_style,
				'font-weight'     => $attr[ $weight_slug ],
				'font-size'       => ( isset( $attr[ $f_sz_slug ] ) ) ? self::get_css_value( $attr[ $f_sz_slug ], $attr[ $f_sz_type_slug ] ) : '',
				'line-height'     => ( isset( $attr[ $l_ht_slug ] ) ) ? self::get_css_value( $attr[ $l_ht_slug ], $attr[ $l_ht_type_slug ] ) : '',
				'letter-spacing'  => ( isset( $attr[ $l_sp_slug ] ) ) ? self::get_css_value( $attr[ $l_sp_slug ], $attr[ $l_sp_type_slug ] ) : '',
			);

			$typo_css_desktop[ $selector ] = array_merge(
				$typo_css_desktop[ $selector ],
				$already_selectors_desktop
			);

			$typo_css_tablet[ $selector ] = array(
				'font-size'      => ( isset( $attr[ $f_sz_slug . 'Tablet' ] ) ) ? self::get_css_value( $attr[ $f_sz_slug . 'Tablet' ], $attr[ $f_sz_type_slug ] ) : '',
				'line-height'    => ( isset( $attr[ $l_ht_slug . 'Tablet' ] ) ) ? self::get_css_value( $attr[ $l_ht_slug . 'Tablet' ], $attr[ $l_ht_type_slug ] ) : '',
				'letter-spacing' => ( isset( $attr[ $l_sp_slug . 'Tablet' ] ) ) ? self::get_css_value( $attr[ $l_sp_slug . 'Tablet' ], $attr[ $l_sp_type_slug ] ) : '',
			);

			$typo_css_tablet[ $selector ] = array_merge(
				$typo_css_tablet[ $selector ],
				$already_selectors_tablet
			);

			$typo_css_mobile[ $selector ] = array(
				'font-size'      => ( isset( $attr[ $f_sz_slug . 'Mobile' ] ) ) ? self::get_css_value( $attr[ $f_sz_slug . 'Mobile' ], $attr[ $f_sz_type_slug ] ) : '',
				'line-height'    => ( isset( $attr[ $l_ht_slug . 'Mobile' ] ) ) ? self::get_css_value( $attr[ $l_ht_slug . 'Mobile' ], $attr[ $l_ht_type_slug ] ) : '',
				'letter-spacing' => ( isset( $attr[ $l_sp_slug . 'Mobile' ] ) ) ? self::get_css_value( $attr[ $l_sp_slug . 'Mobile' ], $attr[ $l_sp_type_slug ] ) : '',
			);

			$typo_css_mobile[ $selector ] = array_merge(
				$typo_css_mobile[ $selector ],
				$already_selectors_mobile
			);

			return array(
				'desktop' => array_merge(
					$combined_selectors['desktop'],
					$typo_css_desktop
				),
				'tablet'  => array_merge(
					$combined_selectors['tablet'],
					$typo_css_tablet
				),
				'mobile'  => array_merge(
					$combined_selectors['mobile'],
					$typo_css_mobile
				),
			);
		}

		/**
		 * Get CSS value
		 *
		 * Syntax:
		 *
		 *  get_css_value( VALUE, UNIT );
		 *
		 * E.g.
		 *
		 *  get_css_value( VALUE, 'em' );
		 *
		 * @param string $value  CSS value.
		 * @param string $unit  CSS unit.
		 * @since 1.6.13
		 */
		public static function get_css_value( $value = '', $unit = '' ) {

			$css_val = '';

			if ( '' !== $value ) {
				$css_val = esc_attr( $value ) . $unit;
			}

			return $css_val;
		}

		/**
		 * Parse CSS into correct CSS syntax.
		 *
		 * @param array  $combined_selectors The combined selector array.
		 * @param string $id The selector ID.
		 * @since 1.6.13
		 */
		public static function generate_all_css( $combined_selectors, $id ) {

			return array(
				'desktop' => self::generate_css( $combined_selectors['desktop'], $id ),
				'tablet'  => self::generate_css( $combined_selectors['tablet'], $id ),
				'mobile'  => self::generate_css( $combined_selectors['mobile'], $id ),
			);
		}

		/**
		 * Parse CSS into correct CSS syntax.
		 *
		 * @param array  $selectors The block selectors.
		 * @param string $id The selector ID.
		 * @since 1.6.13
		 */
		public static function generate_css( $selectors, $id ) {
			$styling_css = '';

			if ( ! empty( $selectors ) ) {
				foreach ( $selectors as $key => $value ) {

					$css = '';

					foreach ( $value as $j => $val ) {

						if ( 'font-family' === $j && 'Default' === $val ) {
							continue;
						}

						if ( ! empty( $val ) || 0 === $val ) {
							if ( 'font-family' === $j ) {
								$css .= $j . ': "' . $val . '";';
							} else {
								$css .= $j . ': ' . $val . ';';
							}
						}
					}

					if ( ! empty( $css ) ) {
						$styling_css     .= $id;
						$styling_css     .= $key . '{';
							$styling_css .= $css . '}';
					}
				}
			}

			return $styling_css;
		}

		/**
		 * Adds Google fonts all blocks.
		 *
		 * @param array $load_google_font the blocks attr.
		 * @param array $font_family the blocks attr.
		 * @param array $font_weight the blocks attr.
		 * @param array $font_subset the blocks attr.
		 */
		public static function blocks_google_font( $load_google_font, $font_family, $font_weight, $font_subset ) {

			if ( true === $load_google_font ) {
				if ( ! array_key_exists( $font_family, self::$gfonts ) ) {
					$add_font                     = array(
						'fontfamily'   => $font_family,
						'fontvariants' => ( isset( $font_weight ) && ! empty( $font_weight ) ? array( $font_weight ) : array() ),
						'fontsubsets'  => ( isset( $font_subset ) && ! empty( $font_subset ) ? array( $font_subset ) : array() ),
					);
					self::$gfonts[ $font_family ] = $add_font;
				} else {
					if ( isset( $font_weight ) && ! empty( $font_weight ) && ! in_array( $font_weight, self::$gfonts[ $font_family ]['fontvariants'], true ) ) {
						array_push( self::$gfonts[ $font_family ]['fontvariants'], $font_weight );
					}
					if ( isset( $font_subset ) && ! empty( $font_subset ) && ! in_array( $font_subset, self::$gfonts[ $font_family ]['fontsubsets'], true ) ) {
						array_push( self::$gfonts[ $font_family ]['fontsubsets'], $font_subset );
					}
				}
			}
		}


		/**
		 * Generates CSS recurrsively.
		 *
		 * @param object $block The block object.
		 * @since 1.6.13
		 */
		public function get_block_css_and_js( $block ) {

			$block = (array) $block;

			$name     = $block['blockName'];
			$css      = array();
			$js       = '';
			$block_id = '';

			if ( isset( $name ) ) {

				if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
					$blockattr = $block['attrs'];
					if ( isset( $blockattr['block_id'] ) ) {
						$block_id = $blockattr['block_id'];
					}
				}

				self::$current_block_list[] = $name;

				if ( strpos( $name, 'wcfpb/' ) !== false ) {
					self::$cfp_flag = true;
				}

				switch ( $name ) {

					case 'wcfb/checkout-form':
						$css = Cartflows_Pro_Block_Helper::get_checkout_form_pro_css( $blockattr, $block_id );
						break;

					case 'wcfpb/offer-yes-no-link':
						$css = Cartflows_Pro_Block_Helper::get_offer_yes_no_link_css( $blockattr, $block_id );
						Cartflows_Pro_Block_JS::blocks_offer_yes_no_link_gfont( $blockattr );
						break;

					case 'wcfpb/offer-yes-no-button':
						$css = Cartflows_Pro_Block_Helper::get_offer_yes_no_button_css( $blockattr, $block_id );
						Cartflows_Pro_Block_JS::blocks_offer_yes_no_button_gfont( $blockattr );
						break;

					case 'wcfpb/offer-product-title':
						$css = Cartflows_Pro_Block_Helper::get_offer_product_title_css( $blockattr, $block_id );
						Cartflows_Pro_Block_JS::blocks_offer_product_title_gfont( $blockattr );
						break;

					case 'wcfpb/offer-product-description':
						$css = Cartflows_Pro_Block_Helper::get_offer_product_description_css( $blockattr, $block_id );
						Cartflows_Pro_Block_JS::blocks_offer_product_description_gfont( $blockattr );
						break;

					case 'wcfpb/offer-product-price':
						$css = Cartflows_Pro_Block_Helper::get_offer_product_price_css( $blockattr, $block_id );
						Cartflows_Pro_Block_JS::blocks_offer_product_price_gfont( $blockattr );
						break;

					case 'wcfpb/offer-product-quantity':
						$css = Cartflows_Pro_Block_Helper::get_offer_product_quantity_css( $blockattr, $block_id );
						Cartflows_Pro_Block_JS::blocks_offer_product_quantity_gfont( $blockattr );
						break;

					case 'wcfpb/offer-product-variation':
						$css = Cartflows_Pro_Block_Helper::get_offer_product_variation_css( $blockattr, $block_id );
						Cartflows_Pro_Block_JS::blocks_offer_product_variation_gfont( $blockattr );
						break;

					case 'wcfpb/offer-product-image':
						$css = Cartflows_Pro_Block_Helper::get_offer_product_image_css( $blockattr, $block_id );
						break;

					default:
						// Nothing to do here.
						break;
				}

				if ( isset( $block['innerBlocks'] ) ) {
					foreach ( $block['innerBlocks'] as $j => $inner_block ) {
						if ( 'core/block' === $inner_block['blockName'] ) {
							$id = ( isset( $inner_block['attrs']['ref'] ) ) ? $inner_block['attrs']['ref'] : 0;

							if ( $id ) {
								$content = get_post_field( 'post_content', $id );

								$reusable_blocks = $this->parse( $content );

								$assets = $this->get_assets( $reusable_blocks );

								self::$stylesheet .= $assets['css'];
								self::$script     .= $assets['js'];
							}
						} else {
							// Get CSS for the Block.
							$inner_assets    = $this->get_block_css_and_js( $inner_block );
							$inner_block_css = $inner_assets['css'];

							$css_desktop = ( isset( $css['desktop'] ) ? $css['desktop'] : '' );
							$css_tablet  = ( isset( $css['tablet'] ) ? $css['tablet'] : '' );
							$css_mobile  = ( isset( $css['mobile'] ) ? $css['mobile'] : '' );

							if ( isset( $inner_block_css['desktop'] ) ) {
								$css['desktop'] = $css_desktop . $inner_block_css['desktop'];
								$css['tablet']  = $css_tablet . $inner_block_css['tablet'];
								$css['mobile']  = $css_mobile . $inner_block_css['mobile'];
							}

							$js .= $inner_assets['js'];
						}
					}
				}

				self::$current_block_list = array_unique( self::$current_block_list );
			}
			return array(
				'css' => $css,
				'js'  => $js,
			);

		}

		/**
		 * Border attribute generation Function.
		 *
		 * @param  array $prefix   Attribute Prefix.
		 * @param array $default_args  default attributes args.
		 * @return array
		 */
		public static function generate_php_border_attribute( $prefix, $default_args = array() ) {

			$border_attr = array();

			$device = array( '', 'Tablet', 'Mobile' );

			foreach ( $device as $slug => $data ) {

				$border_attr[ "{$prefix}BorderTopWidth{$data}" ]          = array(
					'type' => 'number',
				);
				$border_attr[ "{$prefix}BorderLeftWidth{$data}" ]         = array(
					'type' => 'number',
				);
				$border_attr[ "{$prefix}BorderRightWidth{$data}" ]        = array(
					'type' => 'number',
				);
				$border_attr[ "{$prefix}BorderBottomWidth{$data}" ]       = array(
					'type' => 'number',
				);
				$border_attr[ "{$prefix}BorderTopLeftRadius{$data}" ]     = array(
					'type' => 'number',
				);
				$border_attr[ "{$prefix}BorderTopRightRadius{$data}" ]    = array(
					'type' => 'number',
				);
				$border_attr[ "{$prefix}BorderBottomLeftRadius{$data}" ]  = array(
					'type' => 'number',
				);
				$border_attr[ "{$prefix}BorderBottomRightRadius{$data}" ] = array(
					'type' => 'number',
				);
				$border_attr[ "{$prefix}BorderRadiusUnit{$data}" ]        = array(
					'type' => 'number',
				);
			}

			$border_attr[ "{$prefix}BorderStyle" ]      = array(
				'type' => 'string',
			);
			$border_attr[ "{$prefix}BorderColor" ]      = array(
				'type' => 'string',
			);
			$border_attr[ "{$prefix}BorderHColor" ]     = array(
				'type' => 'string',
			);
			$border_attr[ "{$prefix}BorderLink" ]       = array(
				'type'    => 'boolean',
				'default' => true,
			);
			$border_attr[ "{$prefix}BorderRadiusLink" ] = array(
				'type'    => 'boolean',
				'default' => true,
			);

			return $border_attr;
		}

		/**
		 * Border attribute generation Function.
		 *
		 * @param  array $prefix   Attribute Prefix.
		 * @param array $default_args  default attributes args.
		 * @return array
		 */
		public static function generate_border_attribute( $prefix, $default_args = array() ) {
			$defaults = wp_parse_args(
				$default_args,
				array(
					// Width.
					'borderTopWidth'                => '',
					'borderRightWidth'              => '',
					'borderBottomWidth'             => '',
					'borderLeftWidth'               => '',
					'borderTopWidthTablet'          => '',
					'borderRightWidthTablet'        => '',
					'borderBottomWidthTablet'       => '',
					'borderLeftWidthTablet'         => '',
					'borderTopWidthMobile'          => '',
					'borderRightWidthMobile'        => '',
					'borderBottomWidthMobile'       => '',
					'borderLeftWidthMobile'         => '',
					// Radius.
					'borderTopLeftRadius'           => '',
					'borderTopRightRadius'          => '',
					'borderBottomRightRadius'       => '',
					'borderBottomLeftRadius'        => '',
					'borderTopLeftRadiusTablet'     => '',
					'borderTopRightRadiusTablet'    => '',
					'borderBottomRightRadiusTablet' => '',
					'borderBottomLeftRadiusTablet'  => '',
					'borderTopLeftRadiusMobile'     => '',
					'borderTopRightRadiusMobile'    => '',
					'borderBottomRightRadiusMobile' => '',
					'borderBottomLeftRadiusMobile'  => '',
					// unit.
					'borderRadiusUnit'              => 'px',
					'borderRadiusUnitTablet'        => 'px',
					'borderRadiusUnitMobile'        => 'px',
					// common.
					'borderStyle'                   => 'none',
					'borderColor'                   => '',
					'borderHColor'                  => '',
				)
			);

			$border_attr = array();

			$device = array( '', 'Tablet', 'Mobile' );

			foreach ( $device as $slug => $data ) {

				$border_attr[ "{$prefix}BorderTopWidth{$data}" ]          = $defaults[ "borderTopWidth{$data}" ];
				$border_attr[ "{$prefix}BorderLeftWidth{$data}" ]         = $defaults[ "borderLeftWidth{$data}" ];
				$border_attr[ "{$prefix}BorderRightWidth{$data}" ]        = $defaults[ "borderRightWidth{$data}" ];
				$border_attr[ "{$prefix}BorderBottomWidth{$data}" ]       = $defaults[ "borderBottomWidth{$data}" ];
				$border_attr[ "{$prefix}BorderTopLeftRadius{$data}" ]     = $defaults[ "borderTopLeftRadius{$data}" ];
				$border_attr[ "{$prefix}BorderTopRightRadius{$data}" ]    = $defaults[ "borderTopRightRadius{$data}" ];
				$border_attr[ "{$prefix}BorderBottomLeftRadius{$data}" ]  = $defaults[ "borderBottomLeftRadius{$data}" ];
				$border_attr[ "{$prefix}BorderBottomRightRadius{$data}" ] = $defaults[ "borderBottomLeftRadius{$data}" ];
				$border_attr[ "{$prefix}BorderRadiusUnit{$data}" ]        = $defaults[ "borderRadiusUnit{$data}" ];
			}

			$border_attr[ "{$prefix}BorderStyle" ]  = $defaults['borderStyle'];
			$border_attr[ "{$prefix}BorderColor" ]  = $defaults['borderColor'];
			$border_attr[ "{$prefix}BorderHColor" ] = $defaults['borderHColor'];
			return $border_attr;
		}

		/**
		 * Border CSS generation Function.
		 *
		 * @param  array  $attr   Attribute List.
		 * @param  string $prefix Attribuate prefix .
		 * @param  string $device Responsive.
		 * @return array         border css array.
		 */
		public static function generate_border_css( $attr, $prefix, $device = 'desktop' ) {
			$gen_border_css = array();
			if ( 'tablet' === $device ) {
				if ( 'none' !== $attr[ $prefix . 'BorderStyle' ] ) {
					$gen_border_css['border-top-width']    = self::get_css_value( $attr[ $prefix . 'BorderTopWidthTablet' ], 'px' );
					$gen_border_css['border-left-width']   = self::get_css_value( $attr[ $prefix . 'BorderLeftWidthTablet' ], 'px' );
					$gen_border_css['border-right-width']  = self::get_css_value( $attr[ $prefix . 'BorderRightWidthTablet' ], 'px' );
					$gen_border_css['border-bottom-width'] = self::get_css_value( $attr[ $prefix . 'BorderBottomWidthTablet' ], 'px' );
				}
				$gen_border_unit_tablet                       = isset( $attr[ $prefix . 'BorderRadiusUnitTablet' ] ) ? $attr[ $prefix . 'BorderRadiusUnitTablet' ] : 'px';
				$gen_border_css['border-top-left-radius']     = self::get_css_value( $attr[ $prefix . 'BorderTopLeftRadiusTablet' ], $gen_border_unit_tablet );
				$gen_border_css['border-top-right-radius']    = self::get_css_value( $attr[ $prefix . 'BorderTopRightRadiusTablet' ], $gen_border_unit_tablet );
				$gen_border_css['border-bottom-left-radius']  = self::get_css_value( $attr[ $prefix . 'BorderBottomLeftRadiusTablet' ], $gen_border_unit_tablet );
				$gen_border_css['border-bottom-right-radius'] = self::get_css_value( $attr[ $prefix . 'BorderBottomRightRadiusTablet' ], $gen_border_unit_tablet );
			} elseif ( 'mobile' === $device ) {
				if ( 'none' !== $attr[ $prefix . 'BorderStyle' ] ) {
					$gen_border_css['border-top-width']    = self::get_css_value( $attr[ $prefix . 'BorderTopWidthMobile' ], 'px' );
					$gen_border_css['border-left-width']   = self::get_css_value( $attr[ $prefix . 'BorderLeftWidthMobile' ], 'px' );
					$gen_border_css['border-right-width']  = self::get_css_value( $attr[ $prefix . 'BorderRightWidthMobile' ], 'px' );
					$gen_border_css['border-bottom-width'] = self::get_css_value( $attr[ $prefix . 'BorderBottomWidthMobile' ], 'px' );
				}
				$gen_border_unit_mobile                       = isset( $attr[ $prefix . 'BorderTopLeftRadiusMobile' ] ) ? $attr[ $prefix . 'BorderTopLeftRadiusMobile' ] : 'px';
				$gen_border_css['border-top-left-radius']     = self::get_css_value( $attr[ $prefix . 'BorderTopLeftRadiusMobile' ], $gen_border_unit_mobile );
				$gen_border_css['border-top-right-radius']    = self::get_css_value( $attr[ $prefix . 'BorderTopRightRadiusMobile' ], $gen_border_unit_mobile );
				$gen_border_css['border-bottom-left-radius']  = self::get_css_value( $attr[ $prefix . 'BorderBottomLeftRadiusMobile' ], $gen_border_unit_mobile );
				$gen_border_css['border-bottom-right-radius'] = self::get_css_value( $attr[ $prefix . 'BorderBottomRightRadiusMobile' ], $gen_border_unit_mobile );
			} else {
				if ( 'none' !== $attr[ $prefix . 'BorderStyle' ] ) {
					$gen_border_css['border-top-width']    = self::get_css_value( $attr[ $prefix . 'BorderTopWidth' ], 'px' );
					$gen_border_css['border-left-width']   = self::get_css_value( $attr[ $prefix . 'BorderLeftWidth' ], 'px' );
					$gen_border_css['border-right-width']  = self::get_css_value( $attr[ $prefix . 'BorderRightWidth' ], 'px' );
					$gen_border_css['border-bottom-width'] = self::get_css_value( $attr[ $prefix . 'BorderBottomWidth' ], 'px' );
				}
				$gen_border_unit                              = isset( $attr[ $prefix . 'BorderRadiusUnit' ] ) ? $attr[ $prefix . 'BorderRadiusUnit' ] : 'px';
				$gen_border_css['border-top-left-radius']     = self::get_css_value( $attr[ $prefix . 'BorderTopLeftRadius' ], $gen_border_unit );
				$gen_border_css['border-top-right-radius']    = self::get_css_value( $attr[ $prefix . 'BorderTopRightRadius' ], $gen_border_unit );
				$gen_border_css['border-bottom-left-radius']  = self::get_css_value( $attr[ $prefix . 'BorderBottomLeftRadius' ], $gen_border_unit );
				$gen_border_css['border-bottom-right-radius'] = self::get_css_value( $attr[ $prefix . 'BorderBottomRightRadius' ], $gen_border_unit );
			}
			$border_style                   = $attr[ $prefix . 'BorderStyle' ];
			$border_color                   = $attr[ $prefix . 'BorderColor' ];
			$gen_border_css['border-style'] = $border_style;
			$gen_border_css['border-color'] = $border_color;
			return $gen_border_css;
		}

		/**
		 * Generate advanced settings clsses.
		 *
		 * @param  array $attributes Blocks Attribute.
		 * @return array
		 */
		public function generate_advanced_setting_classes( $attributes ) {

			$is_visible               = array_key_exists( 'UAGHideDesktop', $attributes ) || array_key_exists( 'UAGHideTab', $attributes ) || array_key_exists( 'UAGHideMob', $attributes );
			$desktop_class            = $is_visible && isset( $attributes['UAGHideDesktop'] ) ? 'uag-hide-desktop' : '';
			$tab_class                = $is_visible && isset( $attributes['UAGHideTab'] ) ? 'uag-hide-tab' : '';
			$mob_class                = $is_visible && isset( $attributes['UAGHideMob'] ) ? 'uag-hide-mob' : '';
			$zindex_wrap              = array();
			$zindex_extention_enabled = ( isset( $attributes['zIndex'] ) || isset( $attributes['zIndexTablet'] ) || isset( $attributes['zIndexMobile'] ) );
			$zindex_desktop           = $zindex_extention_enabled && isset( $attributes['zIndex'] ) ? '--z-index-desktop:' . $attributes['zIndex'] . ';' : false;
			$zindex_tablet            = $zindex_extention_enabled && isset( $attributes['zIndexTablet'] ) ? '--z-index-tablet:' . $attributes['zIndexTablet'] . ';' : false;
			$zindex_mobile            = $zindex_extention_enabled && isset( $attributes['zIndexMobile'] ) ? '--z-index-mobile:' . $attributes['zIndexMobile'] . ';' : false;

			if ( $zindex_desktop ) {
				array_push( $zindex_wrap, $zindex_desktop );
			}
			if ( $zindex_tablet ) {
				array_push( $zindex_wrap, $zindex_tablet );
			}
			if ( $zindex_mobile ) {
				array_push( $zindex_wrap, $zindex_mobile );
			}

			return array(
				'desktop_class'            => $desktop_class,
				'tab_class'                => $tab_class,
				'mob_class'                => $mob_class,
				'zindex_wrap'              => $zindex_wrap,
				'zindex_extention_enabled' => $zindex_extention_enabled,
			);
		}


	}
	/**
	 *  Prepare if class 'Cartflows_Pro_Gb_Helper' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Gb_Helper::get_instance();
}
