const express = require('express');
const router = express.Router();
const Income = require('../models/Income');
const Payment = require('../models/Payment');

router.get('/loadSummary', async (req, res) => {
  try {
    const month = req.query.month;
    const year = req.query.year;

    const totalIncome = await Income.sum('amount', {
      where: {
        month: month,
        year: year,
        received: true
      }
    });

    const totalPayments = await Payment.sum('amount', {
      where: {
        month: month,
        year: year,
        paid: true
      }
    });

    const summary = {
      totalIncome: totalIncome || 0,
      totalPayments: totalPayments || 0,
      netBalance: (totalIncome || 0) - (totalPayments || 0)
    };

    res.send(summary);
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error loading summary' });
  }
});

module.exports = router;