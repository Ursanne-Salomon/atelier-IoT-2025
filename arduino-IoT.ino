#include <Arduino.h>
#include <DHT.h>
#include <SigFox.h>

// ---------------------------------------------------------------------
// Configuration des pins et du capteur
// ---------------------------------------------------------------------
#define DHTPIN      5     // Broche à laquelle le DHT11 est connecté (D5)
#define DHTTYPE     DHT11 // Type du capteur (DHT11)
#define BUTTONPIN   1     // Broche à laquelle le bouton est connecté (D1)

// ---------------------------------------------------------------------
// Initialisation de la librairie DHT
// ---------------------------------------------------------------------
DHT dht(DHTPIN, DHTTYPE);

// ---------------------------------------------------------------------
// Variables globales
// ---------------------------------------------------------------------
bool lastButtonState = HIGH;          // État précédent du bouton
unsigned long lastSendTime = 0;       // Dernière heure d'envoi des données
const unsigned long sendInterval = 3600000; // Intervalle d'envoi (1h = 3600000 ms)

// ---------------------------------------------------------------------
// Fonction setup() : Initialisation
// ---------------------------------------------------------------------
void setup() {
  Serial.begin(9600);      // Démarre la communication série
  dht.begin();             // Initialise le capteur DHT
  pinMode(BUTTONPIN, INPUT_PULLUP); // Configure le bouton en INPUT_PULLUP

  // Initialise SigFox et vérifie la communication
  SigFox.debug();
  if (!SigFox.begin()) {
    Serial.println("⚠️ Erreur : SigFox non détecté !");
    return; 
  }
  SigFox.debug(); // Active le mode débogage (facultatif)
}

// ---------------------------------------------------------------------
// Fonction loop() : Boucle principale
// ---------------------------------------------------------------------
void loop() {
  bool buttonState = digitalRead(BUTTONPIN);  // Lit l'état du bouton
  unsigned long currentMillis = millis();     // Récupère le temps écoulé depuis le démarrage

  // Envoi automatique toutes les 'sendInterval' millisecondes
  // OU envoi immédiat si le bouton vient d'être pressé (front descendant)
  if ((currentMillis - lastSendTime >= sendInterval) || 
      (buttonState == LOW && lastButtonState == HIGH)) {
    envoyerDonneesSigFox();
    lastSendTime = currentMillis;
  }

  // Met à jour l'état précédent du bouton
  lastButtonState = buttonState;
}

// ---------------------------------------------------------------------
// Fonction d'envoi des données via SigFox
// ---------------------------------------------------------------------
void envoyerDonneesSigFox() {
  float temperature = dht.readTemperature(); // Lit la température
  float humidity    = dht.readHumidity();    // Lit l'humidité

  // Vérifie si les lectures sont valides
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("⚠️ Erreur de lecture du capteur !");
    return;
  }

  // Affiche les données dans la console série
  Serial.print("📡 Envoi des données - ");
  Serial.print("🌡️ Température : ");
  Serial.print(temperature);
  Serial.print(" °C | ");
  Serial.print("💧 Humidité : ");
  Serial.print(humidity);
  Serial.println(" %");

  // Prépare et envoie la trame SigFox
  SigFox.beginPacket();
  SigFox.write((uint8_t*)&temperature, 4); // Écrit 4 octets pour la température
  SigFox.write((uint8_t*)&humidity, 4);   // Écrit 4 octets pour l'humidité
  SigFox.endPacket();                     // Termine l'envoi
}
