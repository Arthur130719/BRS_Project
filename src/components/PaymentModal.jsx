import { useState, useEffect } from 'react';

export default function PaymentModal({ isOpen, onClose, amount, invoiceId }) {
  const [banks, setBanks] = useState([]);

  useEffect(() => {
    if (isOpen) {
      const token = sessionStorage.getItem('brs_token');
      fetch(`http://${window.location.hostname}:8000/api/pelanggan/settings`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
        .then(res => res.json())
        .then(data => {
          if (data.rekening_banks && Array.isArray(data.rekening_banks)) {
            setBanks(data.rekening_banks);
          } else {
            setBanks([]);
          }
        })
        .catch(err => console.error('Error fetching settings:', err));
    }
  }, [isOpen]);

  if (!isOpen) return null;

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(number);
  };

  const waMessage = `Halo Admin BRS, saya ingin konfirmasi pembayaran untuk tagihan ${invoiceId ? `dengan nomor Invoice #${invoiceId}` : 'internet saya'} sebesar ${formatRupiah(amount)}. Berikut bukti transfernya.`;
  const waUrl = `https://wa.me/6287761205991?text=${encodeURIComponent(waMessage)}`;

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div className="bg-surface w-full max-w-md max-h-[90vh] flex flex-col rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 animate-in fade-in zoom-in-95 duration-200">
        <div className="p-6 border-b border-outline-variant/30 flex-shrink-0 flex justify-between items-center bg-surface-container-lowest">
          <h2 className="font-headline-md text-headline-md text-secondary flex items-center gap-2">
            <span className="material-symbols-outlined text-primary-container">payments</span>
            Pembayaran Tagihan
          </h2>
          <button onClick={onClose} className="text-on-surface-variant hover:bg-surface-container-high p-2 rounded-full transition-colors">
            <span className="material-symbols-outlined">close</span>
          </button>
        </div>

        <div className="p-6 flex flex-col gap-6 overflow-y-auto">
          {amount > 0 ? (
            <>
              <div className="text-center">
                <p className="font-label-md text-on-surface-variant mb-1">Total Tagihan</p>
                <h3 className="font-headline-lg text-4xl text-on-surface font-bold">{formatRupiah(amount)}</h3>
              </div>

              <div className="bg-primary-fixed-dim/10 border border-primary-container/20 rounded-xl p-4 flex flex-col gap-3">
                <p className="font-label-sm text-primary-container font-bold uppercase tracking-wider text-xs">Instruksi Transfer</p>
                <p className="font-body-md text-sm text-on-surface-variant">Silakan transfer sesuai nominal di atas ke salah satu rekening berikut:</p>
                
                <div className="flex flex-col gap-2">
                  {banks.length === 0 && <p className="font-label-sm text-center py-2 text-on-surface-variant">Belum ada rekening dikonfigurasi.</p>}
                  
                  {banks.map((bank, index) => {
                    let logoUrl = null;
                    const bname = (bank.bank || '').toLowerCase();
                    if(bname.includes('bca')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg';
                    else if(bname.includes('mandiri')) logoUrl = 'https://www.bankmandiri.co.id/image/layout_set_logo?img_id=31567&t=1690947703378';
                    else if(bname.includes('bri')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_2020.svg';
                    else if(bname.includes('bni')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/f/f0/Bank_Negara_Indonesia_logo_%282004%29.svg';
                    else if(bname.includes('bsi') || bname.includes('syariah')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia.svg';
                    else if(bname.includes('dana')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg';
                    else if(bname.includes('ovo')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_OVO_Purpel.svg';
                    else if(bname.includes('gopay') || bname.includes('go-pay')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg';
                    
                    return (
                      <div key={index} className="bg-surface-bright rounded-lg p-3 flex justify-between items-center border border-outline-variant/50">
                        <div>
                          <p className="font-label-sm font-bold text-primary mb-1 uppercase tracking-wider">{bank.bank}</p>
                          <p className="font-headline-sm text-secondary font-bold text-lg leading-tight">{bank.norek}</p>
                          <p className="font-body-md text-xs text-on-surface-variant mt-1">a.n {bank.an}</p>
                        </div>
                        {logoUrl ? (
                          <img src={logoUrl} alt={bank.bank} className="h-6 object-contain" />
                        ) : (
                          <div className="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant">
                            <span className="material-symbols-outlined">account_balance</span>
                          </div>
                        )}
                      </div>
                    );
                  })}
                </div>
              </div>

              <div className="bg-surface-container-high rounded-xl p-4 border border-outline-variant/30">
                <p className="font-label-sm font-bold text-secondary mb-2 flex items-center gap-1">
                  <span className="material-symbols-outlined text-sm">info</span> Konfirmasi Pembayaran
                </p>
                <p className="font-body-md text-sm text-on-surface-variant mb-4">
                  Setelah melakukan transfer, harap kirimkan bukti transfer melalui WhatsApp agar sistem dapat segera memproses perpanjangan internet Anda.
                </p>
                <a 
                  href={waUrl}
                  target="_blank" 
                  rel="noreferrer"
                  className="w-full bg-[#25D366] hover:bg-[#1ebd5a] text-white font-label-md font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-md shadow-[#25D366]/20"
                >
                  <span className="material-symbols-outlined">chat</span>
                  Konfirmasi via WhatsApp
                </a>
              </div>
            </>
          ) : (
            <div className="text-center py-8">
              <div className="w-20 h-20 bg-[#ecfdf5] text-[#10b981] rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-[#a7f3d0]">
                <span className="material-symbols-outlined text-4xl font-bold" style={{ fontVariationSettings: "'FILL' 1" }}>check_circle</span>
              </div>
              <h3 className="font-headline-md text-secondary font-bold mb-2">Tidak Ada Tagihan</h3>
              <p className="font-body-md text-on-surface-variant">
                Saat ini Anda tidak memiliki tagihan yang harus dibayarkan.
              </p>
              <button onClick={onClose} className="mt-6 px-6 py-2 bg-surface-container-high text-on-surface hover:bg-surface-container-highest transition-colors font-label-md font-bold rounded-full">
                Tutup
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
