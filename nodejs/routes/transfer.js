const express = require('express');
const router = express.Router();
const Payment = require('../models/Payment');

router.post('/transferUnpaidPayments', async (req, res) => {
  try {
    const month = req.body.month;
    const year = req.body.year;
    const nextMonth = month === 12 ? 1 : month + 1;
    const nextYear = month === 12 ? year + 1 : year;

    // Find unpaid payments for the current month and year
    const unpaidPayments = await Payment.findAll({
      where: {
        month: month,
        year: year,
        paid: false
      }
    });

    // Transfer unpaid payments to the next month
    for (const payment of unpaidPayments) {
      await Payment.create({
        name: payment.name + ' (Transferred)',
        amount: payment.amount,
        currency: payment.currency,
        date: new Date(nextYear, nextMonth - 1, payment.date.getDate()), // Adjust date to next month
        frequency: payment.frequency,
        paid: false,
        recurring: payment.recurring,
        month: nextMonth,
        year: nextYear
      });
    }

    res.send({ message: 'Unpaid payments transferred successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error transferring unpaid payments' });
  }
});

module.exports = router;