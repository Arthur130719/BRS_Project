import { useState, useEffect } from 'react';

export default function Profile() {
  const [profile, setProfile] = useState(null);
  const [paket, setPaket] = useState(null);
  const [loading, setLoading] = useState(true);
  const [liveSession, setLiveSession] = useState({
    status: 'loading',
    ip_address: '-'
  });

  const [isUpdating, setIsUpdating] = useState(false);
  const [isEditingPhone, setIsEditingPhone] = useState(false);
  const [editPhone1, setEditPhone1] = useState('');
  const [editPhone2, setEditPhone2] = useState('');

  const fetchProfile = () => {
    const token = sessionStorage.getItem('brs_token');
    
    fetch(`http://${window.location.hostname}:8000/api/pelanggan/dashboard`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
      .then(res => res.json())
      .then(data => {
        setProfile(data.pelanggan);
        setPaket(data.paket);
        setEditPhone1(data.pelanggan.phone || '');
        setEditPhone2(data.pelanggan.phone_2 || '');
        setLoading(false);
      })
      .catch(err => {
        console.error('Error fetching profile:', err);
      });
  };

  useEffect(() => {
    const token = sessionStorage.getItem('brs_token');
    if (!token) return;

    const fetchLiveSession = () => {
      fetch(`http://${window.location.hostname}:8000/api/pelanggan/live-session`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        setLiveSession({
          status: data.status,
          ip_address: data.ip_address
        });
      })
      .catch(err => console.error('Error fetching live session:', err));
    };

    fetchLiveSession();
    const interval = setInterval(fetchLiveSession, 1000);
    return () => clearInterval(interval);
  }, []);

  const handleSavePhone = () => {
    setIsUpdating(true);
    const token = sessionStorage.getItem('brs_token');
    const formData = new FormData();
    formData.append('phone', editPhone1);
    formData.append('phone_2', editPhone2);

    fetch(`http://${window.location.hostname}:8000/api/pelanggan/profile/update`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        setIsEditingPhone(false);
        fetchProfile();
      } else {
        alert('Gagal menyimpan nomor telepon.');
      }
      setIsUpdating(false);
    })
    .catch(err => {
      console.error(err);
      alert('Terjadi kesalahan saat menyimpan nomor telepon.');
      setIsUpdating(false);
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
        const token = sessionStorage.getItem('brs_token');
        fetch(`http://${window.location.hostname}:8000/api/pelanggan/location`, {
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

      <div className="glass-panel rounded-2xl overflow-hidden border border-outline-variant/30 shadow-sm relative">
        <div 
          className="h-40 relative"
          style={{ backgroundImage: `url(https://images.unsplash.com/photo-1550751827-4bd374c3f58b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80)`, backgroundSize: 'cover', backgroundPosition: 'center' }}
        >
          <div className="absolute inset-0 bg-black/20"></div>
          <div className="absolute -bottom-12 left-8 w-24 h-24 bg-surface rounded-full p-1 flex items-center justify-center shadow-lg border border-outline-variant/20 z-10">
            <div className="w-full h-full bg-surface-container-highest text-secondary rounded-full flex items-center justify-center text-4xl font-bold font-headline-lg uppercase">
              {profile?.nama?.charAt(0) || 'U'}
            </div>
          </div>
        </div>
        
        <div className="pt-16 pb-8 px-8 border-b border-outline-variant/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h2 className="font-headline-lg text-secondary mb-1">{profile?.nama}</h2>
            <div className="flex items-center gap-2">
              <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full font-label-sm font-bold border ${
                profile?.status?.toLowerCase() === 'aktif' || profile?.status?.toLowerCase() === 'active'
                  ? 'bg-[#ecfdf5] text-[#065f46] border-[#a7f3d0]' 
                  : 'bg-[#fef2f2] text-[#991b1b] border-[#fecaca]'
              }`}>
                {(profile?.status?.toLowerCase() === 'aktif' || profile?.status?.toLowerCase() === 'active') && <span className="w-1.5 h-1.5 rounded-full bg-[#10b981] mr-1.5"></span>}
                Status: {(profile?.status?.toLowerCase() === 'aktif' || profile?.status?.toLowerCase() === 'active') ? 'Aktif (Tidak Terisolir)' : (profile?.status?.toLowerCase() === 'suspend' ? 'Terisolir' : profile?.status)}
              </span>
            </div>
          </div>
          
          {/* Package Quick View */}
          {paket && (
            <div className="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-4 flex items-center gap-4 shadow-sm w-full md:w-auto">
              <div className="w-12 h-12 rounded-full bg-primary-fixed-dim/20 flex items-center justify-center text-primary-container">
                <span className="material-symbols-outlined text-2xl" style={{ fontVariationSettings: "'FILL' 1" }}>wifi</span>
              </div>
              <div>
                <p className="font-label-sm text-on-surface-variant uppercase tracking-wider mb-0.5">Paket Internet</p>
                <p className="font-headline-sm text-secondary font-bold">{paket.nama_paket}</p>
              </div>
            </div>
          )}
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

            <div className="flex flex-col gap-1 relative">
              {isUpdating && isEditingPhone && (
                <div className="absolute inset-0 bg-surface/50 z-10 flex items-center justify-center">
                  <span className="material-symbols-outlined animate-spin text-2xl text-primary">progress_activity</span>
                </div>
              )}
              <div className="flex justify-between items-center">
                <span className="font-label-sm text-on-surface-variant">Nomor Telepon</span>
                {!isEditingPhone && (
                  <button onClick={() => setIsEditingPhone(true)} className="text-primary hover:underline font-label-sm flex items-center gap-1">
                    <span className="material-symbols-outlined text-sm">edit</span> Edit
                  </button>
                )}
              </div>
              
              {isEditingPhone ? (
                <div className="flex flex-col gap-2 bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3">
                  <div>
                    <label className="font-label-sm text-on-surface-variant block mb-1">Nomor Utama</label>
                    <input 
                      type="text" 
                      value={editPhone1} 
                      onChange={e => setEditPhone1(e.target.value)}
                      className="w-full bg-surface border border-outline-variant rounded-md px-3 py-1.5 font-body-sm focus:outline-primary"
                      placeholder="Misal: 08123456789"
                    />
                  </div>
                  <div>
                    <label className="font-label-sm text-on-surface-variant block mb-1">Nomor Alternatif (Opsional)</label>
                    <input 
                      type="text" 
                      value={editPhone2} 
                      onChange={e => setEditPhone2(e.target.value)}
                      className="w-full bg-surface border border-outline-variant rounded-md px-3 py-1.5 font-body-sm focus:outline-primary"
                      placeholder="Misal: 08987654321"
                    />
                  </div>
                  <div className="flex justify-end gap-2 mt-2">
                    <button onClick={() => setIsEditingPhone(false)} className="px-3 py-1.5 font-label-sm text-secondary hover:bg-surface-container rounded-md transition-colors">Batal</button>
                    <button onClick={handleSavePhone} className="px-3 py-1.5 font-label-sm bg-primary text-on-primary rounded-md hover:bg-primary/90 transition-colors">Simpan</button>
                  </div>
                </div>
              ) : (
                <div className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface flex flex-col gap-1">
                  <div className="flex items-center gap-2">
                    <span className="material-symbols-outlined text-on-surface-variant text-sm">phone_iphone</span>
                    {profile?.phone || '-'}
                  </div>
                  {profile?.phone_2 && (
                    <div className="flex items-center gap-2 border-t border-outline-variant/30 pt-1 mt-1">
                      <span className="material-symbols-outlined text-on-surface-variant text-sm">phone_iphone</span>
                      {profile.phone_2}
                    </div>
                  )}
                </div>
              )}
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
              <div className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface flex flex-col gap-2">
                <div className="flex items-center gap-2">
                  <span className={`w-2 h-2 rounded-full ${liveSession.status === 'online' ? 'bg-[#10b981]' : 'bg-outline-variant'}`}></span>
                  {liveSession.status === 'online' && liveSession.ip_address !== '-' 
                    ? liveSession.ip_address 
                    : (profile?.ip_address || 'Dynamic / DHCP')}
                </div>
                
                {((liveSession.status === 'online' && liveSession.ip_address?.startsWith('172.')) || 
                  (liveSession.status !== 'online' && profile?.ip_address?.startsWith('172.'))) && (
                  <div className="mt-2 p-2 bg-[#fef2f2] border border-[#fecaca] rounded-md flex items-start gap-2">
                    <span className="material-symbols-outlined text-[#991b1b] text-sm mt-0.5" style={{ fontVariationSettings: "'FILL' 1" }}>warning</span>
                    <p className="font-body-sm text-[#991b1b] text-xs">
                      IP Address ini menunjukkan bahwa koneksi Anda sedang <strong>Terisolir</strong> (suspend). Silakan lakukan pembayaran tagihan atau hubungi tim Support.
                    </p>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        <div className="p-8 bg-surface-container-lowest border-t border-outline-variant/30">
          <p className="font-body-sm text-on-surface-variant flex items-center gap-2">
            <span className="material-symbols-outlined text-sm">lock</span>
            Untuk mengubah informasi sensitif seperti password PPPoE, silakan hubungi tim Support kami.
          </p>
        </div>
      </div>
    </div>
  );
}
