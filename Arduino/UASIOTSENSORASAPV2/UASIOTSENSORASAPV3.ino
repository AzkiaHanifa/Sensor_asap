#include "CTBot.h"
#include <DHT.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

CTBot myBot;

// Konfigurasi WiFi
String ssid = "Dragon of Dojima";
String pass = "kaslana3212";
String token = "token telegram nya";  // Token Telegram

// Konfigurasi Pin
#define DHTPIN D7
#define DHTTYPE DHT22
#define Gas 14  // GPIO14 (D5) untuk sensor gas

// Objek Sensor
DHT dht(DHTPIN, DHTTYPE);

// Konfigurasi Server API
const char* serverName = "http://192.168.18.5:8000/api/sensor-data";

// Setup
void setup() {
  pinMode(Gas, INPUT);
  Serial.begin(115200);
  
  // Menghubungkan ke WiFi
  WiFi.begin(ssid.c_str(), pass.c_str());
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Terhubung!");

  // Menghubungkan ke Telegram
  myBot.wifiConnect(ssid, pass);
  myBot.setTelegramToken(token);
  
  // Memeriksa koneksi Telegram
  if (myBot.testConnection()) {
    Serial.println("\nTerhubung ke Telegram");
  } else {
    Serial.println("\nTidak Terhubung ke Telegram");
  }
  
  // Inisialisasi sensor DHT22
  dht.begin();
}

// Loop
void loop() {
  // Membaca data dari sensor gas
  int bacasensorgas = digitalRead(Gas);
  Serial.print("Gas : ");
  Serial.println(bacasensorgas);

  // Cek deteksi gas
  if (bacasensorgas == 0) { // Jika ada kebocoran gas
    String message = "Potensial kebakaran segera cek kerumah !!!!!!!!";
    myBot.sendMessage(6159583940, message);  // Kirim pesan ke Telegram
    delay(500); 
  }

  // Membaca data suhu dan kelembaban
  float humidity = dht.readHumidity();
  float temperature = dht.readTemperature();
  int gas = analogRead(Gas);  // Sensor gas MQ2

  // Validasi pembacaan sensor
  if (isnan(humidity) || isnan(temperature)) {
    Serial.println("Gagal membaca data dari DHT22");
    delay(2000);
    return;
  }

  // Membalikkan pembacaan sensor gas
  gas = 1023 - gas;  // Jika gas = 0, maka gas = 1023, dan sebaliknya

  // Validasi nilai gas
  if (gas < 0) gas = 0;
  if (gas > 1023) gas = 1023;

  // Menyusun payload JSON
  DynamicJsonBuffer jsonBuffer;
  JsonObject& jsonDoc = jsonBuffer.createObject();
  jsonDoc["humidity"] = humidity;
  jsonDoc["temperature"] = temperature;
  jsonDoc["smoke"] = gas;

  String jsonString;
  jsonDoc.printTo(jsonString);

  // Mengirim data ke server API
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient wifiClient;
    HTTPClient http;

    http.begin(wifiClient, serverName);
    http.addHeader("Content-Type", "application/json");

    int httpCode = http.POST(jsonString);
    if (httpCode > 0) {
      Serial.println("Data berhasil dikirim: " + http.getString());
    } else {
      Serial.println("Gagal mengirim data");
    }

    http.end();
  }

  // Delay untuk pengiriman data setiap 1.5 detik
  delay(1500);
}
