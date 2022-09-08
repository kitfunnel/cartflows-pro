/**
 * BLOCK: Offer Product Image Block
 */

// Import block dependencies and components.
import classnames from 'classnames';
import styling from './styling';
import CFP_Block_Icons from '../../../dist/blocks/controls/block-icons';
import BlockEditorPreviewCompatibility from '../../../config/BlockEditorPreviewCompatibility';

import { Flexslider } from './flexslider';

const { __ } = wp.i18n;

const { BlockControls, InspectorControls, ColorPalette } = wp.blockEditor;

const { withSelect } = wp.data;

const { PanelBody, SelectControl, RangeControl } = wp.components;

const { Component, Fragment } = wp.element;

class OfferProductImage extends Component {
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
			'wpcfp-offer-product-image-style-' +
				this.props.clientId.substr( 0, 8 )
		);
		document.head.appendChild( $style );

		Flexslider();
	}

	componentDidUpdate() {
		const element = document.getElementById(
			'wpcfp-offer-product-image-style-' +
				this.props.clientId.substr( 0, 8 )
		);

		if ( null !== element && undefined !== element ) {
			element.innerHTML = styling( this.props );
			Flexslider();
		}

		BlockEditorPreviewCompatibility(
			'wpcfp-offer-product-image-style-' +
				this.props.clientId.substr( 0, 8 ),
			styling( this.props )
		);
	}

	render() {
		// Setup the attributes
		const {
			className,
			setAttributes,
			attributes: {
				formJson,
				isHtml,
				// Alignment
				alignment,
				// Image Bottom Spacing
				image_bottom_spacing,
				//Margin
				topMargin,
				bottomMargin,
				// Image Border
				imageBorderStyle,
				imageBorderWidth,
				imageBorderColor,
				imageBorderRadius,
				// Spacing Between Thumbnails
				spacing_between_thumbnails,
				// Thumbnail Border
				thumbnailBorderStyle,
				thumbnailBorderWidth,
				thumbnailBorderColor,
				thumbnailBorderRadius,
			},
		} = this.props;

		let html = '';

		if ( formJson && formJson.data.html ) {
			html = formJson.data.html;
		}

		const offerProductImageSettings = () => {
			return (
				<PanelBody
					title={ __( 'Offer Product Image', 'cartflows-pro' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Alignment', 'cartflows-pro' ) }
						value={ alignment }
						onChange={ ( value ) =>
							setAttributes( { alignment: value } )
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

					<RangeControl
						label={ 'Image Botton Spacing' }
						value={ image_bottom_spacing }
						onChange={ ( value ) =>
							setAttributes( { image_bottom_spacing: value } )
						}
						min={ 0 }
						max={ 500 }
						allowReset
					/>

					<h2>{ __( 'Image Border', 'cartflows-pro' ) }</h2>
					<SelectControl
						label={ __( 'Border Style', 'cartflows-pro' ) }
						value={ imageBorderStyle }
						onChange={ ( value ) =>
							setAttributes( { imageBorderStyle: value } )
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
					{ 'none' !== imageBorderStyle && (
						<RangeControl
							label={ __( 'Border Width', 'cartflows-pro' ) }
							value={ imageBorderWidth }
							onChange={ ( value ) =>
								setAttributes( { imageBorderWidth: value } )
							}
							min={ 0 }
							max={ 50 }
							allowReset
						/>
					) }
					<RangeControl
						label={ __( 'Border Radius', 'cartflows-pro' ) }
						value={ imageBorderRadius }
						onChange={ ( value ) =>
							setAttributes( { imageBorderRadius: value } )
						}
						min={ 0 }
						max={ 1000 }
						allowReset
					/>
					{ 'none' !== imageBorderStyle && (
						<Fragment>
							<p className="cfp-setting-label">
								{ __( 'Border Color', 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span
										className="component-color-indicator"
										style={ {
											backgroundColor: imageBorderColor,
										} }
									></span>
								</span>
							</p>
							<ColorPalette
								value={ imageBorderColor }
								onChange={ ( colorValue ) =>
									setAttributes( {
										imageBorderColor: colorValue,
									} )
								}
								allowReset
							/>
						</Fragment>
					) }
				</PanelBody>
			);
		};

		const offerProductImageThumbnailSettings = () => {
			return (
				<PanelBody
					title={ __( 'Thumbnails', 'cartflows-pro' ) }
					initialOpen={ false }
				>
					<RangeControl
						label={ 'Spacing Between Thumbnails' }
						value={ spacing_between_thumbnails }
						onChange={ ( value ) =>
							setAttributes( {
								spacing_between_thumbnails: value,
							} )
						}
						min={ 0 }
						max={ 500 }
						allowReset
					/>

					<h2>{ __( 'Thumbnail Border', 'cartflows-pro' ) }</h2>
					<SelectControl
						label={ __( 'Border Style', 'cartflows-pro' ) }
						value={ thumbnailBorderStyle }
						onChange={ ( value ) =>
							setAttributes( { thumbnailBorderStyle: value } )
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
					{ 'none' !== thumbnailBorderStyle && (
						<RangeControl
							label={ __( 'Border Width', 'cartflows-pro' ) }
							value={ thumbnailBorderWidth }
							onChange={ ( value ) =>
								setAttributes( { thumbnailBorderWidth: value } )
							}
							min={ 0 }
							max={ 50 }
							allowReset
						/>
					) }
					<RangeControl
						label={ __( 'Border Radius', 'cartflows-pro' ) }
						value={ thumbnailBorderRadius }
						onChange={ ( value ) =>
							setAttributes( { thumbnailBorderRadius: value } )
						}
						min={ 0 }
						max={ 1000 }
						allowReset
					/>
					{ 'none' !== thumbnailBorderStyle && (
						<Fragment>
							<p className="cfp-setting-label">
								{ __( 'Border Color', 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span
										className="component-color-indicator"
										style={ {
											backgroundColor: thumbnailBorderColor,
										} }
									></span>
								</span>
							</p>
							<ColorPalette
								value={ thumbnailBorderColor }
								onChange={ ( colorValue ) =>
									setAttributes( {
										thumbnailBorderColor: colorValue,
									} )
								}
								allowReset
							/>
						</Fragment>
					) }
				</PanelBody>
			);
		};

		const offerProductImageSpacingSettings = () => {
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
					{ offerProductImageSettings() }
					{ offerProductImageThumbnailSettings() }
					{ offerProductImageSpacingSettings() }
				</InspectorControls>
				<div
					className={ classnames(
						className,
						`cfp-block-${ this.props.clientId.substr( 0, 8 ) }`
					) }
				>
					<div className="wpcfp__offer-product-image">
						{ isHtml && (
							<div dangerouslySetInnerHTML={ { __html: html } } />
						) }
					</div>
				</div>
			</Fragment>
		);
	}
}

// export default OfferProductImage;

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
				action: 'wpcfp_offer_product_image_shortcode',
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
} )( OfferProductImage );
