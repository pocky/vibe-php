# Proposition d'Optimisation des Commandes Claude

## Analyse des Commandes Actuelles

### ✅ Commandes Core du Workflow (À Garder)
- `/prd` - Define requirements
- `/plan` - Design solution  
- `/act` - Build with TDD
- `/qa` - Quality checks
- `/workflow-help` - Get help
- `/workflow-status` - Check progress
- `/user-story` - Create user stories

### 🔄 Commandes à Refactorer

#### 1. `/integrate-plan-to-prd`
**Problème** : Redondant avec la nouvelle structure de documentation
**Solution** : 
- Option A : Supprimer complètement
- Option B : Transformer en `/link-docs` pour créer des références croisées

#### 2. `/report`
**Problème** : Trop générique, fait doublon avec d'autres commandes
**Solution** : Créer des commandes spécifiques :
- `/adr` - Pour Architecture Decision Records uniquement
- `/retrospective` - Intégré à la fin de `/act`
- Supprimer status et documentation (couverts par autres commandes)

#### 3. `/understand`
**Problème** : Très spécifique au code legacy
**Solution** :
- Renommer en `/analyze-legacy` pour clarifier l'usage
- Ou intégrer dans `/plan` avec option `--existing-code`

## Structure Proposée

### Workflow Principal (6 commandes)
```mermaid
graph LR
    A[/prd] --> B[/plan]
    B --> C[/act]
    C --> D[/qa]
    
    E[/workflow-help] -.-> A
    F[/workflow-status] -.-> A
    
    style A fill:#fff3e0
    style B fill:#f3e5f5  
    style C fill:#e8f5e9
    style D fill:#ffebee
```

### Commandes Auxiliaires (3 commandes)
- `/user-story` - Création de user stories individuelles
- `/adr` - Documentation de décisions architecturales
- `/analyze-legacy` - Analyse de code existant (si nécessaire)

## Avantages de cette Simplification

1. **Clarté** : Chaque commande a un but unique et clair
2. **Pas de duplication** : Évite les fonctionnalités redondantes
3. **Workflow fluide** : Les commandes principales suivent le cycle naturel
4. **Flexibilité** : Les commandes auxiliaires sont optionnelles

## Migration

Pour les utilisateurs existants :
- `/integrate-plan-to-prd` → Utiliser la structure de dossiers
- `/report status` → `/workflow-status`
- `/report adr` → `/adr`
- `/understand` → `/analyze-legacy`

## Implémentation

1. Archiver les commandes obsolètes dans `.claude/commands/archived/`
2. Mettre à jour `/workflow-help` avec la nouvelle structure
3. Créer `/adr` comme commande dédiée
4. Simplifier la documentation