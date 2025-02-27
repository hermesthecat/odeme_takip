// app.js
const express = require('express');
const app = express();
const port = 3000;
const User = require('./models/User');
const authRoutes = require('./routes/auth');
const adminRoutes = require('./routes/admin');
const borsaRoutes = require('./routes/borsa');
const currencyRoutes = require('./routes/currency');
const incomeRoutes = require('./routes/income');
const paymentsRoutes = require('./routes/payments');
const savingsRoutes = require('./routes/savings');
const summaryRoutes = require('./routes/summary');
const transferRoutes = require('./routes/transfer');

app.use(express.json());
app.use(express.static('public'));
app.use('/auth', authRoutes);
app.use('/admin', adminRoutes);
app.use('/borsa', borsaRoutes);
app.use('/currency', currencyRoutes);
app.use('/income', incomeRoutes);
app.use('/payments', paymentsRoutes);
app.use('/savings', savingsRoutes);
app.use('/summary', summaryRoutes);
app.use('/transfer', transferRoutes);

app.get('/', (req, res) => {
  res.send('Hello World!');
});

app.listen(port, () => {
  console.log(`Example app listening at http://localhost:${port}`);
});