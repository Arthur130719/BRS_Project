import { useState, useEffect } from 'react';

export default function PaymentModal({ isOpen, onClose, amount, invoiceId }) {
  const [banks, setBanks] = useState({
    bca: { norek: '-', an: '' },
    mandiri: { norek: '-', an: '' },
    bri: { norek: '-', an: '' },
    bni: { norek: '-', an: '' }
  });

  useEffect(() => {
    if (isOpen) {
      const token = localStorage.getItem('brs_token');
      fetch(`http://${window.location.hostname}:8080/api/pelanggan/settings`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
        .then(res => res.json())
        .then(data => {
          const parseBankInfo = (bankData) => {
            if (bankData && bankData.length > 5) {
              const parts = bankData.split(' a/n ');
              return {
                norek: parts[0] || bankData,
                an: parts[1] ? `a.n ${parts[1]}` : ''
              };
            }
            return { norek: '-', an: '' };
          };

          setBanks({
            bca: parseBankInfo(data.bank_bca),
            mandiri: parseBankInfo(data.bank_mandiri),
            bri: parseBankInfo(data.bank_bri),
            bni: parseBankInfo(data.bank_bni)
          });
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
          <div className="text-center">
            <p className="font-label-md text-on-surface-variant mb-1">Total Tagihan</p>
            <h3 className="font-headline-lg text-4xl text-on-surface font-bold">{formatRupiah(amount)}</h3>
          </div>

          <div className="bg-primary-fixed-dim/10 border border-primary-container/20 rounded-xl p-4 flex flex-col gap-3">
            <p className="font-label-sm text-primary-container font-bold uppercase tracking-wider text-xs">Instruksi Transfer</p>
            <p className="font-body-md text-sm text-on-surface-variant">Silakan transfer sesuai nominal di atas ke salah satu rekening berikut:</p>
            
            <div className="flex flex-col gap-2">
              {banks.bca.norek !== '-' && (
                <div className="bg-surface-bright rounded-lg p-3 flex justify-between items-center border border-outline-variant/50">
                  <div>
                    <p className="font-label-md text-secondary font-bold text-lg">{banks.bca.norek}</p>
                    <p className="font-body-md text-xs text-on-surface-variant">{banks.bca.an}</p>
                  </div>
                  <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" alt="BCA" className="h-6 object-contain" />
                </div>
              )}
              
              {banks.mandiri.norek !== '-' && (
                <div className="bg-surface-bright rounded-lg p-3 flex justify-between items-center border border-outline-variant/50">
                  <div>
                    <p className="font-label-md text-secondary font-bold text-lg">{banks.mandiri.norek}</p>
                    <p className="font-body-md text-xs text-on-surface-variant">{banks.mandiri.an}</p>
                  </div>
                  <img src="https://www.bankmandiri.co.id/image/layout_set_logo?img_id=31567&t=1690947703378" alt="Mandiri" className="h-6 object-contain" />
                </div>
              )}
              
              {banks.bri.norek !== '-' && (
                <div className="bg-surface-bright rounded-lg p-3 flex justify-between items-center border border-outline-variant/50">
                  <div>
                    <p className="font-label-md text-secondary font-bold text-lg">{banks.bri.norek}</p>
                    <p className="font-body-md text-xs text-on-surface-variant">{banks.bri.an}</p>
                  </div>
                  <img src="https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_2020.svg" alt="BRI" className="h-6 object-contain" />
                </div>
              )}
              
              {banks.bni.norek !== '-' && (
                <div className="bg-surface-bright rounded-lg p-3 flex justify-between items-center border border-outline-variant/50">
                  <div>
                    <p className="font-label-md text-secondary font-bold text-lg">{banks.bni.norek}</p>
                    <p className="font-body-md text-xs text-on-surface-variant">{banks.bni.an}</p>
                  </div>
                  <img src="https://upload.wikimedia.org/wikipedia/commons/f/f0/Bank_Negara_Indonesia_logo_%282004%29.svg" alt="BNI" className="h-6 object-contain" />
                </div>
              )}
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
        </div>
      </div>
    </div>
  );
}
