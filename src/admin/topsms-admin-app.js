import { render } from '@wordpress/element';
import App from './components/App';
import './css/topsms-admin-app.css';

// Render the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('topsms-admin-app');
    if (container) {
        render(<App />, container);
    }
});