const express = require('express');
const router = express.Router();
const Payment = require('../models/Payment'); // Assuming you have a Payment model

router.post('/addPayment', async (req, res) => {
  try {
    const payment = await Payment.create(req.body);
    res.status(201).send({ message: 'Payment added successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error adding payment' });
  }
});

router.post('/deletePayment', async (req, res) => {
  try {
    await Payment.destroy({
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Payment deleted successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error deleting payment' });
  }
});

router.get('/loadPayments', async (req, res) => {
  try {
    const payments = await Payment.findAll({
      where: {
        month: req.query.month,
        year: req.query.year
      }
    });
    res.send(payments);
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error loading payments' });
  }
});

router.get('/loadRecurringPayments', async (req, res) => {
  try {
    const payments = await Payment.findAll({
      where: {
        recurring: true
      }
    });
    res.send(payments);
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error loading recurring payments' });
  }
});

router.post('/markPaymentPaid', async (req, res) => {
  try {
    await Payment.update(
      { paid: true },
      {
        where: {
          id: req.body.id
        }
      }
    );
    res.send({ message: 'Payment marked as paid successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error marking payment as paid' });
  }
});

router.post('/updatePayment', async (req, res) => {
  try {
    await Payment.update(req.body, {
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Payment updated successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error updating payment' });
  }
});

module.exports = router;