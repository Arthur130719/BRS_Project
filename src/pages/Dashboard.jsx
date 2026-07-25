import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import PaymentModal from '../components/PaymentModal';
export default function Dashboard() {
  const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
  const [paymentAmount, setPaymentAmount] = useState(0);

  const handlePayClick = async (amount = null) => {
    if (amount !== null) {
      setPaymentAmount(amount);
      setIsPaymentModalOpen(true);
    } else {
      try {
        const token = sessionStorage.getItem('brs_token');
        const res = await fetch(`http://${window.location.hostname}:8000/api/pelanggan/dashboard`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
        const data = await res.json();
        
        if (data.tagihan && data.tagihan.status !== 'Lunas') {
          setPaymentAmount(data.tagihan.nominal);
        } else {
          setPaymentAmount(0);
        }
        setIsPaymentModalOpen(true);
      } catch (err) {
        console.error('Error fetching dashboard tagihan:', err);
        setPaymentAmount(0);
        setIsPaymentModalOpen(true);
      }
    }
  };

  return (
    <>
      <DashboardLayout onPayClick={() => handlePayClick()}>
        <Outlet context={{ onPayClick: handlePayClick }} />
      </DashboardLayout>
      
      <PaymentModal 
        isOpen={isPaymentModalOpen} 
        onClose={() => setIsPaymentModalOpen(false)}
        amount={paymentAmount} 
      />
    </>
  );
}
