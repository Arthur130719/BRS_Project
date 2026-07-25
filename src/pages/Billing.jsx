import { useState, useEffect } from 'react';
import { useOutletContext } from 'react-router-dom';

export default function Billing() {
  const { onPayClick } = useOutletContext();
  const [invoices, setInvoices] = useState([]);
  const [loading, setLoading] = useState(true);
  const [downloadingId, setDownloadingId] = useState(null);

  useEffect(() => {
    const token = sessionStorage.getItem('brs_token');
    
    fetch(`http://${window.location.hostname}:8000/api/pelanggan/invoices`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
      .then(res => res.json())
      .then(data => {
        setInvoices(data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Error fetching invoices:', err);
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

  const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric', month: 'long', day: 'numeric'
    });
  };

  const handleDownload = async (e, id, no_invoice) => {
    e.preventDefault();
    e.stopPropagation();
    if (downloadingId) return; // Prevent multiple clicks
    
    try {
      setDownloadingId(id);
      const token = sessionStorage.getItem('brs_token');
      const response = await fetch(`http://${window.location.hostname}:8000/api/pelanggan/invoices/${id}/download`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `invoice-${no_invoice}.pdf`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      setTimeout(() => window.URL.revokeObjectURL(url), 1000);
    } catch (err) {
      console.error('Download error:', err);
      alert(`Terjadi kesalahan saat mengunduh invoice: ${err.message}`);
    } finally {
      setDownloadingId(null);
    }
  };

  if (loading) {
    return <div className="h-96 flex items-center justify-center">
        <p className="font-headline-md text-secondary animate-pulse">Memuat Riwayat Tagihan...</p>
    </div>;
  }

  return (
    <div className="flex flex-col gap-6">
      <header className="mb-4">
        <h1 className="font-headline-lg text-secondary mb-2 flex items-center gap-2">
          <span className="material-symbols-outlined text-3xl">receipt_long</span>
          Billing History
        </h1>
        <p className="font-body-md text-on-surface-variant">Manage your invoices and payment history.</p>
      </header>

      <div className="glass-panel rounded-xl overflow-hidden border border-outline-variant/30">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-surface-container-low border-b border-outline-variant/30">
                <th className="font-label-md text-secondary font-bold p-4">No Invoice</th>
                <th className="font-label-md text-secondary font-bold p-4">Periode</th>
                <th className="font-label-md text-secondary font-bold p-4">Jatuh Tempo</th>
                <th className="font-label-md text-secondary font-bold p-4">Nominal</th>
                <th className="font-label-md text-secondary font-bold p-4">Status</th>
                <th className="font-label-md text-secondary font-bold p-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-outline-variant/20">
              {invoices.length > 0 ? invoices.map((inv) => (
                <tr key={inv.id} className="hover:bg-surface-container-lowest/50 transition-colors">
                  <td className="font-body-md text-on-surface p-4">{inv.no_invoice}</td>
                  <td className="font-body-md text-on-surface p-4">{inv.periode}</td>
                  <td className="font-body-md text-on-surface p-4">{formatDate(inv.tgl_jatuh_tempo)}</td>
                  <td className="font-body-md text-on-surface font-bold p-4">{formatRupiah(inv.nominal)}</td>
                  <td className="p-4">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full font-label-sm font-bold border ${
                      inv.status === 'paid' 
                        ? 'bg-[#ecfdf5] text-[#065f46] border-[#a7f3d0]' 
                        : 'bg-[#fef2f2] text-[#991b1b] border-[#fecaca]'
                    }`}>
                      {inv.status === 'paid' ? 'Lunas' : 'Belum Lunas'}
                    </span>
                  </td>
                  <td className="p-4">
                    <div className="flex items-center justify-center gap-2">
                      {inv.status !== 'paid' ? (
                        <button 
                          onClick={() => onPayClick(inv.nominal)}
                          className="bg-primary-container text-on-primary font-label-sm font-bold px-4 py-1.5 rounded-lg hover:opacity-90 transition-opacity whitespace-nowrap"
                        >
                          Bayar Sekarang
                        </button>
                      ) : (
                        <span className="text-on-surface-variant font-label-sm flex items-center gap-1 whitespace-nowrap px-4 py-1.5">
                          <span className="material-symbols-outlined text-sm text-[#10b981]">check_circle</span> Selesai
                        </span>
                      )}
                      <button
                        onClick={(e) => handleDownload(e, inv.id, inv.no_invoice)}
                        title="Unduh Invoice"
                        disabled={downloadingId === inv.id}
                        className={`bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest hover:text-primary transition-colors p-1.5 rounded-lg flex items-center justify-center ${
                          downloadingId === inv.id ? 'opacity-50 cursor-not-allowed' : ''
                        }`}
                      >
                        {downloadingId === inv.id ? (
                          <span className="material-symbols-outlined text-[18px] animate-spin">refresh</span>
                        ) : (
                          <span className="material-symbols-outlined text-[18px]">download</span>
                        )}
                      </button>
                    </div>
                  </td>
                </tr>
              )) : (
                <tr>
                  <td colSpan="6" className="p-8 text-center text-on-surface-variant font-body-md">
                    Tidak ada riwayat tagihan.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
