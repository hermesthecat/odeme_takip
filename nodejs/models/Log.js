const { Sequelize, DataTypes } = require('sequelize');
const sequelize = new Sequelize('odeme_takip', 'root', 'root', {
  host: 'localhost',
  dialect: 'mysql'
});

const Log = sequelize.define('Log', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  log_method: {
    type: DataTypes.TEXT,
    allowNull: false
  },
  log_text: {
    type: DataTypes.TEXT,
    allowNull: false
  },
  type: {
    type: DataTypes.TEXT,
    allowNull: true
  },
  user_id: {
    type: DataTypes.INTEGER,
    allowNull: true
  },
  created_at: {
    type: DataTypes.DATE,
  }
}, {
  tableName: 'logs',
  timestamps: false,
  underscored: true
});

module.exports = Log;