const express = require('express');
const router = express.Router();
const Portfolio = require('../models/Portfolio'); // Assuming you have a Portfolio model

router.post('/addStock', async (req, res) => {
  try {
    const portfolio = await Portfolio.create(req.body);
    res.status(201).send({ message: 'Stock added successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error adding stock' });
  }
});

router.post('/hisseSil', async (req, res) => {
  try {
    await Portfolio.destroy({
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Stock deleted successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error deleting stock' });
  }
});

router.get('/portfoyListele', async (req, res) => {
  try {
    const portfolio = await Portfolio.findAll();
    res.send(portfolio);
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error listing portfolio' });
  }
});

router.get('/hisseAra', async (req, res) => {
  // Implement search stock logic here
  res.send('Search stock endpoint - Not implemented');
});

router.post('/hisseSat', async (req, res) => {
  try {
    await Portfolio.destroy({
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Stock sold successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error selling stock' });
  }
});

module.exports = router;