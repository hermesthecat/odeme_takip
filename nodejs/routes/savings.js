const express = require('express');
const router = express.Router();
const Saving = require('../models/Saving'); // Assuming you have a Saving model

router.post('/addSaving', async (req, res) => {
  try {
    const saving = await Saving.create(req.body);
    res.status(201).send({ message: 'Saving added successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error adding saving' });
  }
});

router.post('/deleteSaving', async (req, res) => {
  try {
    await Saving.destroy({
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Saving deleted successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error deleting saving' });
  }
});

router.get('/loadSavings', async (req, res) => {
  try {
    const savings = await Saving.findAll();
    res.send(savings);
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error loading savings' });
  }
});

router.post('/updateSaving', async (req, res) => {
  try {
    await Saving.update(req.body, {
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Saving updated successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error updating saving' });
  }
});

router.post('/updateFullSaving', async (req, res) => {
  try {
    await Saving.update(req.body, {
      where: {
        id: req.body.id
      }
    });
    res.send({ message: 'Saving updated successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error updating saving' });
  }
});

router.get('/getSavingsHistory', async (req, res) => {
  try {
    // Implement get savings history logic here
    res.send('Get savings history endpoint - Not implemented');
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error getting savings history' });
  }
});

module.exports = router;