import { SelectControl } from '@wordpress/components';

// Memoize the CustomInput to prevent unnecessary re-renders
const CustomSelect = ({ 
    label, 
    value, 
    options, 
    onChange, 
    error, 
    children,
    ...props 
}) => (
    <div className="mb-4">
        <div className="topsms-label">{label}</div>
        <div className="topsms-input">
            <SelectControl
                label=""
                value={value}
                options={options}
                onChange={onChange}
                {...props}
            />
        </div>
        {children} 
        {error && (
            <div className="text-red-500 text-sm mt-1">{error}</div>
        )}
    </div>
);

export default CustomSelect;