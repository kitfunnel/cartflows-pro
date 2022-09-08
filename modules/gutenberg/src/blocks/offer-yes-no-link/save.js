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
		linkText,
		icon,
		iconPosition,
	} = props.attributes;

	let icon_html = '';
	if ( '' !== icon ) {
		icon_html = (
			<div className="wpcfp__offer-yes-no-link-icon">
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
			<div className="wpcfp__offer-yes-no-link">
				<a
					href={ action_link }
					className="wpcfp__offer-yes-no-link-url"
				>
					<div className="wpcfp__offer-yes-no-link-text-wrap">
						{ iconPosition === 'before_link_text' && icon_html }
						<RichText.Content
							value={ linkText }
							tagName="span"
							className="wpcfp__offer-yes-no-link-text"
						/>
						{ iconPosition === 'after_link_text' && icon_html }
					</div>
				</a>
			</div>
		</div>
	);
}
