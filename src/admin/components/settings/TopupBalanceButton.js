const TopupBalanceButton = ({ children, onClick, isSelected, discount, className = '' }) => {
    return (
        <button 
            className={`relative flex flex-col justify-center items-start p-4 rounded-lg border border-gray-200 bg-transparent hover:bg-gray-50 transition-colors cursor-pointer ${isSelected ? 'border-blue-500 bg-blue-50' : ''} ${className}`}
            type="button"
            onClick={onClick}
        >
            {discount && (
                <div className="absolute top-0 right-0 bg-[#F25F7A] text-white text-sm font-bold px-2 py-1 rounded-full m-[3px]">
                    {discount}
                </div>
            )}
            {children}
        </button>
    );
};

export default TopupBalanceButton;