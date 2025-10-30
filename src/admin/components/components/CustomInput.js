import { TextControl } from '@wordpress/components';

// Memoize the CustomInput to prevent unnecessary re-renders
const CustomInput = ({ 
    label, 
    description, 
    value, 
    onChange, 
    error, 
    children,
    ...props 
}) => (
    <div className="mb-4">
        <div className="topsms-label">
            {label}
            {description && (
                <span className="text-xs text-gray-500 ml-2">{description}</span>
            )}
        </div>
        <div className="topsms-input">
            <TextControl
                label=""
                value={value}
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

export default CustomInput;