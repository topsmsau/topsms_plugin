import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

import Registration from './setup/Registration';
import Verification from './setup/Verification';
import Welcome from './setup/Welcome'; 
import Connected from './setup/Connected';
import PermalinkMessage from './setup/PermalinkMessage';

const Setup = () => {
    // Access the connection status from the global topsmsNonce object
    const [isConnected, setIsConnected] = useState(
        window.topsmsNonce && window.topsmsNonce.isConnected ? 
        window.topsmsNonce.isConnected : 
        false
    );
    // console.log("isConnected", isConnected);

    // If connected, show the Connected component
    if (isConnected === 'true' || isConnected == 1) {
        return <Connected />;
    }

    // Access the permalink structure from the global topsmsNonce object
    // If permalink is empty, then no registration allowed
    const [emptyPermalink, setEmptyPermalink] = useState(
        window.topsmsNonce && window.topsmsNonce.emptyPermalink ? 
        window.topsmsNonce.emptyPermalink : 
        false
    );
    // console.log("emptyPermalink", emptyPermalink);

    // If empty permalink, show the PermalinkMessage component to prompt users to setup their permalinks
    if (emptyPermalink === 'true' || emptyPermalink == 1) {
        return <PermalinkMessage />;
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
                <Welcome />
            )}
        </div>
    );
};

export default Setup;