#include <WiFi.h>
#include <PubSubClient.h>

const char* ssid = "Wokwi-GUEST";
const char* password = "";

// Configurações do MQTT
const char* mqtt_server = "broker.emqx.io";
const char* mqtt_topic = "joao/projeto/p3";

WiFiClient espClient;
PubSubClient client(espClient);
#define MQ2_ANA 34 // GPIO34 para entrada analógica no ESP32
#define MQ2_DIG 25 // GPIO25 para entrada digital no ESP32

void setup() {
  Serial.begin(115200); // ESP32 geralmente usa uma taxa de baud mais alta para Serial
  pinMode(MQ2_ANA, INPUT);
  pinMode(MQ2_DIG, INPUT);
  // Inicia a conexão Wi-Fi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWi-Fi conectado!");

  // Inicia o cliente MQTT
  client.setServer(mqtt_server, 1883);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  int analogValue = analogRead(MQ2_ANA);
  int digitalValue = digitalRead(MQ2_DIG);

  Serial.print("Analógico: ");
  Serial.println(analogValue);
  Serial.print("Digital: ");
  Serial.println(digitalValue);

  // Cria um objeto JSON com as leituras
  String jsonData = "{";
  jsonData += "\"analogico\":" + String(analogValue) + ",";
  jsonData += "\"digital\":" + String(digitalValue);
  jsonData += "}";

 // Publica os dados no tópico MQTT e verifica se foi bem-sucedido
  if (client.publish(mqtt_topic, jsonData.c_str())) {
    Serial.println("Mensagem enviada com sucesso!");
  } else {
    Serial.println("Falha ao enviar a mensagem.");
  }

  delay(10000);
}

void reconnect() {
  // Laço até que o cliente MQTT esteja conectado
  while (!client.connected()) {
    Serial.print("Tentando se conectar ao MQTT...");
    if (client.connect("ESP32Client")) {
      Serial.println("conectado!");
    } else {
      Serial.print("falhou, rc=");
      Serial.print(client.state());
      Serial.println(" tentando novamente em 5 segundos");
      delay(5000);
    }
  }
}
