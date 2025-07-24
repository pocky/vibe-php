# Démarche Itérative pour les Règles Métier

## Philosophie

**Principe fondamental** : Commencer simple, puis itérer avec des User Stories spécifiques pour chaque contrainte ou cas limite.

## Processus

### 1. Règles Métier de Base (MVP)
- Définir le comportement minimal fonctionnel
- Ignorer volontairement les cas limites dans cette première phase
- Focus sur le happy path principal

### 2. Identification des Cas Limites
- Lister tous les "et si..." et contraintes possibles
- Identifier les règles de validation, sécurité, performance
- Noter les cas d'erreur et situations exceptionnelles

### 3. Création de User Stories Dédiées
- Une User Story par contrainte ou cas limite identifié
- Chaque US doit être testable et livrable indépendamment
- Priorisation selon la criticité business

### 4. Itération Continue
- Implémenter les règles de base d'abord
- Ajouter les contraintes une par une via les US
- Permettre les ajustements et révisions basés sur les retours

## Avantages de cette Approche

1. **Livraison rapide** : Le MVP fonctionne vite
2. **Flexibilité** : Possibilité d'ajuster les priorités
3. **Testabilité** : Chaque contrainte est isolée et testable
4. **Compréhension progressive** : Les besoins se clarifient avec l'usage
5. **Réduction des risques** : Évite l'over-engineering initial

## Template pour les Règles Métier

### Phase 1 - Règles de Base
```markdown
## [Fonctionnalité] - Règles de Base

**Comportement principal** :
- Action X fait Y
- Condition Z entraîne W

**Assumptions** :
- Utilisateur authentifié
- Données valides
- Système disponible
```

### Phase 2 - Identification des Cas Limites
```markdown
## [Fonctionnalité] - Cas Limites Identifiés

**Sécurité** :
- [ ] Permissions utilisateur
- [ ] Validation des données
- [ ] Protection contre les abus

**Performance** :
- [ ] Limites de volume
- [ ] Timeouts
- [ ] Optimisations

**Business** :
- [ ] Règles métier complexes
- [ ] Cas d'exception
- [ ] Workflows spéciaux
```

### Phase 3 - User Stories pour Contraintes
```markdown
#### US-XXX: [Contrainte Spécifique]
**En tant que** [utilisateur]
**Je veux** [comportement spécifique à la contrainte]
**Afin de** [bénéfice business]

**Critères d'acceptation** :
- [ ] Critère 1 (testable)
- [ ] Critère 2 (mesurable)
- [ ] Critère 3 (vérifiable)

**Contexte** : Cette US gère le cas limite [X] identifié lors de l'analyse
```

## Exemple d'Application

### Fonctionnalité : Suppression d'Articles

#### Phase 1 - Règles de Base
- Un utilisateur peut supprimer un article
- L'article disparaît du système

#### Phase 2 - Cas Limites Identifiés
- Permissions (qui peut supprimer quoi ?)
- États des articles (draft vs published)
- Données associées (commentaires, révisions)
- Audit trail (traçabilité)
- Confirmation utilisateur

#### Phase 3 - User Stories Créées
- US-050 : Permissions de suppression par rôle
- US-051 : Protection des articles publiés
- US-052 : Gestion des données en cascade
- US-053 : Journal d'audit des suppressions
- US-054 : Confirmation de suppression