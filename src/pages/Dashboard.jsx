import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import PaymentModal from '../components/PaymentModal';
export default function Dashboard() {
  const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
  const [paymentAmount, setPaymentAmount] = useState(0);

  const handlePayClick = (amount = 150000) => {
    setPaymentAmount(amount);
    setIsPaymentModalOpen(true);
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
