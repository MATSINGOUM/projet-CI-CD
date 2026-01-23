# Projet Cloud Computing – Pipeline CI/CD

## Contexte académique

Ce projet a été réalisé dans le cadre du cours de **Cloud Computing (HN5 – année académique 2025/2026)**, dispensé par **le professeur Mvondo Djob**.

L’objectif principal du projet est la **mise en place d’un pipeline CI/CD complet**, appliqué à une application concrète déployée dans le cloud.

---

## Objectif du projet

Le projet vise à concevoir, conteneuriser, tester et déployer automatiquement une application **simple basée sur une architecture microservices**, tout en mettant en œuvre les bonnes pratiques de **CI/CD**.

L’accent est mis sur :

* l’automatisation du déploiement ;
* l’utilisation de Docker et Docker Compose ;
* l’intégration continue avec GitHub Actions ;
* le déploiement sur une infrastructure cloud (AWS EC2).

> ⚠️ **NB** : Les mesures de sécurité au niveau **métier** sont volontairement minimalistes. Le but du projet est avant tout d’obtenir une application fonctionnelle à déployer dans un contexte pédagogique.

---

## Description de l’application

L’application développée est une **application de gestion bancaire très simplifiée**, basée sur une architecture microservices.

### Architecture globale

L’application est composée de **trois services principaux** :

* **Un frontend** développé avec **Next.js** ;
* **Deux backends** développés avec **Laravel**.

Il n’existe **pas de gateway API**. Le frontend communique **directement** avec les deux backends.

Les adresses des services backend sont configurées directement dans le fichier :

```text
lib/app.js
```

---

### Microservice Users

Ce microservice est responsable de la gestion des utilisateurs :

* authentification (login / register) ;
* opérations CRUD sur les comptes utilisateurs.

---

### Microservice Accounts

Ce microservice gère les fonctionnalités bancaires :

* opérations CRUD sur les comptes bancaires ;
* dépôt et retrait d’argent ;
* transfert de fonds entre comptes ;
* sauvegarde et consultation de l’historique des transactions.

---

### Base de données

Les deux microservices utilisent **SQLite** comme système de gestion de base de données.

Ce choix a été fait en raison des **ressources limitées** du nœud EC2 **t3.micro** utilisé pour le déploiement.

---

## Déploiement Cloud

### Infrastructure

L’application est déployée sur une instance **AWS EC2 (type t3.micro)**.

* **Nom de l’instance** : `projet cloud CI/CD`

---

### Technologies de déploiement

* **Docker** : conteneurisation des services ;
* **Docker Compose** : orchestration des conteneurs ;
* **Nginx** : reverse proxy pour exposer les différentes applications.

---

## Pipeline CI/CD

La chaîne CI/CD est implémentée à l’aide de **GitHub Actions**.

### Déclenchement

Le pipeline est déclenché à chaque modification du code source sur le dépôt GitHub.

---

### Étapes du pipeline CI/CD

La chaîne CI/CD repose sur les étapes suivantes :

1. **Détection du microservice modifié** ;
2. **Exécution des tests automatisés** ;
3. **Build de l’image Docker correspondante** ;
4. **Push de l’image sur Docker Hub** ;
5. **Connexion à l’instance EC2 via SSH** ;
6. **Redémarrage des conteneurs Docker** afin de prendre en compte les nouvelles images.

---

## Procédure de déploiement

Pour lancer la plateforme, il suffit de suivre les étapes suivantes :

1. Démarrer l’instance EC2 `projet cloud CI/CD` sur AWS ;
2. Récupérer l’**adresse IPv4 publique** de l’instance ;
3. Remplacer cette adresse dans le fichier :

```text
.github/workflows/deploy.yaml
```

4. Lancer manuellement ou automatiquement le workflow **deploy.yaml** via GitHub Actions.

Le déploiement est alors entièrement automatisé.

---

## Conclusion

Ce projet permet de démontrer la mise en œuvre complète d’un pipeline **CI/CD fonctionnel**, appliqué à une application microservices conteneurisée et déployée dans le cloud.

Il illustre de manière concrète les concepts abordés en cours de Cloud Computing, notamment :

* la conteneurisation ;
* l’orchestration ;
* l’intégration continue ;
* le déploiement continu ;
* l’exploitation d’une infrastructure cloud.
