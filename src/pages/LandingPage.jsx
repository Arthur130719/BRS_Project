import { useState, useEffect } from 'react';
import '../index.css';

function LandingPage() {
  const [packages, setPackages] = useState([]);
  const [loading, setLoading] = useState(true);

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedPkg, setSelectedPkg] = useState(null);
  const [formData, setFormData] = useState({ nama: '', phone: '', alamat: '', latitude: '', longitude: '' });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitSuccess, setSubmitSuccess] = useState(false);
  const [isGettingLocation, setIsGettingLocation] = useState(false);

  useEffect(() => {
    fetch(`http://${window.location.hostname}:8000/api/paket`)
      .then(res => res.json())
      .then(data => {
        setPackages(data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Error fetching data:', err);
        setLoading(false);
      });
  }, []);

  const scrollToPackages = (e) => {
    e.preventDefault();
    document.getElementById('packages').scrollIntoView({ behavior: 'smooth' });
  };

  const openModal = (pkg) => {
    setSelectedPkg(pkg);
    setIsModalOpen(true);
    setSubmitSuccess(false);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setSelectedPkg(null);
    setFormData({ nama: '', phone: '', alamat: '', latitude: '', longitude: '' });
  };

  const handleGetLocation = () => {
    if (!navigator.geolocation) {
      alert('Browser Anda tidak mendukung fitur lokasi.');
      return;
    }
    
    setIsGettingLocation(true);
    navigator.geolocation.getCurrentPosition(
      (position) => {
        setFormData({
          ...formData,
          latitude: position.coords.latitude,
          longitude: position.coords.longitude
        });
        setIsGettingLocation(false);
      },
      (error) => {
        console.error('Error getting location:', error);
        alert('Gagal mengambil lokasi. Pastikan Anda telah memberikan izin akses lokasi pada browser.');
        setIsGettingLocation(false);
      },
      { enableHighAccuracy: true }
    );
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setIsSubmitting(true);

    fetch(`http://${window.location.hostname}:8000/api/pelanggan/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...formData,
        paket_id: selectedPkg.id
      })
    })
      .then(res => res.json())
      .then(data => {
        setIsSubmitting(false);
        if (data.success) {
          setSubmitSuccess(true);
          setTimeout(() => {
            closeModal();
          }, 3000);
        } else {
          alert('Terjadi kesalahan saat mendaftar.');
        }
      })
      .catch(err => {
        console.error('Registration error:', err);
        setIsSubmitting(false);
        alert('Gagal terhubung ke server.');
      });
  };

  return (
    <>
      {/* TopNavBar */}
      <header className="bg-surface/90 backdrop-blur-md dark:bg-surface-dim/90 docked full-width top-0 sticky z-50 border-b border-outline-variant/30 dark:border-outline/20 shadow-sm flex justify-between items-center w-full px-6 lg:px-margin-desktop max-w-max-width mx-auto py-4">
        <div className="flex items-center gap-gutter">
          <a className="font-headline-md text-headline-md font-bold text-secondary dark:text-secondary-fixed flex items-center gap-2" href="#">
            <img alt="BRS Logo" className="h-10 w-auto object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDpyHUd3GTOUf4CRpHHgdobAINBU41fmRe5JBUoMqOVXLdE3fJUNiyFIEK7QW7cGuJZA4x4osK5r6sI5jzBNPoBao6coIs7Zs9qpzSsJm1umeqBwqzLXlQPydw4xyoQvboVvSi6lAVnruXyFiqBF2hGZOEIZ6aqUPdG84DDAzEx6heWsjxLoJQ6RQSbSVk1VrOHZtvqsM0X_TPJ1Giudd9MOG3loF1U850bNxU0saCNRIahYYRGImnPxyr-p5qka1kXL3v9FcYMXlA" />
            <div className="flex items-center gap-2 border-l border-outline-variant/30 pl-2 ml-2">
              <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBPB94dI5_ziZokzvD2f0bFNHrYzTF6YBcbMuZsJow889i5JHpf8gtu3asWLd1avDFt8FpYOMxc0pMGZC5Sad9m-5kNNrRl81MN3EZvwX1740q3grRrJc9hkDVfxmr-SIuMS_k4foDVR0_Zum4OQanuxsBtMsjEbqN5FoURMjpdjgBaSw8Ans-9DzxN7pF71Y2bLxr_ty-bLOk3AU7iHpV7_LQznUlyO5DRAPOd4uui94WpXimLwmmf14K6xQTWpptlHdH_rU8Keww" alt="MCI Logo" className="h-8 w-auto object-contain" />
            </div>
          </a>
        </div>
        <nav className="hidden md:flex items-center gap-lg">
          <a className="text-on-surface-variant hover:text-primary transition-colors duration-200 font-label-md text-label-md hover:opacity-90" href="#services">Services</a>
          <a className="text-on-surface-variant hover:text-primary transition-colors duration-200 font-label-md text-label-md hover:opacity-90" href="#coverage">Coverage Area</a>
          <a className="text-on-surface-variant hover:text-primary transition-colors duration-200 font-label-md text-label-md hover:opacity-90" href="#why-us">Why Choose Us</a>
        </nav>
        <div className="flex items-center gap-3 sm:gap-4">
          <a href="/login" className="flex text-secondary font-label-md text-label-md font-bold hover:text-primary-container transition-colors items-center gap-1">
            <span className="material-symbols-outlined text-[20px]">login</span> <span className="hidden sm:inline">Portal</span>
          </a>
          <button onClick={scrollToPackages} className="bg-primary-container text-on-primary font-label-md text-label-md font-bold py-2 px-4 sm:px-6 rounded-full hover:opacity-90 transition-opacity scale-95 transition-transform duration-150">Daftar</button>
        </div>
      </header>

      <main>
        {/* Hero Section */}
        <section className="relative pt-24 pb-xl px-6 lg:px-margin-desktop max-w-max-width mx-auto overflow-hidden">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-xl items-center relative z-10">
            <div className="flex flex-col gap-md">
              <h1 className="font-headline-xl text-headline-xl text-on-surface leading-tight text-secondary">
                Internet Cepat & Stabil, <br />
                <span className="text-primary-container">Pemasangan Tanpa Ribet!</span>
              </h1>
              <p className="font-body-lg text-body-lg text-on-surface-variant max-w-xl">
                Bina Raja Solusi hadir memberikan solusi internet yang stabil dan terpercaya untuk kebutuhan rumah dan kantor Anda. <span className="opacity-50 text-sm">Supported by MCI.</span>
              </p>
              <div className="flex flex-col sm:flex-row gap-4 pt-4">
                <button onClick={scrollToPackages} className="bg-primary-container text-on-primary font-label-md text-label-md font-bold py-3 px-8 rounded-full hover:bg-surface-tint transition-colors shadow-md flex items-center justify-center gap-2">
                  Cek Ketersediaan Sekarang
                  <span className="material-symbols-outlined text-lg">arrow_forward</span>
                </button>
                <button onClick={scrollToPackages} className="bg-transparent border-2 border-secondary text-secondary font-label-md text-label-md font-bold py-3 px-8 rounded-full hover:bg-secondary hover:text-on-secondary transition-colors flex items-center justify-center">
                  Lihat Paket
                </button>
              </div>
            </div>
            <div className="relative h-[500px] w-full rounded-2xl overflow-hidden ambient-shadow border border-outline-variant/20">
              <div className="bg-cover bg-center w-full h-full" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuCg5A2Dji5enQDiPpYq4hMmLtAxjg61qp7QRAZ0YX7NFbskxEsOZyzQuC2JdGwRT8oyuySifAbQcdYZV4290nKa5agwiqtS0cEgraDrnzaq4LaHI2OOWGk4hWgcTYOlZ6bSaB8nixe-6GDiQ_xFAT7aI786ixGdzWCtD53uh8ysCk_6mbU51vpNOdGVmiSNVp5ynxuYafvrWd1ThSNkeAiDBZhcRyKKnRyQ-kgfuDRgBV2TKZwctimpgY5k_4n2VG_8iUv43SIsMu8')" }}></div>
            </div>
          </div>
          {/* Abstract background shape */}
          <div className="absolute top-0 right-0 -z-10 w-1/2 h-full bg-gradient-to-bl from-secondary-fixed/30 to-transparent rounded-bl-full blur-3xl opacity-50"></div>
        </section>

        {/* Key Benefits Bento Grid */}
        <section id="why-us" className="py-xl px-6 lg:px-margin-desktop bg-surface-container-low border-y border-outline-variant/30">
          <div className="max-w-max-width mx-auto">
            <div className="text-center mb-16">
              <h2 className="font-headline-lg text-headline-lg text-secondary mb-4">Mengapa Memilih BRS?</h2>
              <p className="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">Kami memastikan setiap instalasi dilakukan dengan presisi dan standar profesional tinggi.</p>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-gutter">
              <div className="bg-surface rounded-xl p-8 ambient-shadow border border-outline-variant/20 flex flex-col items-start gap-4 hover:-translate-y-1 transition-transform duration-300">
                <div className="w-14 h-14 rounded-full bg-primary-fixed flex items-center justify-center"><span className="material-symbols-outlined text-primary-container text-3xl" style={{ fontVariationSettings: "'FILL' 1" }}>speed</span></div>
                <h3 className="font-headline-md text-headline-md text-secondary">Pemasangan Cepat & Rapi</h3>
                <p className="font-body-md text-body-md text-on-surface-variant">Nikmati kemudahan aktivasi internet tanpa menunggu lama dengan standar instalasi yang bersih dan profesional.</p>
              </div>
              <div className="bg-surface rounded-xl p-8 ambient-shadow border border-outline-variant/20 flex flex-col items-start gap-4 hover:-translate-y-1 transition-transform duration-300">
                <div className="w-14 h-14 rounded-full bg-primary-fixed flex items-center justify-center"><span className="material-symbols-outlined text-primary-container text-3xl" style={{ fontVariationSettings: "'FILL' 1" }}>verified_user</span></div>
                <h3 className="font-headline-md text-headline-md text-secondary">Layanan Lokal Terpercaya</h3>
                <p className="font-body-md text-body-md text-on-surface-variant">Sebagai mitra resmi di wilayah Anda, kami hadir lebih dekat untuk memberikan solusi yang cepat dan personal.</p>
              </div>
              <div className="bg-surface rounded-xl p-8 ambient-shadow border border-outline-variant/20 flex flex-col items-start gap-4 hover:-translate-y-1 transition-transform duration-300">
                <div className="w-14 h-14 rounded-full bg-primary-fixed flex items-center justify-center"><span className="material-symbols-outlined text-primary-container text-3xl" style={{ fontVariationSettings: "'FILL' 1" }}>payments</span></div>
                <h3 className="font-headline-md text-headline-md text-secondary">Harga Paket Ekonomis</h3>
                <p className="font-body-md text-body-md text-on-surface-variant">Dapatkan koneksi internet berkualitas tinggi dengan biaya bulanan yang terjangkau bagi keluarga maupun bisnis Anda.</p>
              </div>
            </div>
          </div>
        </section>

        {/* Pricing Plans */}
        <section id="packages" className="py-xl px-6 lg:px-margin-desktop max-w-max-width mx-auto">
          <div className="text-center mb-16">
            <h2 className="font-headline-lg text-headline-lg text-secondary mb-4">Pilihan Paket Internet</h2>
            <p className="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">Solusi tepat untuk setiap kebutuhan konektivitas Anda.</p>
          </div>
          
          {loading ? (
            <div className="text-center py-10 text-on-surface-variant animate-pulse font-body-md">Memuat paket dari sistem BRS...</div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-3 gap-gutter items-stretch">
              {packages.length === 0 && <div className="col-span-3 text-center">Belum ada paket.</div>}
              {packages.map((pkg, index) => {
                const isFeatured = index === 1 || (pkg.nama && pkg.nama.toLowerCase().includes('bisnis'));
                const harga = Number(pkg.harga) || 0;
                
                if (isFeatured) {
                  return (
                    <div key={pkg.id || index} className="bg-surface rounded-xl p-8 ambient-shadow border-x border-b border-outline-variant/30 trust-header flex flex-col relative transform md:-translate-y-4 shadow-lg">
                      <div className="absolute top-0 right-0 bg-primary-container text-on-primary font-label-sm text-label-sm px-3 py-1 rounded-bl-lg rounded-tr-lg font-bold">TERPOPULER</div>
                      <h3 className="font-headline-md text-headline-md text-secondary mb-2 mt-2">{pkg.nama}</h3>
                      <p className="font-body-md text-body-md text-on-surface-variant mb-6 pb-6 border-b border-outline-variant/20">Solusi internet ultra-cepat untuk produktivitas tanpa batas.</p>
                      
                      <div className="mb-4">
                        <span className="font-headline-xl text-headline-xl text-secondary">{pkg.kecepatan_down}</span>
                        <span className="font-body-md text-body-md text-on-surface-variant"> Mbps</span>
                      </div>
                      <div className="mb-8">
                        <span className="font-headline-md text-headline-md text-primary-container">Rp {harga.toLocaleString('id-ID')}</span>
                        <span className="font-body-md text-body-md text-on-surface-variant text-sm"> / bulan</span>
                      </div>
                      
                      <ul className="flex flex-col gap-4 mb-8 flex-grow">
                        <li className="flex items-center gap-3">
                          <span className="material-symbols-outlined text-primary-container text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>check_circle</span>
                          <span className="font-body-md text-body-md">Kecepatan Upload & Download Simetris</span>
                        </li>
                        <li className="flex items-center gap-3">
                          <span className="material-symbols-outlined text-primary-container text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>check_circle</span>
                          <span className="font-body-md text-body-md">Koneksi Stabil Berkinerja Tinggi</span>
                        </li>
                      </ul>
                      <button onClick={() => openModal(pkg)} className="w-full bg-primary-container text-on-primary font-label-md text-label-md font-bold py-3 rounded-full hover:bg-surface-tint transition-colors shadow-md">Pilih Paket</button>
                    </div>
                  );
                }

                return (
                  <div key={pkg.id || index} className="bg-surface rounded-xl p-8 ambient-shadow border border-outline-variant/30 flex flex-col relative">
                    <h3 className="font-headline-md text-headline-md text-secondary mb-2">{pkg.nama}</h3>
                    <p className="font-body-md text-body-md text-on-surface-variant mb-6 pb-6 border-b border-outline-variant/20">Koneksi andal untuk hiburan keluarga dan harian Anda.</p>
                    
                    <div className="mb-4">
                      <span className="font-headline-xl text-headline-xl text-secondary">{pkg.kecepatan_down}</span>
                      <span className="font-body-md text-body-md text-on-surface-variant"> Mbps</span>
                    </div>
                    <div className="mb-8">
                      <span className="font-headline-md text-headline-md text-primary-container">Rp {harga.toLocaleString('id-ID')}</span>
                      <span className="font-body-md text-body-md text-on-surface-variant text-sm"> / bulan</span>
                    </div>
                    
                    <ul className="flex flex-col gap-4 mb-8 flex-grow">
                      <li className="flex items-center gap-3">
                        <span className="material-symbols-outlined text-primary-container text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>check_circle</span>
                        <span className="font-body-md text-body-md">Kuota Bebas (Unlimited) Tanpa Batas FUP</span>
                      </li>
                      <li className="flex items-center gap-3">
                        <span className="material-symbols-outlined text-primary-container text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>check_circle</span>
                        <span className="font-body-md text-body-md">Gratis Peminjaman Perangkat Modem WiFi</span>
                      </li>
                    </ul>
                    <button onClick={() => openModal(pkg)} className="w-full bg-transparent border-2 border-secondary text-secondary font-label-md text-label-md font-bold py-3 rounded-full hover:bg-secondary hover:text-on-secondary transition-colors">Pilih Paket</button>
                  </div>
                );
              })}
            </div>
          )}
        </section>

        {/* Installation Process Flow */}
        <section id="services" className="py-xl px-6 lg:px-margin-desktop bg-surface-container-low border-y border-outline-variant/30">
          <div className="max-w-max-width mx-auto">
            <div className="text-center mb-16">
              <h2 className="font-headline-lg text-headline-lg text-secondary mb-4">Proses Pemasangan Mudah</h2>
              <p className="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">Transparansi di setiap langkah, dari pemesanan hingga online.</p>
            </div>
            <div className="relative flex flex-col md:flex-row justify-between items-center md:items-start gap-8 md:gap-4">
              <div className="hidden md:block absolute top-8 left-[10%] right-[10%] h-1 bg-secondary opacity-20 -z-10"></div>
              
              <div className="flex flex-col items-center text-center w-full md:w-1/4 relative z-10">
                <div className="w-16 h-16 rounded-full bg-surface border-4 border-primary-container flex items-center justify-center mb-4 ambient-shadow">
                  <span className="material-symbols-outlined text-secondary text-2xl">shopping_cart</span>
                </div>
                <h4 className="font-headline-md text-headline-md text-secondary text-lg mb-2">1. Pesan</h4>
                <p className="font-body-md text-body-md text-on-surface-variant text-sm">Pilih paket dan isi form pendaftaran.</p>
              </div>
              
              <div className="flex flex-col items-center text-center w-full md:w-1/4 relative z-10">
                <div className="w-16 h-16 rounded-full bg-surface border-4 border-primary-container flex items-center justify-center mb-4 ambient-shadow">
                  <span className="material-symbols-outlined text-secondary text-2xl">explore</span>
                </div>
                <h4 className="font-headline-md text-headline-md text-secondary text-lg mb-2">2. Survey</h4>
                <p className="font-body-md text-body-md text-on-surface-variant text-sm">Tim kami mengecek ketersediaan jaringan di lokasi.</p>
              </div>
              
              <div className="flex flex-col items-center text-center w-full md:w-1/4 relative z-10">
                <div className="w-16 h-16 rounded-full bg-surface border-4 border-primary-container flex items-center justify-center mb-4 ambient-shadow">
                  <span className="material-symbols-outlined text-secondary text-2xl">build</span>
                </div>
                <h4 className="font-headline-md text-headline-md text-secondary text-lg mb-2">3. Pasang</h4>
                <p className="font-body-md text-body-md text-on-surface-variant text-sm">Instalasi perangkat oleh teknisi profesional kami.</p>
              </div>
              
              <div className="flex flex-col items-center text-center w-full md:w-1/4 relative z-10">
                <div className="w-16 h-16 rounded-full bg-primary-container border-4 border-primary-container flex items-center justify-center mb-4 ambient-shadow shadow-primary-container/30">
                  <span className="material-symbols-outlined text-on-primary text-2xl" style={{ fontVariationSettings: "'FILL' 1" }}>wifi_tethering</span>
                </div>
                <h4 className="font-headline-md text-headline-md text-secondary text-lg mb-2">4. Nikmati</h4>
                <p className="font-body-md text-body-md text-on-surface-variant text-sm">Internet cepat dan stabil siap digunakan.</p>
              </div>
            </div>
          </div>
        </section>

        {/* Coverage Area Section */}
        <section id="coverage" className="py-xl px-6 lg:px-margin-desktop max-w-max-width mx-auto">
          <div className="bg-surface rounded-2xl p-8 md:p-12 ambient-shadow border border-outline-variant/30 text-center relative overflow-hidden">
            <div className="absolute top-0 right-0 -z-10 w-64 h-64 bg-primary-container/20 rounded-full blur-3xl opacity-50 transform translate-x-1/2 -translate-y-1/2"></div>
            <div className="absolute bottom-0 left-0 -z-10 w-64 h-64 bg-secondary/10 rounded-full blur-3xl opacity-50 transform -translate-x-1/2 translate-y-1/2"></div>
            
            <span className="material-symbols-outlined text-primary-container text-5xl mb-4">location_on</span>
            <h2 className="font-headline-lg text-headline-lg text-secondary mb-4">Jangkauan Wilayah BRS</h2>
            <p className="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto mb-8">Saat ini jaringan BRS telah beroperasi dan siap melayani pemasangan di wilayah <strong className="text-secondary font-bold">Kabupaten Tangerang</strong>, meliputi area berikut:</p>
            
            <div className="flex flex-wrap justify-center gap-3 mb-8">
              {['Tigaraksa', 'Balaraja', 'Saga', 'Bunar', 'Merak', 'Sukamulya', 'Koper'].map(area => (
                <span key={area} className="bg-surface-container-high border border-outline-variant/30 text-secondary font-label-md px-6 py-2 rounded-full ambient-shadow text-sm font-bold">
                  {area}
                </span>
              ))}
            </div>
            
            <div className="bg-primary-fixed-dim/20 inline-block p-4 rounded-xl border border-primary-container/30">
              <p className="font-body-md text-on-surface-variant text-sm flex items-center justify-center gap-2">
                <span className="material-symbols-outlined text-primary-container text-xl">info</span>
                Di luar wilayah yang disebutkan? Silakan hubungi Admin kami untuk mengecek ketersediaan jaringan di <span className="font-bold text-secondary">087761205991</span>
              </p>
            </div>
          </div>
        </section>
      </main>


      {/* Footer */}
      <footer className="bg-surface-container-highest dark:bg-inverse-surface full-width flat no shadows">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-gutter px-6 lg:px-margin-desktop py-xl max-w-max-width mx-auto">
          <div className="md:col-span-1 flex flex-col gap-4">
            <span className="font-headline-md text-headline-md font-bold text-secondary dark:text-secondary-fixed-dim">
              <img alt="BRS Logo" className="h-10 w-auto object-contain mb-2" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDpyHUd3GTOUf4CRpHHgdobAINBU41fmRe5JBUoMqOVXLdE3fJUNiyFIEK7QW7cGuJZA4x4osK5r6sI5jzBNPoBao6coIs7Zs9qpzSsJm1umeqBwqzLXlQPydw4xyoQvboVvSi6lAVnruXyFiqBF2hGZOEIZ6aqUPdG84DDAzEx6heWsjxLoJQ6RQSbSVk1VrOHZtvqsM0X_TPJ1Giudd9MOG3loF1U850bNxU0saCNRIahYYRGImnPxyr-p5qka1kXL3v9FcYMXlA" />
            </span>
            <p className="font-body-md text-body-md text-secondary dark:text-secondary-fixed-dim opacity-80 text-sm">
              Memberikan solusi internet terbaik dengan keandalan dan kecepatan tanpa kompromi.
            </p>
          </div>
          <div className="md:col-span-3 grid grid-cols-2 sm:grid-cols-3 gap-8">
            <div className="flex flex-col gap-3">
              <h4 className="font-label-sm text-label-sm text-secondary font-bold uppercase tracking-wider mb-2">Perusahaan</h4>
              <a className="text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md text-sm hover:text-primary-container" href="#">Home</a>
              <a className="text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md text-sm hover:text-primary-container" href="#services">Services</a>
              <a className="text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md text-sm hover:text-primary-container" href="#coverage">Coverage</a>
            </div>
            <div className="flex flex-col gap-3">
              <h4 className="font-label-sm text-label-sm text-secondary font-bold uppercase tracking-wider mb-2">Legal</h4>
              <a className="text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md text-sm hover:text-primary-container" href="#">Terms of Service</a>
              <a className="text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md text-sm hover:text-primary-container" href="#">Privacy Policy</a>
            </div>
            <div className="flex flex-col gap-3">
              <h4 className="font-label-sm text-label-sm text-secondary font-bold uppercase tracking-wider mb-2">Kontak</h4>
              <span className="text-on-surface-variant font-body-md text-body-md text-sm flex items-center gap-2"><span className="material-symbols-outlined text-sm">call</span> 087761205991</span>
              <span className="text-on-surface-variant font-body-md text-body-md text-sm flex items-center gap-2"><span className="material-symbols-outlined text-sm">mail</span> ptbinarajasolusi12345@gmail.com</span>
              <span className="text-on-surface-variant font-body-md text-body-md text-sm flex items-start gap-2"><span className="material-symbols-outlined text-sm mt-1">location_on</span> Jl. Raya Permata No.11, Saga, Balaraja</span>
            </div>
          </div>
        </div>
        <div className="border-t border-outline-variant/20 px-6 lg:px-margin-desktop py-6 max-w-max-width mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
          <p className="font-label-sm text-label-sm text-on-surface-variant text-center md:text-left">
            © 2024 Bina Raja Solusi (BRS). Official Partner of ISP Media Cepat Indonesia (MCI).
          </p>
        </div>
      </footer>

      {/* Registration Modal Overlay */}
      {isModalOpen && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-secondary/40 backdrop-blur-sm" onClick={closeModal}></div>
          <div className="bg-surface relative z-10 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-outline-variant/30 animate-in fade-in zoom-in duration-200 flex flex-col max-h-[90vh]">
            {/* Modal Header */}
            <div className="bg-surface-container-low px-6 py-4 border-b border-outline-variant/30 flex justify-between items-center shrink-0">
              <h3 className="font-headline-md text-secondary">Pendaftaran Layanan</h3>
              <button onClick={closeModal} className="text-on-surface-variant hover:text-error transition-colors">
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>
            
            {/* Modal Body */}
            <div className="p-6 overflow-y-auto flex-1 min-h-0">
              {submitSuccess ? (
                <div className="text-center py-6">
                  <div className="w-16 h-16 rounded-full bg-[#ecfdf5] text-[#10b981] flex items-center justify-center mx-auto mb-4">
                    <span className="material-symbols-outlined text-3xl">check</span>
                  </div>
                  <h4 className="font-headline-md text-secondary mb-2">Pendaftaran Berhasil!</h4>
                  <p className="font-body-md text-on-surface-variant">Tim BRS akan segera menghubungi Anda melalui nomor WhatsApp yang terdaftar.</p>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                  <div className="bg-primary-fixed-dim/20 p-4 rounded-lg border border-primary-container/30 mb-2">
                    <p className="font-label-sm text-primary-container mb-1">Paket Terpilih</p>
                    <p className="font-headline-md text-secondary">{selectedPkg?.nama} - {selectedPkg?.kecepatan_down} Mbps</p>
                  </div>
                  
                  <div className="flex flex-col gap-1">
                    <label className="font-label-sm text-secondary">Nama Lengkap</label>
                    <input required type="text" value={formData.nama} onChange={e => setFormData({...formData, nama: e.target.value})} className="px-4 py-2 bg-surface-bright border border-outline-variant/50 rounded-lg focus:outline-none focus:border-primary-container font-body-md text-on-surface" placeholder="Masukkan nama Anda" />
                  </div>
                  
                  <div className="flex flex-col gap-1">
                    <label className="font-label-sm text-secondary">Nomor WhatsApp / HP</label>
                    <input required type="tel" value={formData.phone} onChange={e => setFormData({...formData, phone: e.target.value})} className="px-4 py-2 bg-surface-bright border border-outline-variant/50 rounded-lg focus:outline-none focus:border-primary-container font-body-md text-on-surface" placeholder="08123456789" />
                  </div>

                  <div className="flex flex-col gap-1">
                    <label className="font-label-sm text-secondary">Alamat Lengkap Pemasangan</label>
                    <textarea required rows="3" value={formData.alamat} onChange={e => setFormData({...formData, alamat: e.target.value})} className="px-4 py-2 bg-surface-bright border border-outline-variant/50 rounded-lg focus:outline-none focus:border-primary-container font-body-md text-on-surface" placeholder="Masukkan detail alamat (Jalan, RT/RW, Patokan)"></textarea>
                  </div>

                  <div className="flex flex-col gap-2 mt-2">
                    <button 
                      type="button" 
                      onClick={handleGetLocation}
                      disabled={isGettingLocation}
                      className="flex items-center justify-center gap-2 w-full py-2 border-2 border-primary-container text-primary-container font-label-md font-bold rounded-lg hover:bg-primary-container hover:text-on-primary transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <span className="material-symbols-outlined text-xl">
                        {isGettingLocation ? 'hourglass_empty' : 'my_location'}
                      </span>
                      {isGettingLocation ? 'Mengambil Lokasi...' : 'Bagikan Titik Lokasi Anda'}
                    </button>
                    {formData.latitude && formData.longitude && (
                      <p className="text-xs text-[#10b981] flex items-center justify-center gap-1">
                        <span className="material-symbols-outlined text-sm">check_circle</span>
                        Titik lokasi berhasil dicatat
                      </p>
                    )}
                  </div>
                  
                  <button disabled={isSubmitting} type="submit" className={`mt-4 w-full bg-primary-container text-on-primary font-label-md py-3 rounded-full shadow-md transition-colors ${isSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:bg-surface-tint'}`}>
                    {isSubmitting ? 'Mengirim Data...' : 'Kirim Pendaftaran'}
                  </button>
                </form>
              )}
            </div>
          </div>
        </div>
      )}
    </>
  );
}

export default LandingPage;
