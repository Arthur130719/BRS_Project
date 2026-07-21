import { useState, useEffect } from 'react';

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
  const [showForm, setShowForm] = useState(false);
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [isGettingLocation, setIsGettingLocation] = useState(false);

  const fetchTickets = () => {
    const token = localStorage.getItem('brs_token');
    fetch(`http://${window.location.hostname}:8080/api/pelanggan/tickets`, {
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

  const handleGetLocation = () => {
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
        alert('Gagal mengambil lokasi. Pastikan Anda telah memberikan izin akses lokasi pada browser.');
        setIsGettingLocation(false);
      },
      { enableHighAccuracy: true }
    );
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    const token = localStorage.getItem('brs_token');

    fetch(`http://${window.location.hostname}:8080/api/pelanggan/tickets`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ subject, deskripsi, alamat, latitude, longitude })
    })
      .then(res => res.json())
      .then(data => {
        setIsSubmitting(false);
        setSubject('');
        setAlamat('');
        setDeskripsi('');
        setLatitude('');
        setLongitude('');
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
            Support Center
          </h1>
          <p className="font-body-md text-on-surface-variant">We are here to help. Create a ticket if you face any issues.</p>
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
            <form onSubmit={handleSubmit} className="flex flex-col gap-5">
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
                placeholder="Jelaskan secara detail kendala yang Anda alami..."
                rows="4"
                className="w-full px-4 py-2 bg-surface-bright border border-outline-variant/50 rounded-lg focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container font-body-md text-on-surface transition-all resize-none"
              ></textarea>
            </div>
            
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
              {latitude && longitude && (
                <p className="text-xs text-[#10b981] flex items-center justify-center gap-1">
                  <span className="material-symbols-outlined text-sm">check_circle</span>
                  Titik lokasi berhasil dicatat
                </p>
              )}
            </div>

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
                  onClick={() => setSelectedTicket(ticket)}
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
          <div className="bg-surface w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 animate-in fade-in zoom-in-95 duration-200">
            <div className="p-6 border-b border-outline-variant/30 flex justify-between items-center bg-surface-container-lowest">
              <h2 className="font-headline-md text-secondary flex items-center gap-2">
                <span className="material-symbols-outlined text-primary-container">support_agent</span>
                Detail Tiket #{selectedTicket.id}
              </h2>
              <button onClick={() => setSelectedTicket(null)} className="text-on-surface-variant hover:bg-surface-container-high p-2 rounded-full transition-colors">
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>
            
            <div className="p-6 flex flex-col gap-4">
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
            </div>
            
            <div className="p-4 border-t border-outline-variant/30 bg-surface-container-lowest flex justify-end">
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
