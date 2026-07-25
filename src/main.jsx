import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'
import './index.css'

const originalFetch = window.fetch;
window.fetch = async function () {
  const response = await originalFetch.apply(this, arguments);
  if (response.status === 401) {
    sessionStorage.removeItem('brs_token');
    sessionStorage.removeItem('brs_user');
    if (window.location.pathname !== '/login') {
      window.location.href = '/login?expired=1';
    }
  }
  return response;
};

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)
