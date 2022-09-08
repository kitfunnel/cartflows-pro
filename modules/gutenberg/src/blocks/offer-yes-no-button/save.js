/**
 * BLOCK: Test Block - Save Block
 */

import classnames from 'classnames';
import renderSVG from '../../../dist/blocks/controls/render-icon';

const { RichText } = wp.blockEditor;

export default function save( props ) {
	const {
		block_id,
		offerAction,
		offerYesNoButtonTitle,
		offerYesNoButtonSubTitle,
		icon,
		iconPosition,
	} = props.attributes;

	let icon_html = '';
	if ( '' !== icon ) {
		icon_html = (
			<div className="wpcfp__offer-yes-no-button-icon">
				{ renderSVG( icon ) }
			</div>
		);
	}

	const step_type = cfp_blocks_info.step_type;
	let action_link = '';
	if ( 'upsell' === step_type || 'downsell' === step_type ) {
		const accept_offer_link =
			'upsell' === step_type
				? '?class=wcf-up-offer-yes'
				: '?class=wcf-down-offer-yes';
		const reject_offer_link =
			'upsell' === step_type
				? '?class=wcf-up-offer-no'
				: '?class=wcf-down-offer-no';
		action_link =
			'accept_offer' === offerAction
				? accept_offer_link
				: reject_offer_link;
	}

	return (
		<div
			className={ classnames(
				props.className,
				`cfp-block-${ block_id }`
			) }
		>
			<div className="wpcfp__offer-yes-no-button">
				<div className="wpcfp__offer-yes-no-button-wrap">
					<a
						href={ action_link }
						className="wpcfp__offer-yes-no-button-link"
					>
						{ iconPosition === 'before_title_sub_title' &&
							icon_html }
						<span className="wpcfp__offer-yes-no-button-content-wrap">
							<div className="wpcfp__offer-yes-no-button-title-wrap">
								{ iconPosition === 'before_title' && icon_html }
								<RichText.Content
									value={ offerYesNoButtonTitle }
									tagName="span"
									className="wpcfp__offer-yes-no-button-title"
								/>
								{ iconPosition === 'after_title' && icon_html }
							</div>
							{ offerYesNoButtonSubTitle && (
								<RichText.Content
									value={ offerYesNoButtonSubTitle }
									tagName="div"
									className="wpcfp__offer-yes-no-button-sub-title"
								/>
							) }
						</span>
						{ iconPosition === 'after_title_sub_title' &&
							icon_html }
					</a>
				</div>
			</div>
		</div>
	);
}
