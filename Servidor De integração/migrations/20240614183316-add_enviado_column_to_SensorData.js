'use strict';

module.exports = {
  up: async (queryInterface, Sequelize) => {
    await queryInterface.addColumn('SensorData', 'enviado', {
      type: Sequelize.BOOLEAN,
      defaultValue: false, // Valor padrão será false (não enviado)
      allowNull: false
    });
  },

  down: async (queryInterface, Sequelize) => {
    await queryInterface.removeColumn('SensorData', 'enviado');
  }
};
