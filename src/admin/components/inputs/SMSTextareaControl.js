import { TextareaControl } from '@wordpress/components';

const SMSTextareaControl = () => {
	return (
		<TextareaControl
			__nextHasNoMarginBottom
			className="template-input-field"
			label=""
			help=""
			value="Hello [f_name], there was an issue processing your order with ID [id]. Please contact us for assistance."
			// onChange={ ( value ) => setText( value ) }
		/>
	);
};

export default SMSTextareaControl;
