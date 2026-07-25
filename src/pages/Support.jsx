import { useState, useEffect, useRef } from 'react';

export default function Support() {
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  
  // Form state
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [subject, setSubject] = useState('');
  const [alamat, setAlamat] = useState('');
  const [deskripsi, setDeskripsi] = useState('');
  const [latitude, setLatitude] = useState('');
  const [longitude, setLongitude] = useState('');
  const [jenisTiket, setJenisTiket] = useState('gangguan');
  const [newWifiName, setNewWifiName] = useState('');
  const [newWifiPassword, setNewWifiPassword] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [isGettingLocation, setIsGettingLocation] = useState(false);
  
  // Chat state
  const [ticketChats, setTicketChats] = useState([]);
  const [chatMessage, setChatMessage] = useState('');
  const [isSendingChat, setIsSendingChat] = useState(false);
  const chatContainerRef = useRef(null);

  // Auto-scroll chat to bottom
  useEffect(() => {
    if (chatContainerRef.current) {
      chatContainerRef.current.scrollTop = chatContainerRef.current.scrollHeight;
    }
  }, [ticketChats]);

  const fetchTickets = () => {
    const token = sessionStorage.getItem('brs_token');
    fetch(`http://${window.location.hostname}:8000/api/pelanggan/tickets`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
      .then(res => res.json())
      .then(data => {
        setTickets(data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Error fetching tickets:', err);
        setLoading(false);
      });
  };

  useEffect(() => {
    fetchTickets();
  }, []);

  // Polling for real-time chat updates
  useEffect(() => {
    let interval;
    if (selectedTicket) {
      interval = setInterval(() => {
        fetchChats(selectedTicket.id);
      }, 3000); // fetch every 3 seconds
    }
    return () => {
      if (interval) clearInterval(interval);
    };
  }, [selectedTicket]);

  const fetchChats = (ticketId) => {
    const token = sessionStorage.getItem('brs_token');
    fetch(`http://${window.location.hostname}:8000/api/pelanggan/tickets/${ticketId}/chats`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
      .then(res => res.json())
      .then(data => {
        if(Array.isArray(data)) setTicketChats(data);
      })
      .catch(err => console.error('Error fetching chats:', err));
  };

  const handleOpenTicket = (ticket) => {
    setSelectedTicket(ticket);
    setTicketChats([]);
    fetchChats(ticket.id);
  };

  const handleSendChat = (e) => {
    e.preventDefault();
    if (!chatMessage.trim()) return;
    
    setIsSendingChat(true);
    const token = sessionStorage.getItem('brs_token');
    
    fetch(`http://${window.location.hostname}:8000/api/pelanggan/tickets/${selectedTicket.id}/chats`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ message: chatMessage })
    })
      .then(res => res.json())
      .then(data => {
        setIsSendingChat(false);
        setChatMessage('');
        fetchChats(selectedTicket.id); // refresh chats
      })
      .catch(err => {
        console.error('Error sending chat:', err);
        setIsSendingChat(false);
      });
  };

  const handleGetLocation = () => {
    // Di iOS Safari dan browser modern, Geolocation diblokir jika tidak pakai HTTPS
    if (window.isSecureContext === false) {
      alert('Peringatan: Anda mengakses web menggunakan IP tanpa HTTPS (Tidak Aman).\n\nApple Safari dan browser modern memblokir total akses GPS di koneksi HTTP.\nSolusi: Silakan ketik alamat sedetail mungkin beserta patokan, atau paste link Google Maps di kolom Deskripsi.');
      return;
    }

    if (!navigator.geolocation) {
      alert('Browser Anda tidak mendukung fitur lokasi.');
      return;
    }
    
    setIsGettingLocation(true);
    navigator.geolocation.getCurrentPosition(
      (position) => {
        setLatitude(position.coords.latitude);
        setLongitude(position.coords.longitude);
        setIsGettingLocation(false);
      },
      (error) => {
        console.error('Error getting location:', error);
        if (error.code === error.PERMISSION_DENIED) {
            alert('Gagal mengambil lokasi. Akses lokasi ditolak oleh browser/HP Anda. Coba periksa pengaturan izin lokasi di browser.');
        } else if (error.code === error.POSITION_UNAVAILABLE) {
            alert('Gagal: Sinyal GPS tidak tersedia atau melemah.');
        } else if (error.code === error.TIMEOUT) {
            alert('Gagal: Waktu pengambilan lokasi habis (Timeout).');
        } else {
            alert('Gagal mengambil lokasi. Pastikan Anda memberikan izin akses lokasi.');
        }
        setIsGettingLocation(false);
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    const token = sessionStorage.getItem('brs_token');

    fetch(`http://${window.location.hostname}:8000/api/pelanggan/tickets`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ 
        subject: jenisTiket === 'ganti_password' ? 'Request Ganti Password WiFi' : subject, 
        deskripsi: jenisTiket === 'ganti_password' ? `Nama WiFi Baru: ${newWifiName}\nPassword Baru: ${newWifiPassword}` : deskripsi, 
        alamat: jenisTiket === 'ganti_password' ? '-' : alamat, 
        latitude: jenisTiket === 'ganti_password' ? null : latitude, 
        longitude: jenisTiket === 'ganti_password' ? null : longitude 
      })
    })
      .then(res => res.json())
      .then(data => {
        setIsSubmitting(false);
        setSubject('');
        setAlamat('');
        setDeskripsi('');
        setLatitude('');
        setLongitude('');
        setNewWifiName('');
        setNewWifiPassword('');
        setJenisTiket('gangguan');
        setShowForm(false);
        fetchTickets(); // Refresh list
      })
      .catch(err => {
        console.error('Error creating ticket:', err);
        setIsSubmitting(false);
        alert('Gagal membuat tiket. Silakan coba lagi.');
      });
  };

  const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute:'2-digit'
    });
  };

  if (loading) {
    return <div className="h-96 flex items-center justify-center">
        <p className="font-headline-md text-secondary animate-pulse">Memuat Tiket Bantuan...</p>
    </div>;
  }

  return (
    <div className="flex flex-col gap-6">
      <header className="flex justify-between items-end mb-4">
        <div>
          <h1 className="font-headline-lg text-secondary mb-2 flex items-center gap-2">
            <span className="material-symbols-outlined text-3xl">support_agent</span>
            Pusat Bantuan
          </h1>
          <p className="font-body-md text-on-surface-variant">Laporkan kendala jaringan atau ajukan ganti password WiFi Anda di sini.</p>
        </div>
        {!showForm && (
          <button 
            onClick={() => setShowForm(true)}
            className="bg-primary-container text-on-primary font-label-md font-bold px-6 py-2 rounded-full hover:opacity-90 transition-opacity flex items-center gap-2 shadow-sm"
          >
            <span className="material-symbols-outlined text-sm">add</span> Buat Tiket Baru
          </button>
        )}
      </header>

      {showForm && (
        <div className="glass-panel rounded-xl p-6 border border-outline-variant/30 animate-in fade-in slide-in-from-top-4 duration-300">
          <div className="flex justify-between items-center mb-4">
            <h3 className="font-headline-md text-secondary">Buat Tiket Bantuan</h3>
            <button onClick={() => setShowForm(false)} className="text-on-surface-variant hover:text-error transition-colors">
              <span className="material-symbols-outlined">close</span>
            </button>
          </div>
          
          <div className="flex gap-3 mb-5">
            <button 
              type="button" 
              onClick={() => setJenisTiket('gangguan')} 
              className={`px-4 py-2 rounded-full font-label-md font-bold transition-colors ${jenisTiket === 'gangguan' ? 'bg-primary-container text-on-primary shadow-sm' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant'}`}
            >
              Lapor Gangguan
            </button>
            <button 
              type="button" 
              onClick={() => setJenisTiket('ganti_password')} 
              className={`px-4 py-2 rounded-full font-label-md font-bold transition-colors ${jenisTiket === 'ganti_password' ? 'bg-primary-container text-on-primary shadow-sm' : 'bg-surface-container text-on-surface-variant hover:bg-surface-variant'}`}
            >
              Ganti Password WiFi
            </button>
          </div>

          <form onSubmit={handleSubmit} className="flex flex-col gap-5">
            
            {jenisTiket === 'gangguan' ? (
              <>
                <div className="flex flex-col gap-2">
                  <label className="font-label-md font-bold text-secondary">Judul Masalah</label>
                  <input 
                    type="text" 
                    value={subject}
                    onChange={(e) => setSubject(e.target.value)}
                    className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface focus:outline-none focus:border-primary transition-colors"
                    placeholder="Contoh: Internet Mati Sejak Pagi"
                    required
                  />
                </div>

                <div className="flex flex-col gap-2">
                  <label className="font-label-md font-bold text-secondary">Alamat Pemasangan</label>
                  <input 
                    type="text" 
                    value={alamat}
                    onChange={(e) => setAlamat(e.target.value)}
                    className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface focus:outline-none focus:border-primary transition-colors"
                    placeholder="Tuliskan alamat lengkap beserta patokan rumah..."
                    required
                  />
                </div>

                <div className="flex flex-col gap-2">
                  <label className="font-label-md font-bold text-secondary">Deskripsi Detail</label>
                  <textarea 
                    required
                    value={deskripsi}
                    onChange={(e) => setDeskripsi(e.target.value)}
                    placeholder="Contoh: Lag saat buka aplikasi YouTube, nyala indikator merah pada router, dll."
                    rows="4"
                    className="w-full px-4 py-2 bg-surface-bright border border-outline-variant/50 rounded-lg focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container font-body-md text-on-surface transition-all resize-none"
                  ></textarea>
                </div>
              </>
            ) : (
              <>
                <div className="flex flex-col gap-2">
                  <label className="font-label-md font-bold text-secondary">Nama WiFi (SSID) Baru</label>
                  <p className="text-xs text-on-surface-variant mb-1">
                    Jika hanya ingin ganti password saja, isi dengan nama WiFi Anda saat ini. Jika ingin ganti nama juga, masukkan nama WiFi yang baru.
                  </p>
                  <input 
                    type="text" 
                    value={newWifiName}
                    onChange={(e) => setNewWifiName(e.target.value)}
                    className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface focus:outline-none focus:border-primary transition-colors"
                    placeholder="Masukkan nama WiFi yang diinginkan..."
                    required
                  />
                </div>

                <div className="flex flex-col gap-2">
                  <label className="font-label-md font-bold text-secondary">Password WiFi Baru</label>
                  <input 
                    type="text" 
                    value={newWifiPassword}
                    onChange={(e) => setNewWifiPassword(e.target.value)}
                    className="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-3 font-body-md text-on-surface focus:outline-none focus:border-primary transition-colors"
                    placeholder="Masukkan password WiFi baru (minimal 8 karakter)..."
                    required
                    minLength="8"
                  />
                </div>
                <div className="p-3 bg-primary-container/10 border border-primary-container/20 rounded-lg">
                  <p className="text-sm text-secondary font-medium"><i className="fas fa-info-circle mr-2"></i>Catatan: Penggantian password WiFi tidak memerlukan verifikasi lokasi. Tiket akan langsung diproses oleh admin.</p>
                </div>
              </>
            )}
            
            {jenisTiket === 'gangguan' && (
              <div className="flex flex-col gap-2">
                <button 
                  type="button" 
                  onClick={handleGetLocation}
                  disabled={isGettingLocation}
                  className="flex items-center justify-center gap-2 w-full py-2 border-2 border-primary-container text-primary-container font-label-md font-bold rounded-lg hover:bg-primary-container hover:text-on-primary transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span className="material-symbols-outlined text-xl">
                    {isGettingLocation ? 'hourglass_empty' : 'my_location'}
                  </span>
                  {isGettingLocation ? 'Mengambil Lokasi...' : 'Sertakan Titik Lokasi Anda'}
                </button>
                
                {window.isSecureContext === false && (
                  <p className="text-xs text-error font-medium mt-1 text-center bg-error/10 py-1 rounded">
                    Browser memblokir fitur lokasi karena koneksi tidak menggunakan HTTPS.
                  </p>
                )}
                {latitude && longitude && (
                  <p className="text-xs text-primary font-medium mt-1 text-center bg-primary-container/20 py-1 rounded flex justify-center items-center gap-1">
                    <span className="material-symbols-outlined text-[14px]">check_circle</span>
                    Lokasi berhasil dilampirkan
                  </p>
                )}
              </div>
            )}

            <div className="flex justify-end gap-3 mt-2">
              <button 
                type="button" 
                onClick={() => setShowForm(false)}
                className="px-6 py-2 rounded-lg font-label-md font-bold text-on-surface-variant hover:bg-surface-container-high transition-colors border border-outline-variant/30"
              >
                Batal
              </button>
              <button 
                type="submit" 
                disabled={isSubmitting}
                className={`px-6 py-2 rounded-lg font-label-md font-bold text-on-primary bg-primary-container transition-colors flex items-center gap-2 ${isSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:bg-surface-tint'}`}
              >
                {isSubmitting ? 'Mengirim...' : 'Kirim Tiket'}
              </button>
            </div>
          </form>
        </div>
      )}

      <div className="glass-panel rounded-xl overflow-hidden border border-outline-variant/30">
        <div className="p-4 border-b border-outline-variant/30 bg-surface-container-lowest">
          <h3 className="font-label-md font-bold text-secondary uppercase tracking-wider">Riwayat Tiket Saya</h3>
        </div>
        <div className="flex flex-col divide-y divide-outline-variant/20">
          {tickets.length > 0 ? tickets.map((ticket) => (
            <div key={ticket.id} className="p-5 hover:bg-surface-container-lowest/50 transition-colors flex flex-col sm:flex-row gap-4 justify-between items-start">
              <div className="flex-1">
                <div className="flex items-center gap-3 mb-1">
                  <h4 className="font-headline-sm text-secondary font-bold">{ticket.subject}</h4>
                  <span className={`px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider ${
                    ticket.status === 'open' ? 'bg-error-container text-error' :
                    ticket.status === 'in_progress' ? 'bg-[#fef08a] text-[#854d0e]' :
                    'bg-[#ecfdf5] text-[#065f46]'
                  }`}>
                    {ticket.status.replace('_', ' ')}
                  </span>
                </div>
                <p className="font-body-sm text-on-surface-variant mb-2 line-clamp-2">{ticket.deskripsi}</p>
                <div className="flex items-center gap-4 text-xs font-label-sm text-tertiary">
                  <span className="flex items-center gap-1">
                    <span className="material-symbols-outlined text-[14px]">calendar_today</span>
                    {formatDate(ticket.created_at)}
                  </span>
                  <span className="flex items-center gap-1">
                    <span className="material-symbols-outlined text-[14px]">tag</span>
                    Ticket #{ticket.id}
                  </span>
                </div>
              </div>
              <div className="sm:self-center">
                <button 
                  onClick={() => handleOpenTicket(ticket)}
                  className="text-primary-container hover:bg-primary-container/10 px-3 py-1.5 rounded-lg font-label-sm font-bold transition-colors"
                >
                  Detail
                </button>
              </div>
            </div>
          )) : (
            <div className="p-12 text-center flex flex-col items-center">
              <span className="material-symbols-outlined text-5xl text-outline-variant/50 mb-3">inbox</span>
              <p className="font-body-md text-on-surface-variant">Belum ada riwayat tiket bantuan.</p>
            </div>
          )}
        </div>
      </div>

      {/* Modal Detail Tiket */}
      {selectedTicket && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
          <div className="bg-surface w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
            <div className="p-6 border-b border-outline-variant/30 flex justify-between items-center bg-surface-container-lowest shrink-0">
              <h2 className="font-headline-md text-secondary flex items-center gap-2">
                <span className="material-symbols-outlined text-primary-container">support_agent</span>
                Detail Tiket #{selectedTicket.id}
              </h2>
              <button onClick={() => setSelectedTicket(null)} className="text-on-surface-variant hover:bg-surface-container-high p-2 rounded-full transition-colors">
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>
            
            <div className="flex-1 overflow-y-auto p-6 flex flex-col md:flex-row gap-6">
              {/* Kolom Kiri: Detail Tiket */}
              <div className="flex-1 flex flex-col gap-4">
              <div>
                <span className={`inline-flex px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider mb-2 ${
                  selectedTicket.status === 'open' ? 'bg-error-container text-error' :
                  selectedTicket.status === 'in_progress' ? 'bg-[#fef08a] text-[#854d0e]' :
                  'bg-[#ecfdf5] text-[#065f46]'
                }`}>
                  Status: {selectedTicket.status.replace('_', ' ')}
                </span>
                <h3 className="font-headline-sm text-secondary font-bold">{selectedTicket.subject}</h3>
                <p className="text-xs text-on-surface-variant mt-1">Dibuat pada: {formatDate(selectedTicket.created_at)}</p>
              </div>

              {selectedTicket.alamat && (
                <div className="bg-surface-container-lowest border border-outline-variant/30 p-3 rounded-lg">
                  <span className="text-xs font-bold text-secondary uppercase tracking-wider block mb-1">Alamat Pemasangan</span>
                  <p className="font-body-sm text-on-surface">{selectedTicket.alamat}</p>
                </div>
              )}

              <div className="bg-surface-container-lowest border border-outline-variant/30 p-3 rounded-lg flex-1">
                <span className="text-xs font-bold text-secondary uppercase tracking-wider block mb-1">Deskripsi Kendala</span>
                <p className="font-body-sm text-on-surface whitespace-pre-wrap">{selectedTicket.deskripsi}</p>
              </div>

              <div className="bg-primary-container/10 border border-primary-container/30 p-3 rounded-lg">
                <span className="text-[11px] font-bold text-primary-container uppercase tracking-wider block mb-2"><i className="fas fa-info-circle mr-1"></i> Penjelasan Status</span>
                <ul className="text-[11px] text-on-surface-variant space-y-1.5 list-disc pl-4 leading-relaxed">
                  <li><strong className="text-error">Open:</strong> Tiket baru saja diajukan dan sedang antri. Admin belum dapat membalas pesan pada status ini.</li>
                  <li><strong className="text-[#854d0e]">In Progress:</strong> Tiket sedang aktif ditangani. Admin/Kasir akan segera membalas pesan Anda.</li>
                  <li><strong className="text-[#065f46]">Resolved:</strong> Kendala Anda telah selesai diperbaiki dan tiket ini sudah ditutup.</li>
                </ul>
              </div>
              </div>

              {/* Kolom Kanan: Chat Diskusi */}
              <div className="flex-1 flex flex-col border-t md:border-t-0 md:border-l border-outline-variant/30 pt-4 md:pt-0 md:pl-6">
                <h3 className="font-label-md font-bold text-secondary mb-3"><i className="fas fa-comments mr-2"></i>Diskusi Tiket</h3>
                <div ref={chatContainerRef} className="bg-surface-container-lowest rounded-lg p-3 flex-1 min-h-[300px] overflow-y-auto flex flex-col gap-3 mb-3 border border-outline-variant/30">
                  {ticketChats.length > 0 ? ticketChats.map((chat) => (
                    <div key={chat.id} className={`flex flex-col max-w-[85%] ${chat.is_me ? 'self-end items-end' : 'self-start items-start'}`}>
                      <span className="text-[10px] text-on-surface-variant font-bold mb-0.5 px-1">{chat.sender_name}</span>
                      <div className={`px-3 py-2 rounded-2xl text-sm whitespace-pre-wrap ${chat.is_me ? 'bg-primary-container text-on-primary rounded-tr-sm' : 'bg-surface-variant text-on-surface-variant rounded-tl-sm'}`}>
                        {chat.message}
                      </div>
                      <span className="text-[9px] text-on-surface-variant mt-0.5 px-1">{chat.created_at}</span>
                    </div>
                  )) : (
                    <div className="text-center p-4 text-sm text-on-surface-variant">Belum ada diskusi.</div>
                  )}
                </div>
                <form onSubmit={handleSendChat} className="flex gap-2">
                  <input 
                    type="text" 
                    value={chatMessage}
                    onChange={(e) => setChatMessage(e.target.value)}
                    placeholder="Tulis pesan..."
                    className="flex-1 bg-surface-container-lowest border border-outline-variant/50 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary"
                    required
                  />
                  <button 
                    type="submit" 
                    disabled={isSendingChat}
                    className="bg-primary-container text-on-primary px-4 py-2 rounded-lg font-bold text-sm disabled:opacity-50"
                  >
                    Kirim
                  </button>
                </form>
              </div>
            </div>
            
            <div className="p-4 border-t border-outline-variant/30 bg-surface-container-lowest flex justify-end shrink-0">
              <button 
                onClick={() => setSelectedTicket(null)}
                className="bg-primary-container text-on-primary font-label-md font-bold px-6 py-2 rounded-lg hover:opacity-90 transition-opacity"
              >
                Tutup
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
