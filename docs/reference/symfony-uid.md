# Symfony UID Component

Ce document décrit l'utilisation du composant Symfony UID dans le projet pour la génération d'identifiants uniques.

## Installation

Le composant est installé via Composer :

```bash
composer require symfony/uid
```

## Vue d'ensemble

Le composant Symfony UID fournit des utilitaires pour travailler avec des identifiants uniques (UID). Il supporte :

- **UUIDs** : Identifiants universellement uniques
- **ULIDs** : Identifiants lexicographiquement triables

## Types d'UUID disponibles

### UUID v1 (Time-based)
- Basé sur l'horodatage et l'adresse MAC
- Peut révéler des informations sur l'hôte

### UUID v3 (Name-based MD5)
- Généré à partir d'un namespace et d'un nom
- Utilise MD5 (moins recommandé)

### UUID v4 (Random)
- Complètement aléatoire
- Le plus couramment utilisé

### UUID v5 (Name-based SHA-1)
- Généré à partir d'un namespace et d'un nom
- Utilise SHA-1

### UUID v6 (Lexicographically sortable)
- Version améliorée de v1
- Triable lexicographiquement

### UUID v7 (UNIX timestamp-based) ⭐ **RECOMMANDÉ**
- Basé sur l'horodatage UNIX
- Meilleure entropie que v1
- **Utilisé dans notre projet**

### UUID v8 (Custom)
- Implémentation personnalisée

## Implementation dans le projet

### UuidGenerator

Notre générateur utilise UUID v7 pour ses avantages :

```php
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Generator;

use Symfony\Component\Uid\Uuid;

final class UuidGenerator implements GeneratorInterface
{
    #[\Override]
    public static function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
```

### Avantages d'UUID v7

1. **Triable** : Les UUIDs générés sont triables chronologiquement
2. **Performance** : Meilleure performance en base de données
3. **Entropie** : Bonne entropie tout en conservant l'ordre temporel
4. **Standard** : Format RFC 4122 compatible

## Utilisation

### Génération simple

```php
use App\Shared\Infrastructure\Generator\UuidGenerator;

$uuid = UuidGenerator::generate();
// Exemple: 01915c8a-b5d2-7034-8c5f-123456789abc
```

### Génération directe avec Symfony UID

```php
use Symfony\Component\Uid\Uuid;

// UUID v7 (recommandé)
$uuid = Uuid::v7();
echo $uuid->toRfc4122(); // 01915c8a-b5d2-7034-8c5f-123456789abc

// UUID v4 (aléatoire)
$uuid = Uuid::v4();
echo $uuid->toRfc4122(); // 550e8400-e29b-41d4-a716-446655440000

// ULID
use Symfony\Component\Uid\Ulid;
$ulid = new Ulid();
echo $ulid; // 01AN4Z07BY79KA1307SR9X4MV3
```

### Formats de sortie

```php
$uuid = Uuid::v7();

// Format RFC 4122 (recommandé)
echo $uuid->toRfc4122(); // 01915c8a-b5d2-7034-8c5f-123456789abc

// Format base32
echo $uuid->toBase32(); // 0C7QCE7TGTAYY3J1ESA9X4MV30

// Format base58
echo $uuid->toBase58(); // SmZjG8Z8kSKSC1n8nVfgDy

// Format binaire
echo $uuid->toBinary(); // Binary representation
```

## Intégration avec Doctrine

### Types Doctrine

Le composant fournit des types Doctrine pour une intégration transparente :

```php
// Dans une entité
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }
}
```

### Configuration Doctrine

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            uuid: 'Symfony\Component\Uid\Doctrine\UuidType'
            ulid: 'Symfony\Component\Uid\Doctrine\UlidType'
```

## ULIDs (Alternative aux UUIDs)

### Caractéristiques des ULIDs

- **128 bits** comme les UUIDs
- **26 caractères** de représentation
- **Triable lexicographiquement**
- **Monotonique** dans la même milliseconde

### Utilisation des ULIDs

```php
use Symfony\Component\Uid\Ulid;

$ulid = new Ulid();
echo $ulid; // 01AN4Z07BY79KA1307SR9X4MV3

// Depuis timestamp
$ulid = Ulid::fromDateTime(new \DateTime());

// Parsing
$ulid = Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3');
```

## Commandes Console

Le composant fournit des commandes pour générer et inspecter les UIDs :

```bash
# Générer des UUIDs
php bin/console debug:uuid

# Générer des ULIDs
php bin/console debug:ulid

# Inspecter un UID
php bin/console debug:uid 01915c8a-b5d2-7034-8c5f-123456789abc
```

## Bonnes pratiques

### ✅ Recommandations

1. **Utiliser UUID v7** pour les nouveaux projets
2. **Éviter UUID v1** (peut révéler des informations)
3. **Utiliser les types Doctrine** pour l'intégration ORM
4. **Considérer les ULIDs** pour les cas nécessitant un tri

### ⚠️ Précautions

1. **Performance des clés primaires** : Les UUIDs comme clés primaires peuvent impacter les performances
2. **Taille de stockage** : Les UUIDs prennent plus d'espace que les entiers
3. **Indexation** : Considérer l'impact sur les index de base de données

### 🚫 À éviter

1. **UUID v1 en production** (information d'hôte)
2. **UUID v3** (MD5 déprécié)
3. **Conversion de format inutile** (garder le format natif quand possible)

## Migration depuis Ramsey/UUID

Si vous migrez depuis ramsey/uuid :

```php
// Avant (Ramsey)
use Ramsey\Uuid\Uuid;
$uuid = Uuid::uuid4()->toString();

// Après (Symfony)
use Symfony\Component\Uid\Uuid;
$uuid = Uuid::v7()->toRfc4122();
```

### Avantages de la migration

1. **Intégration native** avec l'écosystème Symfony
2. **Types Doctrine inclus**
3. **Commandes console**
4. **Meilleure performance** avec UUID v7
5. **Maintenance** par l'équipe Symfony

## Références

- [Documentation officielle Symfony UID](https://symfony.com/doc/current/components/uid.html)
- [RFC 4122 - UUID Standard](https://tools.ietf.org/html/rfc4122)
- [ULID Specification](https://github.com/ulid/spec)