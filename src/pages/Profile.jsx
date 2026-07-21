import { useState, useEffect } from 'react';

export default function Profile() {
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchProfile = () => {
    const token = localStorage.getItem('brs_token');
    
    fetch(`http://${window.location.hostname}:8080/api/pelanggan/dashboard`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
      .then(res => res.json())
      .then(data => {
        setProfile(data.pelanggan);
        setLoading(false);
      })
      .catch(err => {
        console.error('Error fetching profile:', err);
        setLoading(false);
      });
  };

  useEffect(() => {
    fetchProfile();
  }, []);

  const [isUpdatingLocation, setIsUpdatingLocation] = useState(false);

  const handleUpdateLocation = () => {
    if (!navigator.geolocation) {
      alert('Browser Anda tidak mendukung fitur lokasi.');
      return;
    }
    
    setIsUpdatingLocation(true);
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const token = localStorage.getItem('brs_token');
        fetch(`http://${window.location.hostname}:8080/api/pelanggan/location`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('Lokasi berhasil diperbarui!');
            fetchProfile(); // Refresh data
          } else {
            alert('Gagal memperbarui lokasi.');
          }
          setIsUpdatingLocation(false);
        })
        .catch(err => {
          console.error(err);
          alert('Terjadi kesalahan.');
          setIsUpdatingLocation(false);
        });
      },
      (error) => {
        console.error('Error getting location:', error);
        alert('Gagal mengambil lokasi. Pastikan Anda telah memberikan izin akses lokasi.');
        setIsUpdatingLocation(false);
      },
      { enableHighAccuracy: true }
    );
  };

  if (loading) {
    return <div className="h-96 flex items-center justify-center">
        <p className="font-headline-md text-secondary animate-pulse">Memuat Profil...</p>
    </div>;
  }

  return (
    <div className="flex flex-col gap-6 max-w-4xl mx-auto">
      <header className="mb-4">
        <h1 className="font-headline-lg text-secondary mb-2 flex items-center gap-2">
          <span className="material-symbols-outlined text-3xl">person</span>
          Profile
        </h1>
        <p className="font-body-md text-on-surface-variant">Manage your account information and preferences.</p>
      </header>

      <div className="glass-panel rounded-2xl overflow-hidden border border-outline-variant/30">
        <div className="bg-gradient-to-r from-surface-container-high to-surface-container-low h-32 relative">
          <div className="absolute -bottom-12 left-8 w-24 h-24 bg-surface rounded-full p-1 flex items-center justify-center shadow-md">
            <div className="w-full h-full bg-primary-container text-on-primary rounded-full flex items-center justify-center text-4xl font-bold font-headline-lg uppercase">
              {profile?.nama?.charAt(0) || 'U'}
            </div>
          </div>
        </div>
        
        <div className="pt-16 pb-8 px-8 border-b border-outline-variant/30">
          <h2 className="font-headline-lg text-secondary mb-1">{profile?.nama}</h2>
          <div className="flex items-center gap-2 mb-4">
            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full font-label-sm font-bold border ${
              profile?.status === 'aktif' || profile?.status === 'Aktif'
                ? 'bg-[#ecfdf5] text-[#065f46] border-[#a7f3d0]' 
                : 'bg-[#fef2f2] text-[#991b1b] border-[#fecaca]'
            }`}>
              Status: {profile?.status}
            </span>
          </div>
        </div>

        <div className="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
          <div className="flex flex-col gap-4">
            <h3 className="font-label-md font-bold text-secondary uppercase tracking-wider mb-2 border-b border-outline-variant/30 pb-2">Informasi Akun</h3>
            
            <div className="flex flex-col gap-1">
              <span className="font-label-sm text-on-surface-variant">Username PPPoE</span>
              <div className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface flex justify-between items-center">
                <span>{profile?.username_pppoe}</span>
                <span className="material-symbols-outlined text-tertiary text-sm cursor-help" title="Digunakan untuk koneksi router dan login portal">info</span>
              </div>
            </div>

            <div className="flex flex-col gap-1">
              <span className="font-label-sm text-on-surface-variant">Nomor Telepon</span>
              <div className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface">
                {profile?.phone || '-'}
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-4">
            <h3 className="font-label-md font-bold text-secondary uppercase tracking-wider mb-2 border-b border-outline-variant/30 pb-2">Informasi Teknis</h3>
            
            <div className="flex flex-col gap-1">
              <span className="font-label-sm text-on-surface-variant">Alamat Pemasangan</span>
              <div className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface">
                {profile?.alamat || '-'}
              </div>
            </div>

            <div className="flex flex-col gap-1">
              <span className="font-label-sm text-on-surface-variant flex justify-between items-center">
                Titik Koordinat (Geolocation)
                <button 
                  onClick={handleUpdateLocation}
                  disabled={isUpdatingLocation}
                  className="text-xs bg-primary-container text-on-primary px-2 py-1 rounded hover:opacity-90 transition-opacity disabled:opacity-50"
                >
                  {isUpdatingLocation ? 'Memperbarui...' : 'Perbarui Titik Lokasi'}
                </button>
              </span>
              <div className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface flex items-center justify-between">
                <span>
                  {profile?.latitude && profile?.longitude 
                    ? `${profile.latitude}, ${profile.longitude}` 
                    : 'Belum diatur'}
                </span>
                {profile?.latitude && profile?.longitude && (
                  <span className="material-symbols-outlined text-[#10b981] text-sm">check_circle</span>
                )}
              </div>
            </div>

            <div className="flex flex-col gap-1">
              <span className="font-label-sm text-on-surface-variant">IP Address Router</span>
              <div className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-[#10b981]"></span>
                {profile?.ip_address || 'Dynamic / DHCP'}
              </div>
            </div>
          </div>
        </div>

        <div className="p-8 bg-surface-container-lowest border-t border-outline-variant/30">
          <p className="font-body-sm text-on-surface-variant flex items-center gap-2">
            <span className="material-symbols-outlined text-sm">lock</span>
            Untuk mengubah informasi sensitif (seperti password atau nomor HP), silakan hubungi tim Support kami.
          </p>
        </div>
      </div>
    </div>
  );
}
