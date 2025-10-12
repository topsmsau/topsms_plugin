import { Tooltip } from '@wordpress/components';

const SmsTag = ({ tag, onClick, message }) => {
    return (
        <Tooltip text={message} delay={300}>
            <button 
                className="automation-tag-button px-1 py-1 mx-1 my-1 text-sm text-blue-500"
                onClick={() => onClick && onClick(tag)}
            >
                {tag}
            </button>
        </Tooltip>
    );
};

export default SmsTag;