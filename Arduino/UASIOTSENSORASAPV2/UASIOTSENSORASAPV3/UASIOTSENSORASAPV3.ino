#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <ArduinoJson.h>
#include <DHT.h>

#define DHTPIN D7
#define DHTTYPE DHT22
#define Gas 14  // Sensor gas di analog pin

DHT dht(DHTPIN, DHTTYPE);
ESP8266WebServer server(80);

const char* ssid = "";   // Ganti dengan WiFi Anda
const char* pass = "";  // Ganti dengan password WiFi Anda

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, pass);

  // Tunggu sampai ESP8266 terhubung ke WiFi
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Terhubung!");
  Serial.print("ESP8266 IP Address: ");
  Serial.println(WiFi.localIP());  // Cetak IP ESP8266

  dht.begin();

  // Endpoint untuk menyajikan data sensor
  server.on("/sensor-data", []() {
    float humidity = dht.readHumidity();
    float temperature = dht.readTemperature();
    int smoke = analogRead(Gas);  // Sesuaikan pembacaan sensor gas
    smoke = 1023 - smoke;             // Jika gas = 0, maka gas = 1023, dan sebaliknya

    // Validasi nilai gas
    if (smoke < 0) smoke = 0;
    if (smoke > 1023) smoke = 1023;

    if (isnan(humidity) || isnan(temperature)) {
      server.sendHeader("Access-Control-Allow-Origin", "*");
      server.send(500, "application/json", "{\"error\": \"Sensor read failed\"}");
      return;
    }

    StaticJsonDocument<200> jsonDoc;
    jsonDoc["humidity"] = humidity;
    jsonDoc["temperature"] = temperature;
    jsonDoc["smoke"] = smoke;

    String jsonString;
    serializeJson(jsonDoc, jsonString);

    // **Tambahkan Header CORS**
    server.sendHeader("Access-Control-Allow-Origin", "*");
    server.send(200, "application/json", jsonString);
  });

  server.begin();
}

void loop() {
  server.handleClient();  // Tangani permintaan HTTP
}
