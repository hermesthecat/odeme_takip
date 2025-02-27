const { Sequelize, DataTypes } = require('sequelize');
const sequelize = new Sequelize('odeme_takip', 'root', 'root', {
  host: 'localhost',
  dialect: 'mysql'
});

const ExchangeRate = sequelize.define('ExchangeRate', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  from_currency: {
    type: DataTypes.STRING(3),
    allowNull: false
  },
  to_currency: {
    type: DataTypes.STRING(3),
    allowNull: false
  },
  rate: {
    type: DataTypes.DECIMAL(10, 4),
    allowNull: false
  },
  date: {
    type: DataTypes.DATE,
    allowNull: false
  },
  created_at: {
    type: DataTypes.DATE
  }
}, {
  tableName: 'exchange_rates',
  timestamps: false,
  underscored: true,
  indexes: [
    {
      fields: ['date'],
      name: 'idx_date'
    },
    {
      fields: ['from_currency', 'to_currency'],
      name: 'idx_currencies'
    }
  ]
});

module.exports = ExchangeRate;