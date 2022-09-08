/**
 * BLOCK: Offer Yes No Button Block
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
import GradientSettings from '../../components/gradient-settings';

// Import Web font loader for google fonts.
import WebfontLoader from '../../components/typography/fontloader';

const { __ } = wp.i18n;

const {
	BlockAlignmentToolbar,
	InspectorControls,
	RichText,
	ColorPalette,
	MediaUpload,
} = wp.blockEditor;

const {
	PanelBody,
	SelectControl,
	RangeControl,
	ButtonGroup,
	Button,
	TabPanel,
	Dashicon,
	BaseControl,
} = wp.components;

const svg_icons = Object.keys( CFPIcon );

const { Component, Fragment } = wp.element;

const { withSelect } = wp.data;

class OfferYesNoButton extends Component {
	constructor() {
		super( ...arguments );
		this.getIcon = this.getIcon.bind( this );
		// this.splitBlock = this.splitBlock.bind( this )
		this.onRemoveImage = this.onRemoveImage.bind( this );
		this.onSelectImage = this.onSelectImage.bind( this );
	}

	getIcon( value ) {
		this.props.setAttributes( { icon: value } );
	}

	/*
	 * Event to set Image as null while removing.
	 */
	onRemoveImage() {
		const { setAttributes } = this.props;

		setAttributes( { backgroundImage: null } );
	}

	/*
	 * Event to set Image as while adding.
	 */
	onSelectImage( media ) {
		const { setAttributes } = this.props;

		if ( ! media || ! media.url ) {
			setAttributes( { backgroundImage: null } );
			return;
		}

		if ( ! media.type || 'image' !== media.type ) {
			return;
		}

		setAttributes( { backgroundImage: media } );
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
			'wpcfp-offer-yes-no-button-style-' +
				this.props.clientId.substr( 0, 8 )
		);
		document.head.appendChild( $style );
	}

	componentDidUpdate() {
		BlockEditorPreviewCompatibility(
			'wpcfp-offer-yes-no-button-style-' +
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
				align,
				malign,
				talign,
				// Offer Action
				offerAction,
				// Icon
				icon,
				iconColor,
				iconHoverColor,
				iconSize,
				iconPosition,
				iconSpacing,
				offerYesNoButtonTitle,
				offerYesNoButtonSubTitle,
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
				//Border
				borderStyle,
				borderWidth,
				borderRadius,
				borderColor,
				borderHoverColor,
				// Text Color
				textColor,
				textHoverColor,
				// Button Background
				buttonHoverColor,
				backgroundType,
				backgroundImage,
				backgroundColor,
				backgroundPosition,
				backgroundAttachment,
				backgroundRepeat,
				backgroundSize,
				backgroundOpacity,
				// Title
				titleFontFamily,
				titleFontWeight,
				titleFontSubset,
				titleFontSize,
				titleFontSizeType,
				titleFontSizeMobile,
				titleFontSizeTablet,
				titleLineHeight,
				titleLineHeightType,
				titleLineHeightMobile,
				titleLineHeightTablet,
				titleLoadGoogleFonts,
				// Sub Title
				subTitleFontFamily,
				subTitleFontWeight,
				subTitleFontSubset,
				subTitleFontSize,
				subTitleFontSizeType,
				subTitleFontSizeMobile,
				subTitleFontSizeTablet,
				subTitleLineHeight,
				subTitleLineHeightType,
				subTitleLineHeightMobile,
				subTitleLineHeightTablet,
				subTitleLoadGoogleFonts,
				// Title Bottom Margin
				titleBottomSpacing,
			},
		} = this.props;

		let loadTitleGoogleFonts;
		let loadSubTitleGoogleFonts;

		if ( titleLoadGoogleFonts === true ) {
			const titleconfig = {
				google: {
					families: [
						titleFontFamily +
							( titleFontWeight ? ':' + titleFontWeight : '' ),
					],
				},
			};

			loadTitleGoogleFonts = (
				<WebfontLoader config={ titleconfig }></WebfontLoader>
			);
		}

		if ( subTitleLoadGoogleFonts === true ) {
			const subtitleconfig = {
				google: {
					families: [
						subTitleFontFamily +
							( subTitleFontWeight
								? ':' + subTitleFontWeight
								: '' ),
					],
				},
			};

			loadSubTitleGoogleFonts = (
				<WebfontLoader config={ subtitleconfig }></WebfontLoader>
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
				<div className="wpcfp__offer-yes-no-button-icon">
					{ renderSVG( icon ) }
				</div>
			);
		}

		const offerYesNoButtonGeneralSettings = () => {
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
								label: __( 'Decline Offer', 'cartflows-pro' ),
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
										value: 'before_title',
										label: __(
											'Before Title',
											'cartflows-pro'
										),
									},
									{
										value: 'after_title',
										label: __(
											'After Title',
											'cartflows-pro'
										),
									},
									{
										value: 'before_title_sub_title',
										label: __(
											'Before Title & Sub Title',
											'cartflows-pro'
										),
									},
									{
										value: 'after_title_sub_title',
										label: __(
											'After Title & Sub Title',
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

		const offerYesNoButtonStyleSettings = () => {
			return (
				<PanelBody
					title={ __( 'Style', 'cartflows-pro' ) }
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
										<h2>
											{ __(
												'Alignment',
												'cartflows-pro'
											) }
										</h2>
										<BaseControl>
											<BlockAlignmentToolbar
												value={ malign }
												onChange={ ( value ) =>
													setAttributes( {
														malign: value,
													} )
												}
												controls={ [
													'left',
													'center',
													'right',
												] }
												isCollapsed={ false }
											/>
										</BaseControl>
									</Fragment>
								);
							} else if ( 'tablet' === tab.name ) {
								tabout = (
									<Fragment>
										<h2>
											{ __(
												'Alignment',
												'cartflows-pro'
											) }
										</h2>
										<BaseControl>
											<BlockAlignmentToolbar
												value={ talign }
												onChange={ ( value ) =>
													setAttributes( {
														talign: value,
													} )
												}
												controls={ [
													'left',
													'center',
													'right',
												] }
												isCollapsed={ false }
											/>
										</BaseControl>
									</Fragment>
								);
							} else {
								tabout = (
									<Fragment>
										<h2>
											{ __(
												'Alignment',
												'cartflows-pro'
											) }
										</h2>
										<BaseControl>
											<BlockAlignmentToolbar
												value={ align }
												onChange={ ( value ) =>
													setAttributes( {
														align: value,
													} )
												}
												controls={ [
													'left',
													'center',
													'right',
													'full',
												] }
												isCollapsed={ false }
											/>
										</BaseControl>
										{ 'full' !== align && (
											<SelectControl
												label={ __(
													'Text Alignment',
													'cartflows-pro'
												) }
												value={ textAlignment }
												onChange={ ( value ) =>
													setAttributes( {
														textAlignment: value,
													} )
												}
												options={ [
													{
														value: 'center',
														label: __(
															'Center',
															'cartflows-pro'
														),
													},
													{
														value: 'left',
														label: __(
															'Left',
															'cartflows-pro'
														),
													},
													{
														value: 'right',
														label: __(
															'Right',
															'cartflows-pro'
														),
													},
												] }
											/>
										) }
									</Fragment>
								);
							}
							return <div>{ tabout }</div>;
						} }
					</TabPanel>

					<hr className="cfp-editor__separator" />
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

					<hr className="cfp-editor__separator" />
					<h2>{ __( 'Button', 'cartflows-pro' ) }</h2>

					<SelectControl
						label={ __( 'Background Type', 'cartflows-pro' ) }
						value={ backgroundType }
						onChange={ ( value ) =>
							setAttributes( { backgroundType: value } )
						}
						options={ [
							{
								value: 'color',
								label: __( 'Color', 'cartflows-pro' ),
							},
							{
								value: 'gradient',
								label: __( 'Gradient', 'cartflows-pro' ),
							},
							{
								value: 'image',
								label: __( 'Image', 'cartflows-pro' ),
							},
						] }
					/>
					{ 'color' === backgroundType && (
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
											<p className="cf-setting-label">
												{ __(
													'Background Color',
													'cartflows-pro'
												) }
												<span className="components-base-control__label">
													<span
														className="component-color-indicator"
														style={ {
															backgroundColor,
														} }
													></span>
												</span>
											</p>
											<ColorPalette
												value={ backgroundColor }
												onChange={ ( colorValue ) =>
													setAttributes( {
														backgroundColor: colorValue,
													} )
												}
												allowReset
											/>
										</Fragment>
									);
								} else {
									tabout_color = (
										<Fragment>
											<p className="cf-setting-label">
												{ __(
													'Background Color',
													'cartflows-pro'
												) }
												<span className="components-base-control__label">
													<span
														className="component-color-indicator"
														style={ {
															backgroundColor: buttonHoverColor,
														} }
													></span>
												</span>
											</p>
											<ColorPalette
												value={ buttonHoverColor }
												onChange={ ( colorValue ) =>
													setAttributes( {
														buttonHoverColor: colorValue,
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
					) }
					{ 'image' === backgroundType && (
						<Fragment>
							<BaseControl
								className="editor-bg-image-control"
								label={ __(
									'Background Image',
									'cartflows-pro'
								) }
								id="editor-bg-image-control"
							>
								<MediaUpload
									title={ __(
										'Select Background Image',
										'cartflows-pro'
									) }
									onSelect={ this.onSelectImage }
									allowedTypes={ [ 'image' ] }
									value={ backgroundImage }
									render={ ( { open } ) => (
										<Button isDefault onClick={ open }>
											{ ! backgroundImage
												? __(
														'Select Background Image',
														'cartflows-pro'
												  )
												: __(
														'Replace image',
														'cartflows-pro'
												  ) }
										</Button>
									) }
								/>
								{ backgroundImage && (
									<Button
										className="cf-rm-btn"
										onClick={ this.onRemoveImage }
										isLink
										isDestructive
									>
										{ __(
											'Remove Image',
											'cartflows-pro'
										) }
									</Button>
								) }
							</BaseControl>
							{ backgroundImage && (
								<Fragment>
									<SelectControl
										label={ __(
											'Image Position',
											'cartflows-pro'
										) }
										value={ backgroundPosition }
										onChange={ ( value ) =>
											setAttributes( {
												backgroundPosition: value,
											} )
										}
										options={ [
											{
												value: 'top-left',
												label: __(
													'Top Left',
													'cartflows-pro'
												),
											},
											{
												value: 'top-center',
												label: __(
													'Top Center',
													'cartflows-pro'
												),
											},
											{
												value: 'top-right',
												label: __(
													'Top Right',
													'cartflows-pro'
												),
											},
											{
												value: 'center-left',
												label: __(
													'Center Left',
													'cartflows-pro'
												),
											},
											{
												value: 'center-center',
												label: __(
													'Center Center',
													'cartflows-pro'
												),
											},
											{
												value: 'center-right',
												label: __(
													'Center Right',
													'cartflows-pro'
												),
											},
											{
												value: 'bottom-left',
												label: __(
													'Bottom Left',
													'cartflows-pro'
												),
											},
											{
												value: 'bottom-center',
												label: __(
													'Bottom Center',
													'cartflows-pro'
												),
											},
											{
												value: 'bottom-right',
												label: __(
													'Bottom Right',
													'cartflows-pro'
												),
											},
										] }
									/>
									<SelectControl
										label={ __(
											'Attachment',
											'cartflows-pro'
										) }
										value={ backgroundAttachment }
										onChange={ ( value ) =>
											setAttributes( {
												backgroundAttachment: value,
											} )
										}
										options={ [
											{
												value: 'fixed',
												label: __(
													'Fixed',
													'cartflows-pro'
												),
											},
											{
												value: 'scroll',
												label: __(
													'Scroll',
													'cartflows-pro'
												),
											},
										] }
									/>
									<SelectControl
										label={ __(
											'Repeat',
											'cartflows-pro'
										) }
										value={ backgroundRepeat }
										onChange={ ( value ) =>
											setAttributes( {
												backgroundRepeat: value,
											} )
										}
										options={ [
											{
												value: 'no-repeat',
												label: __(
													'No Repeat',
													'cartflows-pro'
												),
											},
											{
												value: 'repeat',
												label: __(
													'Repeat',
													'cartflows-pro'
												),
											},
											{
												value: 'repeat-x',
												label: __(
													'Repeat-x',
													'cartflows-pro'
												),
											},
											{
												value: 'repeat-y',
												label: __(
													'Repeat-y',
													'cartflows-pro'
												),
											},
										] }
									/>
									<SelectControl
										label={ __( 'Size', 'cartflows-pro' ) }
										value={ backgroundSize }
										onChange={ ( value ) =>
											setAttributes( {
												backgroundSize: value,
											} )
										}
										options={ [
											{
												value: 'auto',
												label: __(
													'Auto',
													'cartflows-pro'
												),
											},
											{
												value: 'cover',
												label: __(
													'Cover',
													'cartflows-pro'
												),
											},
											{
												value: 'contain',
												label: __(
													'Contain',
													'cartflows-pro'
												),
											},
										] }
									/>
								</Fragment>
							) }
						</Fragment>
					) }
					{ 'gradient' === backgroundType && (
						<Fragment>
							<GradientSettings
								attributes={ attributes }
								setAttributes={ setAttributes }
							/>
						</Fragment>
					) }
					{ ( 'color' === backgroundType ||
						( 'image' === backgroundType && backgroundImage ) ||
						'gradient' === backgroundType ) && (
						<RangeControl
							label={ __( 'Opacity', 'cartflows-pro' ) }
							value={ backgroundOpacity }
							onChange={ ( value ) =>
								setAttributes( { backgroundOpacity: value } )
							}
							min={ 0 }
							max={ 100 }
							allowReset
							initialPosition={ 0 }
						/>
					) }

					{ '' !== icon && (
						<Fragment>
							<hr className="cfp-editor__separator" />
							<h2>{ __( 'Icon', 'cartflows-pro' ) }</h2>
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
														'Icon Color',
														'cartflows-pro'
													) }
													<span className="components-base-control__label">
														<span
															className="component-color-indicator"
															style={ {
																backgroundColor: iconColor,
															} }
														></span>
													</span>
												</p>
												<ColorPalette
													value={ iconColor }
													onChange={ ( colorValue ) =>
														setAttributes( {
															iconColor: colorValue,
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
														'Icon Color',
														'cartflows-pro'
													) }
													<span className="components-base-control__label">
														<span
															className="component-color-indicator"
															style={ {
																backgroundColor: iconHoverColor,
															} }
														></span>
													</span>
												</p>
												<ColorPalette
													value={ iconHoverColor }
													onChange={ ( colorValue ) =>
														setAttributes( {
															iconHoverColor: colorValue,
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
						</Fragment>
					) }

					<hr className="cfp-editor__separator" />
					<h2>{ __( 'Text', 'cartflows-pro' ) }</h2>
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
					<h2>{ __( 'Border', 'cartflows-pro' ) }</h2>
					<SelectControl
						label={ __( 'Border Style', 'cartflows-pro' ) }
						value={ borderStyle }
						onChange={ ( value ) =>
							setAttributes( { borderStyle: value } )
						}
						options={ [
							{
								value: 'none',
								label: __( 'None', 'cartflows-pro' ),
							},
							{
								value: 'solid',
								label: __( 'Solid', 'cartflows-pro' ),
							},
							{
								value: 'dotted',
								label: __( 'Dotted', 'cartflows-pro' ),
							},
							{
								value: 'dashed',
								label: __( 'Dashed', 'cartflows-pro' ),
							},
							{
								value: 'double',
								label: __( 'Double', 'cartflows-pro' ),
							},
							{
								value: 'groove',
								label: __( 'Groove', 'cartflows-pro' ),
							},
							{
								value: 'inset',
								label: __( 'Inset', 'cartflows-pro' ),
							},
							{
								value: 'outset',
								label: __( 'Outset', 'cartflows-pro' ),
							},
							{
								value: 'ridge',
								label: __( 'Ridge', 'cartflows-pro' ),
							},
						] }
					/>
					{ 'none' !== borderStyle && (
						<RangeControl
							label={ __( 'Border Width', 'cartflows-pro' ) }
							value={ borderWidth }
							onChange={ ( value ) =>
								setAttributes( { borderWidth: value } )
							}
							min={ 0 }
							max={ 50 }
							allowReset
						/>
					) }
					<RangeControl
						label={ __( 'Border Radius', 'cartflows-pro' ) }
						value={ borderRadius }
						onChange={ ( value ) =>
							setAttributes( { borderRadius: value } )
						}
						min={ 0 }
						max={ 1000 }
						allowReset
					/>
					{ 'none' !== borderStyle && (
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
													'Border Color',
													'cartflows-pro'
												) }
												<span className="components-base-control__label">
													<span
														className="component-color-indicator"
														style={ {
															backgroundColor: borderColor,
														} }
													></span>
												</span>
											</p>
											<ColorPalette
												value={ borderColor }
												onChange={ ( colorValue ) =>
													setAttributes( {
														borderColor: colorValue,
													} )
												}
												allowReset
											/>
										</Fragment>
									);
								} else {
									tabout_color = (
										<Fragment>
											<p className="cf-setting-label">
												{ __(
													'Border Color',
													'cartflows-pro'
												) }
												<span className="components-base-control__label">
													<span
														className="component-color-indicator"
														style={ {
															backgroundColor: borderHoverColor,
														} }
													></span>
												</span>
											</p>
											<ColorPalette
												value={ borderHoverColor }
												onChange={ ( colorValue ) =>
													setAttributes( {
														borderHoverColor: colorValue,
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
					) }
				</PanelBody>
			);
		};

		const offerYesNoButtonContentSettings = () => {
			return (
				<PanelBody
					title={ __( 'Content', 'cartflows-pro' ) }
					initialOpen={ false }
				>
					<h2>{ __( 'Title', 'cartflows-pro' ) }</h2>
					<TypographyControl
						label={ __( 'Typography', 'cartflows-pro' ) }
						attributes={ attributes }
						setAttributes={ setAttributes }
						loadGoogleFonts={ {
							value: titleLoadGoogleFonts,
							label: 'titleLoadGoogleFonts',
						} }
						fontFamily={ {
							value: titleFontFamily,
							label: 'titleFontFamily',
						} }
						fontWeight={ {
							value: titleFontWeight,
							label: 'titleFontWeight',
						} }
						fontSubset={ {
							value: titleFontSubset,
							label: 'titleFontSubset',
						} }
						fontSizeType={ {
							value: titleFontSizeType,
							label: 'titleFontSizeType',
						} }
						fontSize={ {
							value: titleFontSize,
							label: 'titleFontSize',
						} }
						fontSizeMobile={ {
							value: titleFontSizeMobile,
							label: 'titleFontSizeMobile',
						} }
						fontSizeTablet={ {
							value: titleFontSizeTablet,
							label: 'titleFontSizeTablet',
						} }
						lineHeightType={ {
							value: titleLineHeightType,
							label: 'titleLineHeightType',
						} }
						lineHeight={ {
							value: titleLineHeight,
							label: 'titleLineHeight',
						} }
						lineHeightMobile={ {
							value: titleLineHeightMobile,
							label: 'titleLineHeightMobile',
						} }
						lineHeightTablet={ {
							value: titleLineHeightTablet,
							label: 'titleLineHeightTablet',
						} }
					/>

					<hr className="cfp-editor__separator" />
					<h2>{ __( 'Sub Title', 'cartflows-pro' ) }</h2>
					<TypographyControl
						label={ __( 'Typography', 'cartflows-pro' ) }
						attributes={ attributes }
						setAttributes={ setAttributes }
						loadGoogleFonts={ {
							value: subTitleLoadGoogleFonts,
							label: 'subTitleLoadGoogleFonts',
						} }
						fontFamily={ {
							value: subTitleFontFamily,
							label: 'subTitleFontFamily',
						} }
						fontWeight={ {
							value: subTitleFontWeight,
							label: 'subTitleFontWeight',
						} }
						fontSubset={ {
							value: subTitleFontSubset,
							label: 'subTitleFontSubset',
						} }
						fontSizeType={ {
							value: subTitleFontSizeType,
							label: 'subTitleFontSizeType',
						} }
						fontSize={ {
							value: subTitleFontSize,
							label: 'subTitleFontSize',
						} }
						fontSizeMobile={ {
							value: subTitleFontSizeMobile,
							label: 'subTitleFontSizeMobile',
						} }
						fontSizeTablet={ {
							value: subTitleFontSizeTablet,
							label: 'subTitleFontSizeTablet',
						} }
						lineHeightType={ {
							value: subTitleLineHeightType,
							label: 'subTitleLineHeightType',
						} }
						lineHeight={ {
							value: subTitleLineHeight,
							label: 'subTitleLineHeight',
						} }
						lineHeightMobile={ {
							value: subTitleLineHeightMobile,
							label: 'subTitleLineHeightMobile',
						} }
						lineHeightTablet={ {
							value: subTitleLineHeightTablet,
							label: 'subTitleLineHeightTablet',
						} }
					/>

					<hr className="cfp-editor__separator" />
					<RangeControl
						label={ __(
							'Title Bottom Spacing (px)',
							'cartflows-pro'
						) }
						value={ titleBottomSpacing }
						onChange={ ( value ) =>
							setAttributes( { titleBottomSpacing: value } )
						}
						min={ 0 }
						max={ 500 }
						beforeIcon=""
						allowReset
						initialPosition={ 0 }
					/>
				</PanelBody>
			);
		};

		return (
			<Fragment>
				<InspectorControls>
					{ offerYesNoButtonGeneralSettings() }
					{ offerYesNoButtonStyleSettings() }
					{ offerYesNoButtonContentSettings() }
				</InspectorControls>
				<div
					className={ classnames(
						className,
						`cfp-block-${ this.props.clientId.substr( 0, 8 ) }`
					) }
				>
					<div className="wpcfp__offer-yes-no-button">
						<div className="wpcfp__offer-yes-no-button-wrap">
							<a
								href="#"
								className="wpcfp__offer-yes-no-button-link"
							>
								{ iconPosition === 'before_title_sub_title' &&
									icon_html }
								<span className="wpcfp__offer-yes-no-button-content-wrap">
									<div className="wpcfp__offer-yes-no-button-title-wrap">
										{ iconPosition === 'before_title' &&
											icon_html }
										<RichText
											placeholder={ __(
												'Add text…',
												'cartflows-pro'
											) }
											value={ offerYesNoButtonTitle }
											tagName="span"
											onChange={ ( value ) => {
												setAttributes( {
													offerYesNoButtonTitle: value,
												} );
											} }
											className="wpcfp__offer-yes-no-button-title"
										/>
										{ iconPosition === 'after_title' &&
											icon_html }
									</div>
									<RichText
										placeholder={ __(
											'Add text…',
											'cartflows-pro'
										) }
										value={ offerYesNoButtonSubTitle }
										tagName="div"
										onChange={ ( value ) => {
											setAttributes( {
												offerYesNoButtonSubTitle: value,
											} );
										} }
										className="wpcfp__offer-yes-no-button-sub-title"
									/>
								</span>
								{ iconPosition === 'after_title_sub_title' &&
									icon_html }
							</a>
						</div>
					</div>
				</div>
				{ loadTitleGoogleFonts }
				{ loadSubTitleGoogleFonts }
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
} )( OfferYesNoButton );
