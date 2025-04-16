# 📘 Documentation Technique – Projet IoT

---

## Objectif du projet

Créer une solution **IoT complète** permettant de :

- **Mesurer** température et humidité avec un objet connecté (Arduino MKR FOX 1200)
- **Transmettre** les données via le réseau Sigfox
- **Stocker** les mesures dans une base de données MySQL
- **Visualiser** les données sur un site web dynamique avec tableau et graphique

---

## Architecture du projet

[Arduino + Capteur]
       │
       ▼
[Sigfox Backend]
       │
       ▼
[serveur PHP: recevoir.php] ──► [Base de données MySQL]
       ▲                                  │
       │                                  ▼
[API PHP: api.php]                  [Dashboard Web]
                                          ▼
                                  [HTML/CSS + JS]

---

## 🔧 Étapes du projet

### 1. Configuration de l'objet connecté

- Utilisation d’un **Arduino MKR FOX 1200**
- Capteur de température/humidité connecté
- Envoi des données via Sigfox à chaque appui sur un bouton

### 2. Envoi vers le backend Sigfox

- Création d’un **callback HTTP** dans l’espace Sigfox
- Le callback POST les données JSON vers notre fichier `recevoir.php`

**Payload JSON reçu :**

```json
{
  "device": "DEVICE_ID",
  "time": 1712668412,
  "data": "0000e0410000b041",
  "seqNum": 104
}
