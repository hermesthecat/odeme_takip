const express = require('express');
const router = express.Router();
const Income = require('../models/Income'); // Assuming you have an Income model

router.post('/addIncome', async (req, res) => {
  try {
    const income = await Income.create(req.body);
    res.status(201).send({ message: 'Income added successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error adding income' });
  }
});

router.post('/deleteIncome', async (req, res) => {
  try {
    await Income.destroy({
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Income deleted successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error deleting income' });
  }
});

router.get('/loadIncomes', async (req, res) => {
  try {
    const incomes = await Income.findAll({
      where: {
        month: req.query.month,
        year: req.query.year
      }
    });
    res.send(incomes);
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error loading incomes' });
  }
});

router.post('/markIncomeReceived', async (req, res) => {
  try {
    await Income.update(
      { received: true },
      {
        where: {
          id: req.body.id
        }
      }
    );
    res.send({ message: 'Income marked as received successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error marking income as received' });
  }
});

router.post('/updateIncome', async (req, res) => {
  try {
    await Income.update(req.body, {
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Income updated successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error updating income' });
  }
});

module.exports = router;