const { Sequelize, DataTypes } = require('sequelize');
const sequelize = new Sequelize('odeme_takip', 'root', 'root', {
  host: 'localhost',
  dialect: 'mysql'
});

const Payment = sequelize.define('Payment', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  user_id: {
    type: DataTypes.INTEGER,
    allowNull: false,
    references: {
      model: 'users',
      key: 'id'
    }
  },
  parent_id: {
    type: DataTypes.INTEGER,
    allowNull: true,
    references: {
      model: 'payments',
      key: 'id'
    }
  },
  name: {
    type: DataTypes.STRING(100),
    allowNull: false
  },
  amount: {
    type: DataTypes.DECIMAL(10, 2),
    allowNull: false
  },
  currency: {
    type: DataTypes.STRING(3),
    defaultValue: 'TRY'
  },
  first_date: {
    type: DataTypes.DATE,
    allowNull: false
  },
  frequency: {
    type: DataTypes.STRING(20),
    allowNull: false
  },
  status: {
    type: DataTypes.ENUM('pending', 'paid'),
    defaultValue: 'pending'
  },
  exchange_rate: {
    type: DataTypes.DECIMAL(10, 4),
    allowNull: true
  },
  created_at: {
    type: DataTypes.DATE
  }
}, {
  tableName: 'payments',
  timestamps: false,
  underscored: true,
  indexes: [
    {
      fields: ['user_id']
    },
    {
      fields: ['parent_id']
    }
  ]
});

module.exports = Payment;