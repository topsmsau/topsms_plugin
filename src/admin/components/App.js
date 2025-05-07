import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

import Registration from './Registration';
import Verification from './Verification';
import Welcome from './Welcome'; 

const App = () => {
    const [currentStep, setCurrentStep] = useState('registration');
    const [userData, setUserData] = useState({});

    const handleStepComplete = (nextStep, data = {}) => {
        // Update userData with any new data passed from the step
        setUserData(prevData => ({ ...prevData, ...data }));
        setCurrentStep(nextStep);
    };

    return (
        <div className="topsms-app">
            {currentStep === 'registration' && (
                <Registration onComplete={handleStepComplete} />
            )}
            
            {currentStep === 'verification' && (
                <Verification 
                    onComplete={handleStepComplete} 
                    phoneNumber={userData.phoneNumber} 
                />
            )}
            
            {currentStep === 'welcome' && (
                <Welcome onComplete={handleStepComplete} userData={userData} />
            )}
        </div>
    );
};

export default App;