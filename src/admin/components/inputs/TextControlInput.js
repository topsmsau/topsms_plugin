import { TextControl } from '@wordpress/components';

const TextControlInput = ({ label, type, name, placeholder, value, onChange }) => {
	return (
		<TextControl
			// label={label}
			__nextHasNoMarginBottom
			className="card-form-input"
			type={type}
			placeholder={placeholder}
			value={value}
			onChange={(newValue) => onChange(name, newValue)}
		/>
	);
};

export default TextControlInput;