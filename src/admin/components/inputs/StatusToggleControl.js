import { ToggleControl } from '@wordpress/components';
// import { useState } from '@wordpress/element';

const StatusToggleControl = ({ label, value, onChange }) => {
	// const [ changeStatus, setChangeStatus ] = useState( false );

	return (
		<ToggleControl
			__nextHasNoMarginBottom
			label={label}
			checked={value}
			onChange={onChange}
		/>
		// <ToggleControl
		// 	__nextHasNoMarginBottom
		// 	label="test"
		// 	checked={ changeStatus }
		// 	onChange={ () => setChangeStatus( ( state ) => ! state ) }
		// />
	);
};

export default StatusToggleControl;
