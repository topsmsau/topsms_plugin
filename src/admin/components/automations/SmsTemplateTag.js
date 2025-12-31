const SmsTemplateTag = ({ tag, label, description, onClick }) => {
  return (
    <button
      className='automation-tag-button px-3 py-1 mx-1 my-1 bg-gray-100 hover:bg-gray-200 rounded-full text-sm text-gray-600 transition-colors'
      onClick={() => onClick && onClick(tag)}
      title={description || tag}
    >
      {label || tag}
    </button>
  );
};

export default SmsTemplateTag;
