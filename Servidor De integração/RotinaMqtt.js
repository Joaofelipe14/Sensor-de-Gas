const mqtt = require('mqtt');
const Sequelize = require('sequelize');
const config = require('./config/config.json');

// Configurações do MQTT
const mqttServer = 'mqtt://broker.emqx.io';
const mqttTopic = 'joao/projeto/p3';

const { username, password, database, host } = config.development;
const sequelize = new Sequelize(database, username, password, {
  host: host,
  dialect: 'mysql'
});

// Modelo Sequelize para os dados do sensor
const SensorData = sequelize.define('SensorData', {
  analogico: Sequelize.INTEGER,
  digital: Sequelize.INTEGER,
  enviado: {
    type: Sequelize.BOOLEAN,
    defaultValue: false
  }
});

sequelize.authenticate()
  .then(() => {
    console.log('Conexão com o banco de dados estabelecida com sucesso.');
  })
  .catch(err => {
    console.error('Erro ao conectar com o banco de dados:', err);
  });

// Conectar ao servidor MQTT
const client = mqtt.connect(mqttServer);
client.on('connect', () => {
  console.log('Conexão MQTT estabelecida com sucesso.');
  client.subscribe(mqttTopic);
});

client.on('message', (topic, message) => {
  const data = JSON.parse(message.toString());
  console.log('Mensagem recebida do tópico', topic, ':', data);

  // Salvar os dados no banco de dados local
  SensorData.create({
    analogico: data.analogico,
    digital: data.digital
  })
    .then(() => {
      console.log('Dados salvos no banco de dados local.');
    })
    .catch(err => {
      console.error('Erro ao salvar dados no banco de dados local:', err);
    });
});

module.exports = SensorData;
