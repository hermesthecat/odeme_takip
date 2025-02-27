const { Sequelize, DataTypes } = require('sequelize');
const sequelize = new Sequelize('odeme_takip', 'root', 'root', {
  host: 'localhost',
  dialect: 'mysql'
});

const Saving = sequelize.define('Saving', {
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
  name: {
    type: DataTypes.STRING(100),
    allowNull: false
  },
  target_amount: {
    type: DataTypes.DECIMAL(10, 2),
    allowNull: false
  },
  current_amount: {
    type: DataTypes.DECIMAL(10, 2),
    defaultValue: 0.00
  },
  currency: {
    type: DataTypes.STRING(3),
    defaultValue: 'TRY'
  },
  start_date: {
    type: DataTypes.DATE,
    allowNull: false
  },
  target_date: {
    type: DataTypes.DATE,
    allowNull: false
  },
  created_at: {
    type: DataTypes.DATE
  }
}, {
  tableName: 'savings',
  timestamps: false,
  underscored: true,
  indexes: [
    {
      fields: ['user_id']
    }
  ]
});

module.exports = Saving;