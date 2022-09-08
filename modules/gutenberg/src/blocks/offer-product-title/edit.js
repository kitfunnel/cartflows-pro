/**
 * BLOCK: Offer Product title Block
 */

// Import block dependencies and components.
import classnames from 'classnames';
import BlockEditorPreviewCompatibility from '../../../config/BlockEditorPreviewCompatibility';

import styling from './styling';
import CFP_Block_Icons from '../../../dist/blocks/controls/block-icons';

// Import all of our Text Options requirements.
import TextShadowControl from '../../components/text-shadow';
import TypographyControl from '../../components/typography';

// Import Web font loader for google fonts.
import WebfontLoader from '../../components/typography/fontloader';

const { __ } = wp.i18n;

const { BlockControls, InspectorControls, ColorPalette } = wp.blockEditor;

const { withSelect } = wp.data;

const { PanelBody, SelectControl, RangeControl, TabPanel } = wp.components;

const { Component, Fragment } = wp.element;

class OfferProductTitle extends Component {
	componentDidMount() {
		// Assigning block_id in the attribute.
		this.props.setAttributes( {
			block_id: this.props.clientId.substr( 0, 8 ),
		} );

		// Assigning block_id in the attribute.
		this.props.setAttributes( { classMigrate: true } );

		// Pushing Style tag for this block css.
		const $style = document.createElement( 'style' );
		$style.setAttribute(
			'id',
			'wpcfp-offer-product-title-style-' +
				this.props.clientId.substr( 0, 8 )
		);
		document.head.appendChild( $style );
	}

	componentDidUpdate() {
		BlockEditorPreviewCompatibility(
			'wpcfp-offer-product-title-style-' +
				this.props.clientId.substr( 0, 8 ),
			styling( this.props )
		);
	}

	render() {
		// Setup the attributes
		const {
			className,
			attributes,
			setAttributes,
			attributes: {
				formJson,
				isHtml,
				// Alignment
				textAlignment,
				//Margin
				topMargin,
				bottomMargin,
				// Text Color
				textColor,
				textHoverColor,
				// Text
				textFontFamily,
				textFontWeight,
				textFontSubset,
				textFontSize,
				textFontSizeType,
				textFontSizeMobile,
				textFontSizeTablet,
				textLineHeight,
				textLineHeightType,
				textLineHeightMobile,
				textLineHeightTablet,
				textLoadGoogleFonts,
				// Text Shadow
				textShadowColor,
				textShadowHOffset,
				textShadowVOffset,
				textShadowBlur,
			},
		} = this.props;

		let html = '';

		if ( formJson && formJson.data.html ) {
			html = formJson.data.html;
		}

		let loadTextGoogleFonts;

		if ( true === textLoadGoogleFonts ) {
			const tconfig = {
				google: {
					families: [
						textFontFamily +
							( textFontWeight ? ':' + textFontWeight : '' ),
					],
				},
			};
			loadTextGoogleFonts = (
				<WebfontLoader config={ tconfig }></WebfontLoader>
			);
		}

		const offerProductTitleGeneralSettings = () => {
			return (
				<PanelBody
					title={ __( 'General', 'cartflows-pro' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Text Alignment', 'cartflows-pro' ) }
						value={ textAlignment }
						onChange={ ( value ) =>
							setAttributes( { textAlignment: value } )
						}
						options={ [
							{
								value: 'center',
								label: __( 'Center', 'cartflows-pro' ),
							},
							{
								value: 'left',
								label: __( 'Left', 'cartflows-pro' ),
							},
							{
								value: 'right',
								label: __( 'Right', 'cartflows-pro' ),
							},
							{
								value: 'justify',
								label: __( 'Justify', 'cartflows-pro' ),
							},
						] }
					/>
				</PanelBody>
			);
		};

		const offerProductTitleStyleSettings = () => {
			return (
				<PanelBody
					title={ __( 'Style', 'cartflows-pro' ) }
					initialOpen={ false }
				>
					<TabPanel
						className="cfp-inspect-tabs cfp-inspect-tabs-col-2"
						activeClass="active-tab"
						tabs={ [
							{
								name: 'normal',
								title: __( 'Normal', 'cartflows-pro' ),
								className: 'cfp-normal-tab',
							},
							{
								name: 'hover',
								title: __( 'Hover', 'cartflows-pro' ),
								className: 'cfp-focus-tab',
							},
						] }
					>
						{ ( tabName ) => {
							let tabout_color;
							if ( 'normal' === tabName.name ) {
								tabout_color = (
									<Fragment>
										<p className="cfp-setting-label">
											{ __(
												'Text Color',
												'cartflows-pro'
											) }
											<span className="components-base-control__label">
												<span
													className="component-color-indicator"
													style={ {
														backgroundColor: textColor,
													} }
												></span>
											</span>
										</p>
										<ColorPalette
											value={ textColor }
											onChange={ ( colorValue ) =>
												setAttributes( {
													textColor: colorValue,
												} )
											}
											allowReset
										/>
									</Fragment>
								);
							} else {
								tabout_color = (
									<Fragment>
										<p className="cfp-setting-label">
											{ __(
												'Text Color',
												'cartflows-pro'
											) }
											<span className="components-base-control__label">
												<span
													className="component-color-indicator"
													style={ {
														backgroundColor: textHoverColor,
													} }
												></span>
											</span>
										</p>
										<ColorPalette
											value={ textHoverColor }
											onChange={ ( colorValue ) =>
												setAttributes( {
													textHoverColor: colorValue,
												} )
											}
											allowReset
										/>
									</Fragment>
								);
							}
							return <div>{ tabout_color }</div>;
						} }
					</TabPanel>

					<hr className="cfp-editor__separator" />
					<TypographyControl
						label={ __( 'Typography', 'cartflows-pro' ) }
						attributes={ attributes }
						setAttributes={ setAttributes }
						loadGoogleFonts={ {
							value: textLoadGoogleFonts,
							label: 'textLoadGoogleFonts',
						} }
						fontFamily={ {
							value: textFontFamily,
							label: 'textFontFamily',
						} }
						fontWeight={ {
							value: textFontWeight,
							label: 'textFontWeight',
						} }
						fontSubset={ {
							value: textFontSubset,
							label: 'textFontSubset',
						} }
						fontSizeType={ {
							value: textFontSizeType,
							label: 'textFontSizeType',
						} }
						fontSize={ {
							value: textFontSize,
							label: 'textFontSize',
						} }
						fontSizeMobile={ {
							value: textFontSizeMobile,
							label: 'textFontSizeMobile',
						} }
						fontSizeTablet={ {
							value: textFontSizeTablet,
							label: 'textFontSizeTablet',
						} }
						lineHeightType={ {
							value: textLineHeightType,
							label: 'textLineHeightType',
						} }
						lineHeight={ {
							value: textLineHeight,
							label: 'textLineHeight',
						} }
						lineHeightMobile={ {
							value: textLineHeightMobile,
							label: 'textLineHeightMobile',
						} }
						lineHeightTablet={ {
							value: textLineHeightTablet,
							label: 'textLineHeightTablet',
						} }
					/>

					<hr className="cfp-editor__separator" />

					<TextShadowControl
						setAttributes={ setAttributes }
						label={ __( 'Text Shadow', 'cartflows-pro' ) }
						textShadowColor={ {
							value: textShadowColor,
							label: __( 'Color', 'cartflows-pro' ),
						} }
						textShadowHOffset={ {
							value: textShadowHOffset,
							label: __( 'Horizontal', 'cartflows-pro' ),
						} }
						textShadowVOffset={ {
							value: textShadowVOffset,
							label: __( 'Vertical', 'cartflows-pro' ),
						} }
						textShadowBlur={ {
							value: textShadowBlur,
							label: __( 'Blur', 'cartflows-pro' ),
						} }
					/>
				</PanelBody>
			);
		};

		const offerProductTitleSpacingSettings = () => {
			return (
				<PanelBody
					title={ __( 'Spacing', 'cartflows-pro' ) }
					initialOpen={ false }
				>
					<h2>{ __( 'Margin (px)', 'cartflows-pro' ) }</h2>
					<RangeControl
						label={ CFP_Block_Icons.top_margin }
						className={ 'cfp-margin-control' }
						value={ topMargin }
						onChange={ ( value ) =>
							setAttributes( { topMargin: value } )
						}
						min={ 0 }
						max={ 100 }
						allowReset
					/>
					<RangeControl
						label={ CFP_Block_Icons.bottom_margin }
						className={ 'cfp-margin-control' }
						value={ bottomMargin }
						onChange={ ( value ) =>
							setAttributes( { bottomMargin: value } )
						}
						min={ 0 }
						max={ 100 }
						allowReset
					/>
				</PanelBody>
			);
		};

		return (
			<Fragment>
				<BlockControls></BlockControls>
				<InspectorControls>
					{ offerProductTitleGeneralSettings() }
					{ offerProductTitleStyleSettings() }
					{ offerProductTitleSpacingSettings() }
				</InspectorControls>
				<div
					className={ classnames(
						className,
						`cfp-block-${ this.props.clientId.substr( 0, 8 ) }`
					) }
				>
					<div className="wpcfp__offer-product-title">
						{ isHtml && (
							<div dangerouslySetInnerHTML={ { __html: html } } />
						) }
					</div>
				</div>

				{ loadTextGoogleFonts }
			</Fragment>
		);
	}
}

// export default OfferProductTitle;

export default withSelect( ( select, props ) => {
	const { setAttributes } = props;
	const { isHtml } = props.attributes;
	let json_data = '';
	const { __GetPreviewDeviceType = null } = select( 'core/edit-post' );
	const device_type = __GetPreviewDeviceType
		? __GetPreviewDeviceType()
		: null;

	if ( ! isHtml ) {
		jQuery.ajax( {
			url: cfp_blocks_info.ajax_url,
			data: {
				action: 'wpcfp_offer_product_title_shortcode',
				nonce: cfp_blocks_info.wpcfp_ajax_nonce,
				id: cfp_blocks_info.ID,
				cartflows_gb: true,
			},
			dataType: 'json',
			type: 'POST',
			success( data ) {
				setAttributes( { isHtml: true } );
				setAttributes( { formJson: data } );
				json_data = data;
			},
		} );
	}

	return {
		formHTML: json_data,
		deviceType: device_type,
	};
} )( OfferProductTitle );
