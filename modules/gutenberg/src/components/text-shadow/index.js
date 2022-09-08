/**
 * Text-Shadow reusable component.
 *
 */
const { __ } = wp.i18n

const {
	ColorPalette
} = wp.blockEditor

const {
    Button,
    RangeControl,
    Dashicon
} = wp.components

// Extend component
const { Component, Fragment } = wp.element

class TextShadowControl extends Component {

	constructor() {
        super( ...arguments )
        this.onAdvancedControlClick  = this.onAdvancedControlClick.bind( this )
        this.onAdvancedControlReset  = this.onAdvancedControlReset.bind( this )
    }
    onAdvancedControlClick() {

		let control = true
		let label = __( "Hide Advanced", 'cartflows-pro' )

		if( this.state !== null && this.state.showAdvancedControls === true ) {
			control = false
			label = __( "Advanced", 'cartflows-pro' )
		}

		this.setState(
			{
				showAdvancedControls: control,
				showAdvancedControlsLabel: label
			}
		)
    }
    onAdvancedControlReset() {

        const { setAttributes } = this.props
        
        setAttributes( { textShadowColor: "" } )
        setAttributes( { textShadowHOffset: "" } )
        setAttributes( { textShadowVOffset: "" } )
        setAttributes( { textShadowBlur: "" } )
	}
    render() {
        const { 
            setAttributes,
            textShadowColor,
            textShadowHOffset,
            textShadowVOffset,
            textShadowBlur,
        } = this.props
        
        var advancedControls;
        var textShadowAdvancedControls;
        var resetTextShadowAdvancedControls;
        if( this.state !== null && true === this.state.showAdvancedControls ) {
            advancedControls = (
                <div className="cfp-box-shadow-advanced">
                    <Fragment>
                    <p className="cfp-setting-label">{ textShadowColor.label }<span className="components-base-control__label"><span className="component-color-indicator" style={{ backgroundColor: textShadowColor.value }} ></span></span></p>
                    <ColorPalette
                        value={ textShadowColor.value }
                        onChange={ ( colorValue ) => setAttributes( { textShadowColor: colorValue } ) }
                        allowReset
                    />
                    </Fragment>
                    <Fragment>
                    <h2>{ textShadowHOffset.label }</h2>
                    <RangeControl
                        value={ textShadowHOffset.value }
                        onChange={ ( value ) => setAttributes( { textShadowHOffset: value } ) }
                        min={ -100 }
                        max={ 100 }
                        allowReset
                    />
                    </Fragment>
                    <Fragment>
                    <h2>{ textShadowVOffset.label }</h2>
                    <RangeControl
                        value={ textShadowVOffset.value }
                        onChange={ ( value ) => setAttributes( { textShadowVOffset: value } ) }
                        min={ -100 }
                        max={ 100 }
                        allowReset
                    />
                    </Fragment>
                    <Fragment>
                    <h2>{ textShadowBlur.label }</h2>
                    <RangeControl
                        value={ textShadowBlur.value }
                        onChange={ ( value ) => setAttributes( { textShadowBlur: value } ) }
                        min={ 0 }
                        max={ 100 }
                        allowReset
                    />
                    </Fragment>
                </div>
            );
        }
        resetTextShadowAdvancedControls =  (
            <Button
                className="cfp-size-btn cfp-typography-reset-btn"
                isSmall
                aria-pressed={ ( this.state !== null ) }
                onClick={ this.onAdvancedControlReset }
            ><Dashicon icon="image-rotate" />
            </Button>
        );
        
        textShadowAdvancedControls = (
            <Button
                className="cfp-size-btn cfp-typography-control-btn"
                isSmall
                aria-pressed={ ( this.state !== null ) }
                onClick={ this.onAdvancedControlClick }
            ><Dashicon icon="admin-tools" />
            </Button>
        );

        return(
            <div className='cfp-typography-option-actions'>
                <span>{ this.props.label }</span>
                { textShadowAdvancedControls }
                { resetTextShadowAdvancedControls }
                { advancedControls }
            </div>
        )
    }
}

export default TextShadowControl
