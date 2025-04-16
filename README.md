Documentation Technique – Projet IoT
====================================

Objectif du projet
------------------

Créer une solution IoT complète permettant de :

*   Mesurer température et humidité avec un objet connecté (**Arduino MKR FOX 1200**)
    
*   Transmettre les données via le réseau **Sigfox**
    
*   Stocker les mesures dans une base de données **MySQL**
    
*   Visualiser les données sur un site web dynamique avec tableau et graphique
    

Architecture du projet
----------------------

[Arduino + Capteur] ──► [Sigfox Backend] ──► [serveur PHP: recevoir.php] ──► [Base de données MySQL] ──► [API PHP: api.php] ──► [Dashboard Web] ──► [HTML/CSS + JS]

Étapes du projet
----------------

### 1\. Configuration de l'objet connecté

*   Utilisation d’un **Arduino MKR FOX 1200**
    
*   Capteur de température/humidité connecté
    
*   Envoi des données via **Sigfox** à chaque appui sur un bouton
    

### 2\. Envoi vers le backend Sigfox

*   Création d’un **callback HTTP** dans l’espace Sigfox
    
*   Le callback POST les données JSON vers le fichier recevoir.php
    

**Exemple de payload JSON reçu** :
{
  "device": "DEVICE_ID",
  "time": 1712668412,
  "data": "0000e0410000b041",
  "seqNum": 104
}

Scripts PHP utilisés
--------------------

### recevoir.php

*   Reçoit les données JSON depuis Sigfox
    
*   Décode le payload hexadécimal (data) en **floats** : température + humidité
    
*   Vérifie la séquence seqNum pour détecter les messages manqués
    
*   Insère les données dans la base **MySQL**
    

> Voir le fichier recevoir.php dans ce dépôt.

### api.php

*   Expose les **50 dernières mesures** dans un tableau JSON
    
*   Utilisé côté front par le site web pour afficher les mesures dynamiquement
    

> Voir le fichier api.php dans ce dépôt.

Structure de la base de données
-------------------------------

**Base** : gi50x\_IoT**Table** : mesures
CREATE TABLE mesures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device VARCHAR(255),
    time DATETIME,
    temperature FLOAT,
    humidity FLOAT,
    seqNum INT DEFAULT NULL
);

Site web – Dashboard
--------------------

### Fonctionnalités

*   Affichage d’un tableau HTML avec les mesures
    
*   Affichage d’un graphique dynamique avec **Chart.js**
    
*   **Pagination** : 10 mesures par page
    
*   **Rechargement automatique** toutes les 60 secondes
    

### Composants

*   index.html : page principale
    
*   script.js : chargement des données, pagination, graphique
    
*   style.css : apparence visuelle
    

### Exemple de données affichées

ID	Device	Heure	Température	Humidité
18	18E1F5	09/04/2025 10:38:16	28.52°C	50.00%

Détection des messages manqués
------------------------------

*   Utilisation du champ seqNum envoyé par Sigfox
    
*   Stocké dans la base pour chaque mesure
    
*   Vérification à chaque insertion :
    
    *   Si seqNum reçu ≠ dernier seqNum + 1 → **message(s) manqué(s) détecté(s)**
        
*   Log automatique dans les erreurs PHP
    

Fonctionnalités implémentées
----------------------------

*   Lecture capteur + envoi via Sigfox
    
*   Réception JSON sur serveur PHP
    
*   Décodage **hex → float**
    
*   Insertion dans base **MySQL**
    
*   Affichage **tableau** et **graphique**
    
*   **Pagination dynamique**
    
*   Vérification de séquence seqNum (Fonctionne pas)
      
*   **Rafraîchissement automatique** des données
    

Améliorations possibles
-----------------------

*   Vérification de séquence seqNum

*   Envoi d’un **mail** en cas de perte de messages (seqNum)
    
*   Ajout de **filtres** par date ou par device
    
*   Statistiques **moyennes / max / min**
    
*   Interface d’**administration**
    

Auteur
------

*   **Nom** : Ursanne Salomon
    
*   **Classe** : INF3A
    
*   **Date** : Avril 2025
    
*   **Projet** : Dashboard IoT complet
    

Conclusion
----------

Le projet est entièrement fonctionnel :

*   Données récupérées **en temps réel**
    
*   Interface claire, fluide, **responsive**
    
*   Livrable prêt à être **déployé ou évalué**
