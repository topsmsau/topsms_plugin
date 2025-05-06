import { useState } from '@wordpress/element';
import Registration from './Registration';
import Verification from './Verification';
import Welcome from './Welcome';

const App = () => {
    const [currentStep, setCurrentStep] = useState(window.topsmsData.currentStep);
    const { setupSteps } = window.topsmsData;
    
    // Handle step completion
    const handleStepComplete = (nextStep) => {
        // You might want to save progress via AJAX here
        setCurrentStep(nextStep);
    };
    
    // Render current step
    const renderStep = () => {
        switch (currentStep) {
            case 'registration':
                return <Registration onComplete={() => handleStepComplete('verification')} />;
            case 'verification':
                return <Verification onComplete={() => handleStepComplete('welcome')} />;
            case 'welcome':
                return <Welcome />;
            default:
                return <p>Unknown step: {currentStep}</p>;
        }
    };
    
    return (
        <div className="topsms-app">
            {renderStep()}
        </div>
    );
};

export default App;