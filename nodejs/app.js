// app.js
const express = require('express');
const app = express();
const port = 3000;
const User = require('./models/User');
const authRoutes = require('./routes/auth');
const adminRoutes = require('./routes/admin');

app.use(express.json());
app.use('/auth', authRoutes);
app.use('/admin', adminRoutes);

app.get('/', (req, res) => {
  res.send('Hello World!');
});

app.listen(port, () => {
  console.log(`Example app listening at http://localhost:${port}`);
});