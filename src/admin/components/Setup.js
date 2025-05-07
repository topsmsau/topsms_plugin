import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

import Registration from './Registration';
import Verification from './Verification';
import Welcome from './Welcome'; 
import Connected from './Connected';

const Setup = () => {
    // Access the connection status from the global topsmsData object
    const [isConnected, setIsConnected] = useState(
        window.topsmsData && window.topsmsData.isConnected ? 
        window.topsmsData.isConnected : 
        false
    );
    console.log("isConnected", isConnected);

    // If connected, show the Connected component
    if (isConnected === 'true' || isConnected == 1) {
        return <Connected />;
    }

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
                    userData={userData} 
                />
            )}
            
            {currentStep === 'welcome' && (
                <Welcome onComplete={handleStepComplete} userData={userData} />
            )}
        </div>
    );
};

export default Setup;