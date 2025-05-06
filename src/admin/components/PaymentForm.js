import { useState } from '@wordpress/element';
import TextControlInput from './inputs/TextControlInput';

const PaymentForm = () => {
  const [paymentDetails, setPaymentDetails] = useState({
    cardNumber: '',
    expiryDate: '',
    cvv: '',
    nameoncard: '',
  });

  const handleChange = (field, value) => {
    setPaymentDetails({ ...paymentDetails, [field]: value });
  };

  return (
    <div className='payment-form-wrap'>
      <div className='card-form-row'>
        <div className='card-form-field'>
          <label>Card Number</label>
          <TextControlInput
            label='Card Number'
            type='text'
            name='cardNumber'
            placeholder='4444 5555 6666 7777'
            value={paymentDetails.cardNumber}
            onChange={handleChange}
          />
        </div>
      </div>
      <div className='card-form-row'>
        <div className='card-form-field'>
          <label>Expire Date</label>
          <TextControlInput
            label='Expiry Date'
            type='text'
            name='expiryDate'
            placeholder='MM/YY'
            value={paymentDetails.expiryDate}
            onChange={handleChange}
          />
        </div>
        <div className='card-form-field'>
          <label>CVC</label>
          <TextControlInput
            label='CVV'
            type='text'
            name='cvv'
            placeholder='123'
            value={paymentDetails.cvv}
            onChange={handleChange}
          />
        </div>
      </div>
      <div className='card-form-row'>
        <div className='card-form-field'>
          <label>Name on Card</label>
          <TextControlInput
            label='Name on Card'
            type='text'
            name='nameoncard'
            placeholder='John Doe'
            value={paymentDetails.nameoncard}
            onChange={handleChange}
          />
        </div>
      </div>
    </div>
  );
};

export default PaymentForm;
