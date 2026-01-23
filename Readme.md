# Projet Cloud Computing – Pipeline CI/CD

## Contexte académique

Ce projet a été réalisé dans le cadre du cours de **Cloud Computing (HN5 – année académique 2025/2026)**, dispensé par **le professeur Mvondo Djob**.

L’objectif principal est la **mise en place d’un pipeline CI/CD**, pour une application web déployée sur un serveur cloud.

---

## Objectif du projet

Le projet vise à concevoir, conteneuriser, tester et déployer automatiquement une application **simple basée sur une architecture microservices**, en respectant les bonnes pratiques de **CI/CD**.

L’accent est mis sur :

* L’automatisation du déploiement ;
* L’utilisation de **Docker** et **Docker Compose** ;
* L’intégration continue avec **GitHub Actions** ;
* Le déploiement sur une infrastructure cloud (**AWS EC2**).

> ⚠️ **NB** : Les mesures de sécurité au niveau métier sont volontairement limitées. L’objectif est de disposer d’une application fonctionnelle dans un contexte pédagogique.

---

## Description de l’application

L’application développée est une **application bancaire simplifiée**, basée sur une architecture microservices.

### Architecture globale

L’application comporte **trois services principaux** :

1. **Frontend** : développé avec **Next.js** ;
2. **Backend Users-service** : développé avec **Laravel** ;
3. **Backend Accounts-service** : développé avec **Laravel**.

> Le frontend communique directement avec les deux backends, sans passer par de gateway API.
> Les adresses des services backend sont configurées dans :
>
> ```text
> lib/app.js
> ```

---

### Microservice Users

Ce microservice gère :

* L’authentification (login / register) ;
* Les opérations CRUD sur les utilisateurs.

---

### Microservice Accounts

Ce microservice gère :

* Les comptes bancaires (CRUD) ;
* Dépôts et retraits d’argent ;
* Transferts entre comptes ;
* Sauvegarde et consultation de l’historique des transactions.

---

### Base de données

Les deux microservices utilisent **SQLite**, choisi pour sa légèreté sur l’instance EC2 **t3.micro**.

---

## Déploiement Cloud

### Infrastructure

L’application est déployée sur une instance **AWS EC2 (type t3.micro)**.
Nom de l’instance : `projet cloud CI/CD`.

### Technologies

* **Docker** : conteneurisation ;
* **Docker Compose** : orchestration ;
* **Nginx** : reverse proxy pour exposer les services.

---

## Pipeline CI/CD

La chaîne CI/CD est implémentée via **GitHub Actions**, déclenchée automatiquement à chaque modification du code source.

> ⚠️ **Important** : Pour ce projet, **seuls quelques tests unitaires ont été écrits manuellement** pour valider le pipeline. L’accent est mis sur la mise en œuvre du CI/CD plutôt que sur une couverture complète de tests.

### Étapes du pipeline

1. Détection du microservice modifié ;
2. Exécution des tests automatisés (quelques tests unitaires manuels) ;
3. Build de l’image Docker correspondante ;
4. Push de l’image sur Docker Hub ;
5. Connexion à l’instance EC2 via SSH ;
6. Redémarrage des conteneurs pour prendre en compte les nouvelles images.

---

## Procédure de déploiement

Pour lancer l’application :

1. Démarrer l’instance EC2 `projet cloud CI/CD` ;
2. Récupérer l’**IPv4 publique** de l’instance ;
3. Remplacer cette adresse dans :

```text
.github/workflows/deploy.yaml
```

4. Lancer le workflow **deploy.yaml** via GitHub Actions.

> Le déploiement est entièrement automatisé.
> ⚠️ Même avec seulement quelques tests unitaires manuels, le pipeline garantit la mise à jour sécurisée des services.

---

## Mise à jour continue

Une fois l’application déployée, les mises à jour sont entièrement automatisées grâce au pipeline CI/CD.

### Workflow de mise à jour

1. Modifier le code du microservice concerné ;
2. Commit local (`git commit`) ;
3. Push sur la branche principale (`git push origin main`).

### Comportement du pipeline

* GitHub Actions détecte automatiquement les modifications ;
* Les tests automatisés (manuels) sont exécutés ;
* Si un test échoue, le pipeline s’arrête et le déploiement est bloqué ;
* Si les tests réussissent :

  * Construction et push de la nouvelle image Docker ;
  * Redémarrage automatique des conteneurs sur EC2.

> ⚠️ Encore une fois, la validation repose sur **quelques tests unitaires manuels**, suffisants pour ce projet pédagogique.

---

## Test du pipeline CI/CD

### Objectif

Vérifier que le pipeline :

* détecte les modifications ;
* exécute les tests (manuels) ;
* bloque le déploiement en cas d’échec ;
* déploie automatiquement l’application en cas de succès.

### Test 1 : Déclenchement du pipeline

1. Modifier un fichier du projet ;
2. Commit & push sur `main` ;
3. Vérifier dans **Actions** que le workflow **CI/CD Deploy to EC2** se lance automatiquement.

### Test 2 : Tests automatisés

1. Introduire volontairement une erreur dans un test manuel ;
2. Pousser sur GitHub ;
3. Observer le pipeline.

**Résultat attendu** :

* Les tests échouent ;
* Le pipeline s’arrête ;
* Les images Docker **ne sont pas construites** ;
* Aucun déploiement sur EC2.

### Test 3 : Déploiement automatique

1. Corriger l’erreur introduite ;
2. Commit & push ;
3. Vérifier que :

   * Les tests manuels passent ;
   * Les images Docker sont construites et poussées ;
   * Les conteneurs sur EC2 sont redémarrés.

---

## Conclusion

Ce projet montre la mise en œuvre complète d’un pipeline **CI/CD fonctionnel** pour une application microservices conteneurisée, déployée dans le cloud.

> ⚠️ Même si **seuls quelques tests unitaires ont été écrits manuellement**, le pipeline permet :
>
> * La conteneurisation ;
> * L’orchestration ;
> * L’intégration continue ;
> * Le déploiement continu ;
> * L’exploitation d’une infrastructure cloud.

---
