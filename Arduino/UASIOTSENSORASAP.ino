#include <DHT.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

// Pin Konfigurasi
#define DHTPIN D7
#define DHTTYPE DHT22
#define MQ2PIN D2

// WiFi
const char* ssid = "Dragon of Dojima";
const char* password = "kaslana3212";
const char* serverName = "http://192.168.18.5:8000/api/sensor-data";

// Objek
DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);

  Serial.println("Menghubungkan ke WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Terhubung");
  
  dht.begin();
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient wifiClient;
    HTTPClient http;
    http.begin(wifiClient, serverName); // Memperbaiki pemanggilan metode begin()
    http.addHeader("Content-Type", "application/json");

    // Baca data dari sensor
    float humidity = dht.readHumidity();
    float temperature = dht.readTemperature();
    int smoke = analogRead(MQ2PIN);

    // Validasi data
    if (isnan(humidity) || isnan(temperature)) {
      Serial.println("Gagal membaca data DHT22");
      delay(2000);
      return;
    }

    // Buat JSON payload
    StaticJsonDocument<200> jsonDoc;
    jsonDoc["humidity"] = humidity;
    jsonDoc["temperature"] = temperature;
    jsonDoc["smoke"] = smoke;

    String jsonString;
    serializeJson(jsonDoc, jsonString);

    // Kirim data ke server
    int httpCode = http.POST(jsonString);
    if (httpCode > 0) {
      Serial.println("Data berhasil dikirim: " + http.getString());
    } else {
      Serial.println("Gagal mengirim data");
    }
    http.end();
  }
  delay(1500); // Kirim data setiap 5 detik
}