import { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';

export default function DashboardLayout({ children, onPayClick }) {
  const location = useLocation();
  const navigate = useNavigate();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  // Poll session status every 10 seconds
  useEffect(() => {
    const token = sessionStorage.getItem('brs_token');
    if (!token) return;

    const interval = setInterval(() => {
      fetch(`http://${window.location.hostname}:8000/api/pelanggan/ping`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }).catch(err => console.error('Ping failed:', err));
    }, 10000);

    return () => clearInterval(interval);
  }, []);

  const handleLogout = () => {
    const token = sessionStorage.getItem('brs_token');
    if (token) {
      fetch(`http://${window.location.hostname}:8000/api/pelanggan/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
    }
    sessionStorage.removeItem('brs_token');
    sessionStorage.removeItem('brs_user');
    navigate('/login');
  };

  const navItems = [
    { name: 'Dashboard', path: '/dashboard', icon: 'dashboard' },
    { name: 'Billing', path: '/dashboard/billing', icon: 'receipt_long' },
    { name: 'Support', path: '/dashboard/support', icon: 'support_agent' },
    { name: 'Profile', path: '/dashboard/profile', icon: 'person' }
  ];

  return (
    <div className="font-body-md text-on-surface antialiased relative min-h-screen">
      {/* Ambient Background */}
      <div className="bg-graphic">
        <div className="bg-blob-1"></div>
        <div className="bg-blob-2"></div>
      </div>

      {/* TopNavBar */}
      <nav className="bg-surface/90 backdrop-blur-md dark:bg-surface-dim/90 docked full-width top-0 sticky z-50 border-b border-outline-variant/30 dark:border-outline/20 shadow-sm transition-all duration-300">
        <div className="flex justify-between items-center w-full px-6 lg:px-margin-desktop max-w-max-width mx-auto py-4">
          <Link to="/" className="flex items-center gap-2 cursor-pointer hover:opacity-90 transition-opacity">
            <span className="material-symbols-outlined text-primary-container text-3xl" style={{ fontVariationSettings: "'FILL' 1" }}>router</span>
            <span className="font-headline-md text-headline-md font-bold text-secondary dark:text-secondary-fixed">BRS</span>
          </Link>
          
          <div className="hidden md:flex gap-8 items-center h-full">
            {navItems.map((item) => (
              <Link
                key={item.path}
                to={item.path}
                className={`font-label-md text-label-md flex items-center gap-xs pb-1 transition-colors duration-200 ${
                  location.pathname === item.path 
                    ? 'text-primary font-bold border-b-2 border-primary' 
                    : 'text-on-surface-variant hover:text-primary'
                }`}
              >
                <span className="material-symbols-outlined text-sm">{item.icon}</span> {item.name}
              </Link>
            ))}
          </div>

          <div className="flex items-center gap-md">
            <button onClick={onPayClick} className="hidden md:flex items-center justify-center gap-2 px-6 py-2 bg-primary-container text-on-primary font-label-md text-label-md font-semibold rounded-full hover:bg-surface-tint transition-colors shadow-sm hover:opacity-90">
              <span className="material-symbols-outlined text-sm">payments</span> Pay Bill
            </button>
            <button onClick={handleLogout} className="hidden md:flex items-center justify-center gap-2 px-4 py-2 border border-error/50 text-error font-label-md text-label-md font-semibold rounded-full hover:bg-error/10 transition-colors shadow-sm hover:opacity-90">
              <span className="material-symbols-outlined text-sm">logout</span> Keluar
            </button>
            <button 
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="md:hidden text-on-surface-variant hover:text-primary p-2"
            >
              <span className="material-symbols-outlined">{isMobileMenuOpen ? 'close' : 'menu'}</span>
            </button>
          </div>
        </div>
      </nav>

      {/* Mobile Menu Dropdown */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-surface/95 backdrop-blur-md border-b border-outline-variant/30 fixed top-[72px] left-0 w-full z-40 shadow-md">
          <div className="flex flex-col px-6 py-4 gap-2">
            {navItems.map((item) => (
              <Link
                key={item.path}
                to={item.path}
                onClick={() => setIsMobileMenuOpen(false)}
                className={`font-label-md text-label-md flex items-center gap-2 py-3 px-4 rounded-xl transition-colors ${
                  location.pathname === item.path 
                    ? 'bg-primary-container text-primary font-bold' 
                    : 'text-on-surface-variant hover:bg-surface-variant/50 hover:text-primary'
                }`}
              >
                <span className="material-symbols-outlined text-md">{item.icon}</span> {item.name}
              </Link>
            ))}
            <div className="border-t border-outline-variant/20 my-2 pt-4 flex flex-col gap-3 px-2">
              <button onClick={() => { setIsMobileMenuOpen(false); onPayClick(); }} className="flex items-center justify-center gap-2 px-6 py-3 bg-primary-container text-on-primary font-label-md font-bold rounded-full">
                <span className="material-symbols-outlined text-sm">payments</span> Pay Bill
              </button>
              <button onClick={() => { setIsMobileMenuOpen(false); handleLogout(); }} className="flex items-center justify-center gap-2 px-4 py-3 border border-error/50 text-error font-label-md font-bold rounded-full">
                <span className="material-symbols-outlined text-sm">logout</span> Keluar
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Main Content */}
      <main className="max-w-max-width mx-auto px-4 md:px-margin-desktop py-lg relative z-10">
        {children}
      </main>
    </div>
  );
}
