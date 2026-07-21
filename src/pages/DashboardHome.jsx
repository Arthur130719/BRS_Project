import { useState, useEffect } from 'react';
import { useNavigate, useOutletContext } from 'react-router-dom';

export default function DashboardHome() {
  const navigate = useNavigate();
  const { onPayClick } = useOutletContext(); // Pass function from layout
  
  const [data, setData] = useState({
    pelanggan: null,
    paket: null,
    tagihan: null,
    tiket_open: 0,
    network_health: {
      ping: 12,
      latency: 15,
      modem_rx: -18,
      modem_tx: 2.5
    }
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('brs_token');
    
    fetch(`http://${window.location.hostname}:8080/api/pelanggan/dashboard`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
      .then(res => res.json())
      .then(resData => {
        setData(resData);
        setLoading(false);
      })
      .catch(err => {
        console.error('Error fetching dashboard data:', err);
        setLoading(false);
      });
  }, []);

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(number);
  };

  if (loading) {
    return <div className="h-96 flex items-center justify-center">
        <p className="font-headline-md text-secondary animate-pulse">Memuat Data...</p>
    </div>;
  }

  const pelanggan = data.pelanggan || { nama: 'Guest User', username_pppoe: 'brs-guest', status: 'Tidak Aktif' };
  const paket = data.paket || { nama_paket: 'Tidak ada paket', kecepatan: 0 };
  const tagihan = data.tagihan || { nominal: 0, status: 'Lunas', sisa_hari: 0 };
  const statusAktif = pelanggan.status === 'Aktif';

  return (
    <>
      {/* Welcome Header */}
      <header className="mb-md">
        <h1 className="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-secondary mb-xs">
          Welcome back, {pelanggan.nama}
        </h1>
        <div className="flex flex-wrap items-center gap-sm font-label-md text-label-md text-on-surface-variant">
          <span className="flex items-center gap-xs bg-surface-container-high px-3 py-1 rounded-full border border-outline-variant/50">
            <span className="material-symbols-outlined text-[16px]">badge</span> ID: {pelanggan.username_pppoe}
          </span>
          <span className={`flex items-center gap-xs px-3 py-1 rounded-full border ${statusAktif ? 'bg-[#ecfdf5] text-[#065f46] border-[#a7f3d0]' : 'bg-[#fef2f2] text-[#991b1b] border-[#fecaca]'}`}>
            {statusAktif && <div className="w-2 h-2 rounded-full bg-[#10b981] pulse-dot"></div>}
            Connection: {pelanggan.status}
          </span>
        </div>
      </header>

      {/* Bento Grid Layout */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-gutter">
        
        {/* Quick Stat: Speed */}
        <div className="glass-panel rounded-xl p-md border-t-4 border-primary-container flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300">
          <div className="flex justify-between items-start mb-md">
            <div>
              <p className="font-label-md text-label-md text-on-surface-variant mb-xs">Current Plan</p>
              <h2 className="font-headline-md text-headline-md text-secondary">{paket.nama_paket}</h2>
            </div>
            <div className="w-10 h-10 rounded-full bg-secondary-container/30 flex items-center justify-center text-secondary">
              <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>speed</span>
            </div>
          </div>
          <div>
            <div className="flex items-baseline gap-xs">
              <span className="font-headline-lg text-headline-lg text-primary-container">{paket.kecepatan}</span>
              <span className="font-body-md text-body-md text-on-surface-variant font-medium">Mbps</span>
            </div>
            <p className="font-label-sm text-label-sm text-tertiary mt-2">Symmetric Download/Upload</p>
          </div>
        </div>

        {/* Quick Stat: Billing */}
        <div className="glass-panel rounded-xl p-md flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300">
          <div className="flex justify-between items-start mb-md">
            <div>
              <p className="font-label-md text-label-md text-on-surface-variant mb-xs">Next Invoice</p>
              <h2 className={`font-headline-md text-headline-md ${tagihan.status === 'Lunas' ? 'text-secondary' : 'text-error'}`}>
                {tagihan.status === 'Lunas' ? 'All Paid' : `Due in ${tagihan.sisa_hari} Days`}
              </h2>
            </div>
            <div className={`w-10 h-10 rounded-full flex items-center justify-center ${tagihan.status === 'Lunas' ? 'bg-secondary-container/30 text-secondary' : 'bg-error-container/50 text-error'}`}>
              <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>account_balance_wallet</span>
            </div>
          </div>
          <div>
            <div className="flex items-baseline gap-xs">
              <span className="font-headline-lg text-headline-lg text-on-surface">{formatRupiah(tagihan.nominal)}</span>
            </div>
            {tagihan.status !== 'Lunas' && (
              <button onClick={() => onPayClick(tagihan.nominal)} className="mt-sm w-full py-2 bg-primary-container text-on-primary font-label-md text-label-md rounded border border-primary-container hover:bg-transparent hover:text-primary-container transition-colors duration-200">
                Pay Now
              </button>
            )}
          </div>
        </div>

        {/* Quick Stat: Support */}
        <div className="glass-panel rounded-xl p-md flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cursor-pointer" onClick={() => navigate('/dashboard/support')}>
          <div className="flex justify-between items-start mb-md">
            <div>
              <p className="font-label-md text-label-md text-on-surface-variant mb-xs">Support Tickets</p>
              <h2 className="font-headline-md text-headline-md text-secondary">
                {data.tiket_open > 0 ? 'Action Needed' : 'All Clear'}
              </h2>
            </div>
            <div className="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant">
              <span className="material-symbols-outlined" style={{ fontVariationSettings: "'FILL' 1" }}>forum</span>
            </div>
          </div>
          <div>
            <div className="flex items-baseline gap-xs">
              <span className="font-headline-lg text-headline-lg text-on-surface">{data.tiket_open}</span>
              <span className="font-body-md text-body-md text-on-surface-variant font-medium">Open Tickets</span>
            </div>
            <p className="font-label-sm text-label-sm text-tertiary mt-2">Need help? We are online.</p>
          </div>
        </div>

        {/* Network Health Widget */}
        <div className="glass-panel rounded-xl p-md md:col-span-2 flex flex-col">
          <div className="flex items-center gap-sm mb-md border-b border-outline-variant/30 pb-sm">
            <span className="material-symbols-outlined text-secondary" style={{ fontVariationSettings: "'FILL' 1" }}>health_and_safety</span>
            <h3 className="font-headline-md text-headline-md text-secondary">Network Health</h3>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-sm flex-grow items-center">
            <div className="flex flex-col items-center justify-center p-sm bg-surface-container-lowest rounded-lg border border-outline-variant/50">
              <span className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Ping</span>
              <div className="flex items-baseline gap-xs">
                <span className="font-headline-md text-headline-md text-primary-container">{data.network_health.ping}</span>
                <span className="font-label-sm text-label-sm">ms</span>
              </div>
            </div>
            <div className="flex flex-col items-center justify-center p-sm bg-surface-container-lowest rounded-lg border border-outline-variant/50">
              <span className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Latency</span>
              <div className="flex items-baseline gap-xs">
                <span className="font-headline-md text-headline-md text-secondary">{data.network_health.latency}</span>
                <span className="font-label-sm text-label-sm">ms</span>
              </div>
            </div>
            <div className="flex flex-col items-center justify-center p-sm bg-surface-container-lowest rounded-lg border border-outline-variant/50">
              <span className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Modem RX</span>
              <div className="flex items-baseline gap-xs">
                <span className="font-headline-md text-headline-md text-on-surface">{data.network_health.modem_rx}</span>
                <span className="font-label-sm text-label-sm">dBm</span>
              </div>
            </div>
            <div className="flex flex-col items-center justify-center p-sm bg-surface-container-lowest rounded-lg border border-outline-variant/50">
              <span className="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Modem TX</span>
              <div className="flex items-baseline gap-xs">
                <span className="font-headline-md text-headline-md text-on-surface">{data.network_health.modem_tx}</span>
                <span className="font-label-sm text-label-sm">dBm</span>
              </div>
            </div>
          </div>
        </div>

        {/* Decorative Image */}
        <div className="glass-panel rounded-xl overflow-hidden relative min-h-[200px]">
          <div className="absolute inset-0 bg-cover bg-center" style={{ backgroundImage: "url('https://lh3.googleusercontent.com/aida-public/AB6AXuARm3VaXDgRJs2B_1sKCKtT5-Y2yjJfMldaw_Y9oEMdQTEZs8BwOH5Cb4GkVO-lxDxAOsyvzmniXbA40b8Qyho2adAw2qgNQS6Q49FT4Ca0oxswDn2_ei778FVoghUYHSv04fZMZ75_dF2KIF0awYFCvBJtwYTW7ROleXxwODIPb2STpsSmBrDzfg42KSEHFvXbLcJLgfRqNmpjPQ2R-D5Ho0LLCgJ8bVM0plTPgx9s_u76T5CRhwMsMpsxvBnOwyyDQb4PT0V75ek')" }}></div>
          <div className="absolute inset-0 bg-gradient-to-t from-secondary/90 to-transparent flex flex-col justify-end p-md">
            <h4 className="font-headline-md text-headline-md text-on-primary mb-xs">Upgrade to 200Mbps</h4>
            <p className="font-label-sm text-label-sm text-on-primary/80 mb-sm">Experience seamless 4K streaming for the whole family.</p>
            <button className="text-left font-label-md text-label-md text-primary-fixed-dim hover:text-white transition-colors flex items-center gap-xs">
              View Offers <span className="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
          </div>
        </div>
      </div>
    </>
  );
}
