const express = require('express');
const router = express.Router();
const User = require('../models/User');
const bcrypt = require('bcrypt');

router.post('/register', async (req, res) => {
  try {
    const hashedPassword = await bcrypt.hash(req.body.password, 10);
    const user = await User.create({
      username: req.body.username,
      password: hashedPassword
    });
    res.status(201).send({ message: 'User created successfully' });
  } catch (error) {
    console.error(error);
    res.status(500).send({ message: 'Error creating user' });
  }
});

router.post('/login', async (req, res) => {
  // Implement login logic here
  res.send('Login endpoint - Not implemented');
});

router.post('/logout', async (req, res) => {
  // Implement logout logic here
  res.send('Logout endpoint - Not implemented');
});

module.exports = router;