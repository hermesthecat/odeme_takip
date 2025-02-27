const express = require('express');
const router = express.Router();
const User = require('../models/User');

router.get('/users', async (req, res) => {
  try {
    const users = await User.findAll();
    res.send(users);
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error getting users' });
  }
});

router.post('/users', async (req, res) => {
  try {
    const user = await User.create(req.body);
    res.status(201).send({ message: 'User created successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error creating user' });
  }
});

router.put('/users/:id', async (req, res) => {
  try {
    const user = await User.update(req.body, {
      where: {
        id: req.params.id
      }
    });
    res.send({ message: 'User updated successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error updating user' });
  }
});

router.delete('/users/:id', async (req, res) => {
  try {
    const user = await User.destroy({
      where: {
        id: req.params.id
      }
    });
    res.send({ message: 'User deleted successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error deleting user' });
  }
});

module.exports = router;