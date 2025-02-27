const { Sequelize, DataTypes } = require('sequelize');
const sequelize = new Sequelize('odeme_takip', 'root', 'root', {
  host: 'localhost',
  dialect: 'mysql'
});

const Portfolio = sequelize.define('Portfolio', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  sembol: {
    type: DataTypes.STRING(10),
    allowNull: false
  },
  adet: {
    type: DataTypes.INTEGER,
    allowNull: false
  },
  alis_fiyati: {
    type: DataTypes.DECIMAL(10, 2),
    allowNull: false
  },
  alis_tarihi: {
    type: DataTypes.DATE
  },
  anlik_fiyat: {
    type: DataTypes.DECIMAL(10, 2),
    defaultValue: 0.00
  },
  son_guncelleme: {
    type: DataTypes.DATE
  },
  hisse_adi: {
    type: DataTypes.STRING(255),
    defaultValue: ''
  },
  satis_fiyati: {
    type: DataTypes.DECIMAL(10, 2),
    allowNull: true
  },
  satis_tarihi: {
    type: DataTypes.DATE,
    allowNull: true
  },
  satis_adet: {
    type: DataTypes.INTEGER,
    allowNull: true
  },
  durum: {
    type: DataTypes.ENUM('aktif', 'satildi', 'kismi_satildi', 'satis_kaydi'),
    defaultValue: 'aktif'
  },
  user_id: {
    type: DataTypes.INTEGER,
    allowNull: true
  },
  referans_alis_id: {
    type: DataTypes.INTEGER,
    allowNull: true
  }
}, {
  tableName: 'portfolio',
  timestamps: false,
  underscored: true
});

module.exports = Portfolio;