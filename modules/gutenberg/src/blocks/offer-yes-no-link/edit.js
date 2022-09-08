/**
 * BLOCK: Offer Yes No Link Block
 */

// Import block dependencies and components.
import classnames from 'classnames';
import styling from './styling';
import CFP_Block_Icons from '../../../dist/blocks/controls/block-icons';
import BlockEditorPreviewCompatibility from '../../../config/BlockEditorPreviewCompatibility';

import CFPIcon from '../../../dist/blocks/controls/CFPIcon.json';
import FontIconPicker from '@fonticonpicker/react-fonticonpicker';
import renderSVG from '../../../dist/blocks/controls/render-icon';
// Import all of our Text Options requirements.
import TypographyControl from '../../components/typography';

// Import Web font loader for google fonts.
import WebfontLoader from '../../components/typography/fontloader';

const { __ } = wp.i18n;

const { InspectorControls, RichText, ColorPalette } = wp.blockEditor;

const { PanelBody, SelectControl, RangeControl, TabPanel } = wp.components;

const svg_icons = Object.keys( CFPIcon );

const { Component, Fragment } = wp.element;
const { withSelect } = wp.data;

class OfferYesNoLink extends Component {
	constructor() {
		super( ...arguments );
		this.getIcon = this.getIcon.bind( this );
	}

	getIcon( value ) {
		this.props.setAttributes( { icon: value } );
	}

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
			'wpcfp-offer-yes-no-link-style-' +
				this.props.clientId.substr( 0, 8 )
		);
		document.head.appendChild( $style );
	}

	componentDidUpdate() {
		BlockEditorPreviewCompatibility(
			'wpcfp-offer-yes-no-link-style-' +
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
				// Offer Action
				offerAction,
				// Icon
				icon,
				iconSize,
				iconPosition,
				iconSpacing,
				linkText,
				// Alignment
				linkTextAlignment,
				//Margin
				topMargin,
				bottomMargin,
				// Text Color
				linkTextColor,
				linkTextHoverColor,
				// Title
				linkTextFontFamily,
				linkTextFontWeight,
				linkTextFontSubset,
				linkTextFontSize,
				linkTextFontSizeType,
				linkTextFontSizeMobile,
				linkTextFontSizeTablet,
				linkTextLineHeight,
				linkTextLineHeightType,
				linkTextLineHeightMobile,
				linkTextLineHeightTablet,
				linkTextLoadGoogleFonts,
			},
		} = this.props;

		let loadLinkTextGoogleFonts;

		if ( linkTextLoadGoogleFonts === true ) {
			const linktextconfig = {
				google: {
					families: [
						linkTextFontFamily +
							( linkTextFontWeight
								? ':' + linkTextFontWeight
								: '' ),
					],
				},
			};

			loadLinkTextGoogleFonts = (
				<WebfontLoader config={ linktextconfig }></WebfontLoader>
			);
		}

		// Icon properties.
		const icon_props = {
			icons: svg_icons,
			value: icon,
			onChange: this.getIcon,
			isMulti: false,
			renderFunc: renderSVG,
			noSelectedPlaceholder: __( 'Select Icon', 'cartflows-pro' ),
		};

		let icon_html = '';

		if ( '' !== icon ) {
			icon_html = (
				<div className="wpcfp__offer-yes-no-link-icon">
					{ renderSVG( icon ) }
				</div>
			);
		}

		const OfferYesNoLinkGeneralSettings = () => {
			return (
				<PanelBody
					title={ __( 'General', 'cartflows-pro' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Offer Action', 'cartflows-pro' ) }
						value={ offerAction }
						onChange={ ( value ) =>
							setAttributes( { offerAction: value } )
						}
						options={ [
							{
								value: 'accept_offer',
								label: __( 'Accept Offer', 'cartflows-pro' ),
							},
							{
								value: 'decline_offer',
								label: __( 'Reject Offer', 'cartflows-pro' ),
							},
						] }
					/>

					<hr className="cfp-editor__separator" />
					<h2>{ __( 'Icon', 'cartflows-pro' ) }</h2>
					<FontIconPicker { ...icon_props } />
					{ '' !== icon && (
						<Fragment>
							<SelectControl
								label={ __( 'Icon Position', 'cartflows-pro' ) }
								value={ iconPosition }
								onChange={ ( value ) =>
									setAttributes( { iconPosition: value } )
								}
								options={ [
									{
										value: 'before_link_text',
										label: __(
											'Before Link Text',
											'cartflows-pro'
										),
									},
									{
										value: 'after_link_text',
										label: __(
											'After Link Text',
											'cartflows-pro'
										),
									},
								] }
							/>
							<RangeControl
								label={ __( 'Icon Size', 'cartflows-pro' ) }
								value={ iconSize }
								onChange={ ( value ) =>
									setAttributes( { iconSize: value } )
								}
								min={ 0 }
								max={ 300 }
								beforeIcon=""
								allowReset
							/>
							<RangeControl
								label={ __( 'Icon Spacing', 'cartflows-pro' ) }
								value={ iconSpacing }
								onChange={ ( value ) =>
									setAttributes( { iconSpacing: value } )
								}
								min={ 0 }
								max={ 300 }
								beforeIcon=""
								allowReset
							/>
						</Fragment>
					) }
				</PanelBody>
			);
		};

		const OfferYesNoLinkStyleSettings = () => {
			return (
				<PanelBody
					title={ __( 'Style', 'cartflows-pro' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Text Alignment', 'cartflows-pro' ) }
						value={ linkTextAlignment }
						onChange={ ( value ) =>
							setAttributes( { linkTextAlignment: value } )
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
						] }
					/>

					<hr className="cfp-editor__separator" />
					<TypographyControl
						label={ __( 'Typography', 'cartflows-pro' ) }
						attributes={ attributes }
						setAttributes={ setAttributes }
						loadGoogleFonts={ {
							value: linkTextLoadGoogleFonts,
							label: 'linkTextLoadGoogleFonts',
						} }
						fontFamily={ {
							value: linkTextFontFamily,
							label: 'linkTextFontFamily',
						} }
						fontWeight={ {
							value: linkTextFontWeight,
							label: 'linkTextFontWeight',
						} }
						fontSubset={ {
							value: linkTextFontSubset,
							label: 'linkTextFontSubset',
						} }
						fontSizeType={ {
							value: linkTextFontSizeType,
							label: 'linkTextFontSizeType',
						} }
						fontSize={ {
							value: linkTextFontSize,
							label: 'linkTextFontSize',
						} }
						fontSizeMobile={ {
							value: linkTextFontSizeMobile,
							label: 'linkTextFontSizeMobile',
						} }
						fontSizeTablet={ {
							value: linkTextFontSizeTablet,
							label: 'linkTextFontSizeTablet',
						} }
						lineHeightType={ {
							value: linkTextLineHeightType,
							label: 'linkTextLineHeightType',
						} }
						lineHeight={ {
							value: linkTextLineHeight,
							label: 'linkTextLineHeight',
						} }
						lineHeightMobile={ {
							value: linkTextLineHeightMobile,
							label: 'linkTextLineHeightMobile',
						} }
						lineHeightTablet={ {
							value: linkTextLineHeightTablet,
							label: 'linkTextLineHeightTablet',
						} }
					/>

					<hr className="cfp-editor__separator" />
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
														backgroundColor: linkTextColor,
													} }
												></span>
											</span>
										</p>
										<ColorPalette
											value={ linkTextColor }
											onChange={ ( colorValue ) =>
												setAttributes( {
													linkTextColor: colorValue,
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
														backgroundColor: linkTextHoverColor,
													} }
												></span>
											</span>
										</p>
										<ColorPalette
											value={ linkTextHoverColor }
											onChange={ ( colorValue ) =>
												setAttributes( {
													linkTextHoverColor: colorValue,
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
				</PanelBody>
			);
		};

		const OfferYesNoLinkSpacingSettings = () => {
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
				<InspectorControls>
					{ OfferYesNoLinkGeneralSettings() }
					{ OfferYesNoLinkStyleSettings() }
					{ OfferYesNoLinkSpacingSettings() }
				</InspectorControls>
				<div
					className={ classnames(
						className,
						`cfp-block-${ this.props.clientId.substr( 0, 8 ) }`
					) }
				>
					<div className="wpcfp__offer-yes-no-link">
						<a href="#" className="wpcfp__offer-yes-no-link-url">
							<div className="wpcfp__offer-yes-no-link-text-wrap">
								{ iconPosition === 'before_link_text' &&
									icon_html }
								<RichText
									placeholder={ __(
										'Add text…',
										'cartflows-pro'
									) }
									value={ linkText }
									tagName="span"
									onChange={ ( value ) => {
										setAttributes( { linkText: value } );
									} }
									className="wpcfp__offer-yes-no-link-text"
								/>
								{ iconPosition === 'after_link_text' &&
									icon_html }
							</div>
						</a>
					</div>
				</div>
				{ loadLinkTextGoogleFonts }
			</Fragment>
		);
	}
}

export default withSelect( ( select, props ) => {
	const { __GetPreviewDeviceType = null } = select( 'core/edit-post' );
	const device_type = __GetPreviewDeviceType
		? __GetPreviewDeviceType()
		: null;

	return {
		deviceType: device_type,
		attributes: {
			...props.attributes,
			deviceType: device_type,
		},
	};
} )( OfferYesNoLink );
