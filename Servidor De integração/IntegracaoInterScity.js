const axios = require('axios');
const cron = require('node-cron');
const SensorData = require('./RotinaMqtt');

async function enviarDadosParaIntercity() {
  try {
    const dadosNaoEnviados = await SensorData.findAll({
      where: {
        enviado: false
      }
    });
    const dadosParaIntercity = dadosNaoEnviados.map(dado => ({
      Sensor_Gas_A: dado.analogico,
      Sensor_Gas_D: dado.digital,
      timestamp: new Date().toISOString()
    }));

    // Envia os dados para a Intercity
    const api = 'http://cidadesinteligentes.lsdi.ufma.br';
    const response = await axios.post(api + '/adaptor/resources/8cd968a1-9982-4fcc-882a-f59eafbccb52/data/environment_monitoring', {
      data: dadosParaIntercity
    });

    if (response.status === 201) {
      await Promise.all(dadosNaoEnviados.map(dado => dado.update({ enviado: true })));
      console.log('Dados enviados com sucesso para a Intercity e marcados como enviados.');
    } else {
      console.error('Erro ao enviar dados para a Intercity:', response.status);
    }
  } catch (error) {
    console.error('Erro durante o envio de dados para a Intercity:', error);
  }
}

// Agendar o envio de dados a cada 5 minutos
cron.schedule('*/1 * * * *', () => {
  console.log('Enviando dados para a Intercity...');
  enviarDadosParaIntercity();
});

// Início da aplicação
console.log('Integração com a Intercity iniciada.');
