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
  const [showAvatarModal, setShowAvatarModal] = useState(false);
  const [currentSlide, setCurrentSlide] = useState(0);
  const [touchStart, setTouchStart] = useState(0);
  const [touchEnd, setTouchEnd] = useState(0);
  const [isHovered, setIsHovered] = useState(false);

  const promoSlides = [
    {
      image: '/promo1.jpg',
      title: 'Upgrade Kecepatan Tanpa Batas',
      subtitle: 'Rasakan streaming 4K & gaming lebih lancar tanpa lag dengan kecepatan ekstra.',
    },
    {
      image: '/promo2.jpg',
      title: 'Hiburan Keluarga Nomor Satu',
      subtitle: 'Tetap setia bersama BRS! Kualitas koneksi terbaik yang bikin keluarga betah di rumah.',
    },
    {
      image: '/promo3.jpg',
      title: 'Layanan Eksklusif Prioritas',
      subtitle: 'Nikmati koneksi super stabil 24/7 dan pelayanan ekstra VIP khusus pelanggan setia.',
    }
  ];

  const handleNextSlide = () => setCurrentSlide((prev) => (prev + 1) % promoSlides.length);
  const handlePrevSlide = () => setCurrentSlide((prev) => (prev - 1 + promoSlides.length) % promoSlides.length);

  const handleTouchStart = (e) => setTouchStart(e.targetTouches[0].clientX);
  const handleTouchMove = (e) => setTouchEnd(e.targetTouches[0].clientX);
  const handleTouchEnd = () => {
    if (!touchStart || !touchEnd) return;
    const distance = touchStart - touchEnd;
    if (distance > 50) handleNextSlide();
    if (distance < -50) handlePrevSlide();
    setTouchStart(0);
    setTouchEnd(0);
  };

  useEffect(() => {
    if (isHovered) return;
    const timer = setInterval(() => {
      handleNextSlide();
    }, 5000);
    return () => clearInterval(timer);
  }, [isHovered, promoSlides.length]);

  const presetAvatars = [
    'https://api.dicebear.com/9.x/avataaars/svg?seed=Felix&backgroundColor=b6e3f4',
    'https://api.dicebear.com/9.x/avataaars/svg?seed=Aneka&backgroundColor=ffdfbf',
    'https://api.dicebear.com/9.x/avataaars/svg?seed=Jude&backgroundColor=c0aede',
    'https://api.dicebear.com/9.x/avataaars/svg?seed=Avery&backgroundColor=d1d4f9',
    'https://api.dicebear.com/9.x/avataaars/svg?seed=Ryker&backgroundColor=ffdfbf',
    'https://api.dicebear.com/9.x/avataaars/svg?seed=Destiny&backgroundColor=b6e3f4',
  ];

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

  const handleUpdateAvatar = (avatarUrl) => {
    setShowAvatarModal(false);
    setIsUpdating(true);
    const token = sessionStorage.getItem('brs_token');
    
    // We send it via POST form data or JSON. Since the original route expects form-data for files, 
    // but we changed it to accept string. Form data with string is fine.
    const formData = new FormData();
    formData.append('avatar', avatarUrl);
    
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
        fetchProfile();
      } else {
        alert('Gagal mengubah foto profil.');
      }
      setIsUpdating(false);
    })
    .catch(err => {
      console.error(err);
      alert('Terjadi kesalahan saat mengubah foto profil.');
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

      <div className="glass-panel rounded-2xl overflow-hidden border border-outline-variant/30 shadow-sm relative group">
        <style>{`
          .slide-transition {
            transition: opacity 1s ease-in-out;
          }
          .slide-zoom {
            animation: slowZoom 8s ease-in-out infinite alternate;
          }
          @keyframes slowZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.15); }
          }
          .avatar-hover {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
          }
          .avatar-hover:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.6);
            border-color: #f59e0b;
          }
        `}</style>
        
        {/* Banner Container dengan Slider */}
        <div 
          className="h-44 md:h-48 relative overflow-hidden bg-[#0f172a]"
          onMouseEnter={() => setIsHovered(true)}
          onMouseLeave={() => setIsHovered(false)}
          onTouchStart={handleTouchStart}
          onTouchMove={handleTouchMove}
          onTouchEnd={handleTouchEnd}
        >
          {promoSlides.map((slide, idx) => (
            <div 
              key={idx}
              className={`absolute inset-0 slide-transition ${idx === currentSlide ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'}`}
            >
              {/* Gambar Latar */}
              <div 
                className={`absolute inset-0 bg-cover bg-center slide-zoom ${idx === currentSlide ? 'animate-play' : 'animate-pause'}`} 
                style={{ backgroundImage: `url(${slide.image})` }}
              ></div>
              
              {/* Overlay Hitam/Gradien untuk Teks */}
              <div className="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent"></div>
              <div className="absolute inset-0 bg-gradient-to-t from-surface to-transparent opacity-90"></div>

              {/* Konten Teks Promosi */}
              <div className="absolute inset-0 p-6 md:p-8 pl-36 md:pl-44 flex flex-col justify-center items-start w-full md:w-5/6 pointer-events-none">
                <h3 className="text-white font-headline-sm font-bold leading-tight mb-1 drop-shadow-md">
                  {slide.title}
                </h3>
                <p className="text-white/80 font-body-sm text-xs md:text-sm drop-shadow line-clamp-2 max-w-[90%]">
                  {slide.subtitle}
                </p>
              </div>
            </div>
          ))}

          {/* Tombol Navigasi Kiri (Hanya muncul saat hover di PC) */}
          <button 
            onClick={handlePrevSlide}
            className="absolute left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm transition-all opacity-0 md:group-hover:opacity-100"
          >
            <span className="material-symbols-outlined text-lg">chevron_left</span>
          </button>
          
          {/* Tombol Navigasi Kanan (Hanya muncul saat hover di PC) */}
          <button 
            onClick={handleNextSlide}
            className="absolute right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm transition-all opacity-0 md:group-hover:opacity-100"
          >
            <span className="material-symbols-outlined text-lg">chevron_right</span>
          </button>

          {/* Indikator Titik (Dots) */}
          <div className="absolute bottom-4 right-4 md:top-4 md:bottom-auto z-20 flex gap-1.5 pointer-events-auto">
            {promoSlides.map((_, idx) => (
              <button 
                key={idx} 
                onClick={() => setCurrentSlide(idx)}
                className={`w-2 h-2 rounded-full transition-all duration-300 ${idx === currentSlide ? 'bg-primary w-5' : 'bg-white/30 hover:bg-white/50'}`}
              ></button>
            ))}
          </div>
        </div>

        {/* Wadah Avatar Ditempatkan di Luar overflow-hidden Banner (Absolute ke glass-panel) */}
        <div className="absolute top-32 left-8 z-20 group-hover:-translate-y-2 transition-transform duration-500">
          <div className="relative w-24 h-24">
            {/* Avatar Utama */}
            <div 
              onClick={() => setShowAvatarModal(true)}
              className="absolute inset-0 bg-surface rounded-full p-1 flex items-center justify-center shadow-lg border-2 border-outline-variant/30 avatar-hover cursor-pointer z-10 overflow-hidden"
              title="Klik untuk mengubah foto profil"
            >
              <div className="w-full h-full bg-gradient-to-br from-surface-container-highest to-surface-container-lowest text-primary rounded-full flex items-center justify-center text-4xl font-bold font-headline-lg uppercase shadow-inner overflow-hidden">
                {profile?.avatar ? (
                  <img src={profile.avatar} alt="Avatar" className="w-full h-full object-cover rounded-full" />
                ) : (
                  profile?.nama?.charAt(0) || 'U'
                )}
              </div>
            </div>
            {/* Indikator Edit (Opsional, kecil di pojok) */}
            <div className="absolute bottom-0 right-0 bg-primary text-on-primary w-6 h-6 rounded-full flex items-center justify-center shadow-md z-20 pointer-events-none border-2 border-surface">
              <span className="material-symbols-outlined" style={{ fontSize: '12px' }}>edit</span>
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

        <div className="p-8 bg-surface-container-lowest border-t border-outline-variant/30 flex flex-col gap-3">
          <p className="font-body-sm text-on-surface-variant flex items-start gap-2">
            <span className="material-symbols-outlined text-sm mt-0.5">lock</span>
            Untuk mengubah informasi sensitif seperti password PPPoE, silakan hubungi tim Support kami.
          </p>
          <p className="font-body-sm text-on-surface-variant flex items-start gap-2">
            <span className="material-symbols-outlined text-sm mt-0.5">my_location</span>
            Pastikan letak rumah Anda sudah sesuai dengan klik "Perbarui Titik Lokasi" pada kolom Titik Koordinat.
          </p>
          <p className="font-body-sm text-on-surface-variant flex items-start gap-2">
            <span className="material-symbols-outlined text-sm mt-0.5">home_pin</span>
            Jika ingin menambah detail isi atau pindah rumah pada kolom Alamat Pemasangan, silakan hubungi Admin / Support.
          </p>
        </div>
      </div>

      {/* Modal Pilih Avatar */}
      {showAvatarModal && (
        <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
          <div className="bg-surface w-full max-w-md rounded-2xl shadow-xl border border-outline-variant/30 overflow-hidden animate-in fade-in zoom-in duration-200">
            <div className="px-6 py-4 border-b border-outline-variant/30 flex justify-between items-center">
              <h3 className="font-headline-sm font-bold text-secondary">Pilih Foto Profil</h3>
              <button onClick={() => setShowAvatarModal(false)} className="text-on-surface-variant hover:text-error transition-colors">
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>
            <div className="p-6 grid grid-cols-3 gap-4">
              {presetAvatars.map((url, idx) => (
                <div 
                  key={idx} 
                  onClick={() => handleUpdateAvatar(url)}
                  className="cursor-pointer group relative rounded-full overflow-hidden border-2 border-transparent hover:border-primary transition-all p-1"
                >
                  <img src={url} alt={`Avatar ${idx+1}`} className="w-full h-auto rounded-full group-hover:scale-105 transition-transform" />
                </div>
              ))}
            </div>
            <div className="px-6 py-4 bg-surface-container-lowest border-t border-outline-variant/30">
              <p className="text-xs text-on-surface-variant text-center">Silakan klik salah satu avatar di atas untuk langsung mengganti foto profil Anda.</p>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
