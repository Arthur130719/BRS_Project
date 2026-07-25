import { useState, useCallback } from 'react';
import Cropper from 'react-easy-crop';
import 'react-easy-crop/react-easy-crop.css';
import getCroppedImg from '../utils/cropImage';

export default function CropModal({ imageSrc, onCropComplete, onCancel, aspectRatio = 1 }) {
  const [crop, setCrop] = useState({ x: 0, y: 0 });
  const [zoom, setZoom] = useState(1);
  const [croppedAreaPixels, setCroppedAreaPixels] = useState(null);
  const [isProcessing, setIsProcessing] = useState(false);

  const onCropCompleteHandler = useCallback((croppedArea, croppedAreaPixels) => {
    setCroppedAreaPixels(croppedAreaPixels);
  }, []);

  const handleSave = async () => {
    try {
      setIsProcessing(true);
      const croppedBlob = await getCroppedImg(imageSrc, croppedAreaPixels);
      onCropComplete(croppedBlob);
    } catch (e) {
      console.error(e);
      alert('Gagal memproses gambar');
    } finally {
      setIsProcessing(false);
    }
  };

  if (!imageSrc) return null;

  return (
    <div className="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
      <div className="bg-surface rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl flex flex-col">
        <div className="p-4 border-b border-outline-variant/30 flex justify-between items-center">
          <h3 className="font-headline-sm text-secondary">Sesuaikan Gambar</h3>
          <button onClick={onCancel} className="text-on-surface-variant hover:text-error transition-colors">
            <span className="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div className="relative w-full h-80 sm:h-96 bg-black">
          <Cropper
            image={imageSrc}
            crop={crop}
            zoom={zoom}
            aspect={aspectRatio}
            onCropChange={setCrop}
            onCropComplete={onCropCompleteHandler}
            onZoomChange={setZoom}
          />
        </div>
        
        <div className="p-4 flex flex-col gap-4">
          <div className="flex items-center gap-4">
            <span className="material-symbols-outlined text-on-surface-variant text-sm">zoom_out</span>
            <input
              type="range"
              value={zoom}
              min={1}
              max={3}
              step={0.1}
              aria-labelledby="Zoom"
              onChange={(e) => setZoom(Number(e.target.value))}
              className="w-full accent-primary"
            />
            <span className="material-symbols-outlined text-on-surface-variant text-sm">zoom_in</span>
          </div>
          
          <div className="flex justify-end gap-2">
            <button 
              onClick={onCancel}
              className="px-4 py-2 font-label-md text-secondary hover:bg-surface-container rounded-md transition-colors"
            >
              Batal
            </button>
            <button 
              onClick={handleSave}
              disabled={isProcessing}
              className="px-4 py-2 font-label-md bg-primary text-on-primary rounded-md hover:bg-primary/90 transition-colors disabled:opacity-50"
            >
              {isProcessing ? 'Memproses...' : 'Simpan'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
