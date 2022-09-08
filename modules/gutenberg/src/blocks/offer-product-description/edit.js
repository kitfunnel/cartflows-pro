/**
 * BLOCK: Offer Product Description Block
 */

// Import block dependencies and components.
import classnames from 'classnames';
import styling from './styling';
import CFP_Block_Icons from '../../../dist/blocks/controls/block-icons';
import BlockEditorPreviewCompatibility from '../../../config/BlockEditorPreviewCompatibility';

// Import all of our Text Options requirements.
import TextShadowControl from '../../components/text-shadow';
import TypographyControl from '../../components/typography';

// Import Web font loader for google fonts.
import WebfontLoader from '../../components/typography/fontloader';

const { __ } = wp.i18n;

const { BlockControls, InspectorControls, ColorPalette } = wp.blockEditor;

const { withSelect } = wp.data;

const {
	PanelBody,
	SelectControl,
	RangeControl,
	ButtonGroup,
	Button,
	TabPanel,
	ToggleControl,
	Dashicon,
} = wp.components;

const { Component, Fragment } = wp.element;

class OfferProductDescription extends Component {
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
			'wpcfp-offer-product-description-style-' +
				this.props.clientId.substr( 0, 8 )
		);
		document.head.appendChild( $style );
	}

	componentDidUpdate( prevProps ) {
		if (
			prevProps.attributes.shortDescription !==
			this.props.attributes.shortDescription
		) {
			const { setAttributes } = this.props;
			setAttributes( { isHtml: false } );
		}
		BlockEditorPreviewCompatibility(
			'wpcfp-offer-product-description-style-' +
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
				shortDescription,
				// Alignment
				textAlignment,
				//Padding
				paddingTypeDesktop,
				paddingTypeTablet,
				paddingTypeMobile,
				vPaddingDesktop,
				hPaddingDesktop,
				vPaddingTablet,
				hPaddingTablet,
				vPaddingMobile,
				hPaddingMobile,
				// Text Color
				textColor,
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

		const offerProductDescriptionGeneralSettings = () => {
			return (
				<PanelBody
					title={ __( 'General', 'cartflows-pro' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Short Description', 'cartflows-pro' ) }
						checked={ shortDescription }
						onChange={ () =>
							setAttributes( {
								shortDescription: ! shortDescription,
							} )
						}
					/>

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

		const offerProductDescriptionStyleSettings = () => {
			return (
				<PanelBody
					title={ __( 'Style', 'cartflows-pro' ) }
					initialOpen={ false }
				>
					<p className="cfp-setting-label">
						{ __( 'Text Color', 'cartflows-pro' ) }
						<span className="components-base-control__label">
							<span
								className="component-color-indicator"
								style={ { backgroundColor: textColor } }
							></span>
						</span>
					</p>
					<ColorPalette
						value={ textColor }
						onChange={ ( colorValue ) =>
							setAttributes( { textColor: colorValue } )
						}
						allowReset
					/>

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

		const offerProductDescriptionSpacingSettings = () => {
			return (
				<PanelBody
					title={ __( 'Spacing', 'cartflows-pro' ) }
					initialOpen={ false }
				>
					<TabPanel
						className="cfp-size-type-field-tabs cfp-size-type-field__common-tabs cfp-inline-margin"
						activeClass="active-tab"
						tabs={ [
							{
								name: 'desktop',
								title: <Dashicon icon="desktop" />,
								className:
									'cfp-desktop-tab cfp-responsive-tabs',
							},
							{
								name: 'tablet',
								title: <Dashicon icon="tablet" />,
								className: 'cfp-tablet-tab cfp-responsive-tabs',
							},
							{
								name: 'mobile',
								title: <Dashicon icon="smartphone" />,
								className: 'cfp-mobile-tab cfp-responsive-tabs',
							},
						] }
					>
						{ ( tab ) => {
							let tabout;

							if ( 'mobile' === tab.name ) {
								tabout = (
									<Fragment>
										<ButtonGroup
											className="cfp-size-type-field"
											aria-label={ __(
												'Size Type',
												'cartflows-pro'
											) }
										>
											<Button
												key={ 'px' }
												className="cfp-size-btn"
												isSmall
												isPrimary={
													paddingTypeMobile === 'px'
												}
												aria-pressed={
													paddingTypeMobile === 'px'
												}
												onClick={ () =>
													setAttributes( {
														paddingTypeMobile: 'px',
													} )
												}
											>
												{ 'px' }
											</Button>
											<Button
												key={ '%' }
												className="cfp-size-btn"
												isSmall
												isPrimary={
													paddingTypeMobile === '%'
												}
												aria-pressed={
													paddingTypeMobile === '%'
												}
												onClick={ () =>
													setAttributes( {
														paddingTypeMobile: '%',
													} )
												}
											>
												{ '%' }
											</Button>
										</ButtonGroup>
										<h2>
											{ __( 'Padding', 'cartflows-pro' ) }
										</h2>
										<RangeControl
											label={
												CFP_Block_Icons.vertical_spacing
											}
											className={ 'cfp-margin-control' }
											value={ vPaddingMobile }
											onChange={ ( value ) =>
												setAttributes( {
													vPaddingMobile: value,
												} )
											}
											min={ 0 }
											max={ 100 }
											allowReset
										/>
										<RangeControl
											label={
												CFP_Block_Icons.horizontal_spacing
											}
											className={ 'cfp-margin-control' }
											value={ hPaddingMobile }
											onChange={ ( value ) =>
												setAttributes( {
													hPaddingMobile: value,
												} )
											}
											min={ 0 }
											max={ 100 }
											allowReset
										/>
									</Fragment>
								);
							} else if ( 'tablet' === tab.name ) {
								tabout = (
									<Fragment>
										<ButtonGroup
											className="cfp-size-type-field"
											aria-label={ __(
												'Size Type',
												'cartflows-pro'
											) }
										>
											<Button
												key={ 'px' }
												className="cfp-size-btn"
												isSmall
												isPrimary={
													paddingTypeTablet === 'px'
												}
												aria-pressed={
													paddingTypeTablet === 'px'
												}
												onClick={ () =>
													setAttributes( {
														paddingTypeTablet: 'px',
													} )
												}
											>
												{ 'px' }
											</Button>
											<Button
												key={ '%' }
												className="cfp-size-btn"
												isSmall
												isPrimary={
													paddingTypeTablet === '%'
												}
												aria-pressed={
													paddingTypeTablet === '%'
												}
												onClick={ () =>
													setAttributes( {
														paddingTypeTablet: '%',
													} )
												}
											>
												{ '%' }
											</Button>
										</ButtonGroup>
										<h2>
											{ __( 'Padding', 'cartflows-pro' ) }
										</h2>
										<RangeControl
											label={
												CFP_Block_Icons.vertical_spacing
											}
											className={ 'cfp-margin-control' }
											value={ vPaddingTablet }
											onChange={ ( value ) =>
												setAttributes( {
													vPaddingTablet: value,
												} )
											}
											min={ 0 }
											max={ 100 }
											allowReset
										/>
										<RangeControl
											label={
												CFP_Block_Icons.horizontal_spacing
											}
											className={ 'cfp-margin-control' }
											value={ hPaddingTablet }
											onChange={ ( value ) =>
												setAttributes( {
													hPaddingTablet: value,
												} )
											}
											min={ 0 }
											max={ 100 }
											allowReset
										/>
									</Fragment>
								);
							} else {
								tabout = (
									<Fragment>
										<ButtonGroup className="cfp-size-type-field">
											<Button
												key={ 'px' }
												className="cfp-size-btn"
												isSmall
												isPrimary={
													paddingTypeDesktop === 'px'
												}
												aria-pressed={
													paddingTypeDesktop === 'px'
												}
												onClick={ () =>
													setAttributes( {
														paddingTypeDesktop:
															'px',
													} )
												}
											>
												{ 'px' }
											</Button>
											<Button
												key={ '%' }
												className="cfp-size-btn"
												isSmall
												isPrimary={
													paddingTypeDesktop === '%'
												}
												aria-pressed={
													paddingTypeDesktop === '%'
												}
												onClick={ () =>
													setAttributes( {
														paddingTypeDesktop: '%',
													} )
												}
											>
												{ '%' }
											</Button>
										</ButtonGroup>
										<h2>
											{ __( 'Padding', 'cartflows-pro' ) }
										</h2>
										<RangeControl
											label={
												CFP_Block_Icons.vertical_spacing
											}
											className={ 'cfp-margin-control' }
											value={ vPaddingDesktop }
											onChange={ ( value ) =>
												setAttributes( {
													vPaddingDesktop: value,
												} )
											}
											min={ 0 }
											max={ 100 }
											allowReset
										/>
										<RangeControl
											label={
												CFP_Block_Icons.horizontal_spacing
											}
											className={ 'cfp-margin-control' }
											value={ hPaddingDesktop }
											onChange={ ( value ) =>
												setAttributes( {
													hPaddingDesktop: value,
												} )
											}
											min={ 0 }
											max={ 100 }
											allowReset
										/>
									</Fragment>
								);
							}

							return <div>{ tabout }</div>;
						} }
					</TabPanel>
				</PanelBody>
			);
		};

		return (
			<Fragment>
				<BlockControls></BlockControls>
				<InspectorControls>
					{ offerProductDescriptionGeneralSettings() }
					{ offerProductDescriptionStyleSettings() }
					{ offerProductDescriptionSpacingSettings() }
				</InspectorControls>
				<div
					className={ classnames(
						className,
						`cfp-block-${ this.props.clientId.substr( 0, 8 ) }`
					) }
				>
					<div className="wpcfp__offer-product-description">
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

// export default OfferProductDescription;

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
				action: 'wpcfp_offer_product_description_shortcode',
				nonce: cfp_blocks_info.wpcfp_ajax_nonce,
				id: cfp_blocks_info.ID,
				shortDescription: props.attributes.shortDescription,
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
} )( OfferProductDescription );
