const TopupBalanceButton = ({ children, onClick, isSelected, className = '' }) => {
    return (
        <button 
            className={`flex flex-col justify-center items-start p-4 rounded-lg border border-gray-200 bg-transparent hover:bg-gray-50 transition-colors cursor-pointer ${isSelected ? 'border-blue-500 bg-blue-50' : ''} ${className}`}
            type="button"
            onClick={onClick}
        >
            {children}
        </button>
    );
};

export default TopupBalanceButton;