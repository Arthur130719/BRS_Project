import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

function Login() {
  const [formData, setFormData] = useState({ username_pppoe: '', password_pppoe: '' });
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    try {
      const response = await fetch(`http://${window.location.hostname}:8080/api/pelanggan/login`, {
        method: POST`,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (response.ok) {
        localStorage.setItem('brs_token', data.token);
        localStorage.setItem('brs_user', JSON.stringify(data.user));
        navigate('/dashboard');
      } else {
        setError(data.message || 'Login gagal. Periksa kembali username dan password Anda.');
      }
    } catch (err) {
      setError('Terjadi kesalahan jaringan. Tidak dapat terhubung ke server BRS.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-surface-container-highest dark:bg-[#0f172a] relative overflow-hidden">
      {/* Background Ornaments */}
      <div className="absolute top-0 right-0 -z-10 w-96 h-96 bg-primary-container/20 rounded-full blur-3xl opacity-60 transform translate-x-1/2 -translate-y-1/2"></div>
      <div className="absolute bottom-0 left-0 -z-10 w-96 h-96 bg-secondary/10 rounded-full blur-3xl opacity-60 transform -translate-x-1/2 translate-y-1/2"></div>
      
      <div className="w-full max-w-md bg-surface/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 ambient-shadow">
        <div className="p-8 sm:p-10">
          <div className="text-center mb-8">
            <img 
              alt="BRS Logo" 
              className="h-12 w-auto mx-auto mb-4 drop-shadow-md" 
              src="https://lh3.googleusercontent.com/aida-public/AB6AXuDpyHUd3GTOUf4CRpHHgdobAINBU41fmRe5JBUoMqOVXLdE3fJUNiyFIEK7QW7cGuJZA4x4osK5r6sI5jzBNPoBao6coIs7Zs9qpzSsJm1umeqBwqzLXlQPydw4xyoQvboVvSi6lAVnruXyFiqBF2hGZOEIZ6aqUPdG84DDAzEx6heWsjxLoJQ6RQSbSVk1VrOHZtvqsM0X_TPJ1Giudd9MOG3loF1U850bNxU0saCNRIahYYRGImnPxyr-p5qka1kXL3v9FcYMXlA" 
            />
            <h1 className="font-headline-lg text-headline-md text-secondary">Portal Pelanggan</h1>
            <p className="font-body-md text-on-surface-variant text-sm mt-2">Masuk untuk memantau koneksi dan tagihan Anda.</p>
          </div>

          {error && (
            <div className="bg-error/10 border border-error/30 text-error p-4 rounded-xl mb-6 flex items-start gap-3">
              <span className="material-symbols-outlined text-xl shrink-0">error</span>
              <span className="font-body-md text-sm">{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="flex flex-col gap-5">
            <div className="flex flex-col gap-1.5">
              <label className="font-label-sm text-secondary font-bold">Username PPPoE</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant/50">person</span>
                <input 
                  type="text" 
                  required
                  value={formData.username_pppoe}
                  onChange={(e) => setFormData({...formData, username_pppoe: e.target.value})}
                  className="w-full pl-12 pr-4 py-3 bg-surface-bright border border-outline-variant/50 rounded-xl focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container font-body-md text-on-surface transition-all"
                  placeholder="Masukkan username Anda"
                />
              </div>
            </div>

            <div className="flex flex-col gap-1.5">
              <label className="font-label-sm text-secondary font-bold">Password PPPoE</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant/50">lock</span>
                <input 
                  type="password" 
                  required
                  value={formData.password_pppoe}
                  onChange={(e) => setFormData({...formData, password_pppoe: e.target.value})}
                  className="w-full pl-12 pr-4 py-3 bg-surface-bright border border-outline-variant/50 rounded-xl focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container font-body-md text-on-surface transition-all"
                  placeholder="Masukkan password Anda"
                />
              </div>
            </div>

            <button 
              type="submit" 
              disabled={isLoading}
              className={`mt-4 w-full bg-primary-container text-on-primary font-label-md text-label-lg font-bold py-3.5 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 ${isLoading ? 'opacity-70 cursor-not-allowed scale-95' : 'hover:bg-surface-tint hover:shadow-primary-container/30 hover:-translate-y-0.5'}`}
            >
              {isLoading ? (
                <>
                  <span className="material-symbols-outlined animate-spin">autorenew</span>
                  Memproses...
                </>
              ) : (
                'Masuk ke Dashboard'
              )}
            </button>
          </form>
        </div>
        
        <div className="bg-surface-container-low p-6 border-t border-outline-variant/30 text-center">
          <p className="font-body-md text-on-surface-variant text-sm mb-3">
            Belum punya akun web? Akses hanya diberikan untuk pelanggan aktif.
          </p>
          <div className="inline-flex items-center justify-center gap-2 bg-primary-fixed-dim/10 text-primary-container px-4 py-2 rounded-lg font-label-sm font-bold border border-primary-container/20">
            <span className="material-symbols-outlined text-sm">support_agent</span>
            Hubungi Admin: 087761205991
          </div>
          <div className="mt-6">
             <button onClick={() => navigate('/')} className="text-sm font-label-md text-on-surface-variant hover:text-primary-container transition-colors flex items-center justify-center gap-1 mx-auto">
                <span className="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Beranda
             </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Login;
