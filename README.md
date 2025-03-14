# Projeto de Integração MQTT e Intercity e sensor de gas 

Neste projeto, uma ESP32 está equipada com um sensor de gás (MQ-5) e implantada em um depósito de gás. A ESP32 monitora constantemente os níveis de gás no ambiente. Se os níveis excederem um limite seguro, indicando a presença de vazamento, a ESP32 publica imediatamente um alerta através do protocolo MQTT, que também será feito a integração com a API da InterSCity. Esta comunicação em tempo real permite uma resposta rápida e eficaz para mitigar os riscos associados aos vazamentos de gás.

Este projeto consiste em tres partes:

## Parte 1 /Sensor E esp32

Desenvolvido usando a plataforma Wokwi, este componente recebe sinais de sensores e publica no canal MQTT configurado.

Link do Projeto: [Sensor e ESP32 no Wokwi](https://wokwi.com/projects/400689979661282305)

## Parte 2 /Servidor De Integração

Desenvolvido em Node.js, este servidor se conecta ao canal MQTT configurado, escuta as publicações, salva os dados em um banco de dados local MySQL e os envia para a API do Intercity.

Configuração dos Sensores no Intercity: [Configuração no Intercity](https://colab.research.google.com/drive/1uN2nL8FTUuwL0P4Eq15w1wqxuncsi2Xl#scrollTo=RrgX-lajd6dw)

### Requisitos

- Node.js
- MySQL

### Instalação e Execução

1. **Instalação das Dependências**

   Crie e configure  o banco de dados em config/config.json, depois execute o seguinte comando para instalar as dependências do projeto:

   ```bash
   npm install
   npx sequelize-cli db:migrate
   node start

## Parte 3 /

#### Endpoint da IntersCity

- URL: `http://cidadesinteligentes.lsdi.ufma.br/collector/resources/8cd968a1-9982-4fcc-882a-f59eafbccb52/data`
- Método: `POST`
Vizualizando de dados na web,,,,



#### preview
