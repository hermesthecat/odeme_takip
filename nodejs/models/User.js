const { Sequelize, DataTypes } = require('sequelize');
const sequelize = new Sequelize('odeme_takip', 'root', 'root', {
  host: 'localhost',
  dialect: 'mysql'
});

const User = sequelize.define('User', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  username: {
    type: DataTypes.STRING(50),
    allowNull: false,
    unique: true
  },
  password: {
    type: DataTypes.STRING,
    allowNull: false
  },
  base_currency: {
    type: DataTypes.STRING(3),
    allowNull: false,
    defaultValue: 'TRY'
  },
  theme_preference: {
    type: DataTypes.STRING(10),
    allowNull: false,
    defaultValue: 'light'
  },
  created_at: {
    type: DataTypes.DATE
  },
  remember_token: {
    type: DataTypes.STRING(64),
    allowNull: true
  },
  is_admin: {
    type: DataTypes.INTEGER,
    allowNull: true
  }
}, {
  tableName: 'users',
  timestamps: false
});

module.exports = User;