import { render } from '@wordpress/element';
import App from './components/App';
import Automation from './components/Automation';
import Settings from './components/Settings';

import './css/topsms-admin-app.css';

// Render the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('topsms-admin-app');
  if (container) {
    render(<App />, container);
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('topsms-admin-automations');
  if (container) {
    render(<Automation />, container);
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('topsms-admin-settings');
  if (container) {
    render(<Settings />, container);
  }
});
