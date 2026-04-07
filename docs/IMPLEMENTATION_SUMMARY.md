# Résumé de l'implémentation : Safe Doctrine Migrations

## Objectif

Modifier automatiquement la génération des migrations Doctrine pour ajouter des vérifications `hasTable()` et `hasColumn()` avant d'exécuter les requêtes SQL, permettant ainsi de rendre les migrations idempotentes.

## Solution implémentée

### Architecture : Custom SqlGenerator via Factory Pattern

Plutôt que de décorer le SqlGenerator (impossible car non exposé comme service), la solution utilise le système de **factories** de Doctrine Migrations pour injecter un SqlGenerator personnalisé dans le DependencyFactory.

### Fichiers créés

```
src/Doctrine/Migration/
├── ParsedQuery.php              (20 lignes)  - Structure de données
├── SqlParser.php                (110 lignes) - Parser SQL PostgreSQL
├── SafeSqlGenerator.php         (310 lignes) - Générateur avec vérifications
└── SafeSqlGeneratorFactory.php  (28 lignes)  - Factory pour DI

config/
├── packages/doctrine_migrations.yaml  (modifié) - Config factory
└── services/doctrine.php              (créé)    - Services Symfony

docs/
└── SAFE_MIGRATIONS.md          (180 lignes) - Documentation complète
```

**Total : ~648 lignes de code**

## Fonctionnement technique

### 1. Configuration Symfony (config/services/doctrine.php)

Enregistre les services nécessaires :
- `SqlParser` : service simple
- `SafeSqlGeneratorFactory` : factory avec injection de `SqlParser`, `Configuration` et `Connection`

### 2. Configuration Doctrine (doctrine_migrations.yaml)

Déclare la factory pour remplacer le SqlGenerator :

```yaml
factories:
    'Doctrine\Migrations\Generator\SqlGenerator': 'App\Doctrine\Migration\SafeSqlGeneratorFactory'
```

### 3. Flux d'exécution

```
doctrine:migrations:diff
    ↓
DependencyFactory::getMigrationSqlGenerator()
    ↓
SafeSqlGeneratorFactory::__invoke()
    ↓
SafeSqlGenerator::generate(array $sql)
    ↓
SqlParser::parse(string $sql)  ← Parse chaque requête
    ↓
generateSafeCode(ParsedQuery)   ← Génère le code avec vérifications
    ↓
Fichier migration.php créé
```

### 4. Exemple de transformation

**Input SQL** (généré par Doctrine) :
```sql
ALTER TABLE cv_project ADD visible BOOLEAN DEFAULT false NOT NULL
```

**Output PHP** (dans la migration) :
```php
if ($schema->hasTable('cv_project')) {
    $table = $schema->getTable('cv_project');
    if (!$table->hasColumn('visible')) {
        $this->addSql('ALTER TABLE cv_project ADD visible BOOLEAN DEFAULT false NOT NULL');
    }
}
```

## Opérations supportées

| Type SQL | Vérification |
|----------|--------------|
| ALTER TABLE ADD | `hasTable()` + `!hasColumn()` |
| ALTER TABLE DROP | `hasTable()` + `hasColumn()` |
| ALTER TABLE ALTER | `hasTable()` + `hasColumn()` |
| ALTER TABLE RENAME | `hasTable()` + `hasColumn()` |
| CREATE TABLE | `!hasTable()` |
| DROP TABLE | `hasTable()` |
| CREATE INDEX | `hasTable()` + `!hasIndex()` |
| DROP INDEX | `hasTable()` + `hasIndex()` |

## Tests effectués

✅ Cache Symfony compile sans erreur
✅ Script de test valide le parsing et la génération
✅ Code généré testé avec succès (voir test_safe_sql_generator.php)

## Exemple de résultat

Test avec 5 requêtes SQL différentes :

```php
// ALTER TABLE ADD
if ($schema->hasTable('cv_project')) {
    $table = $schema->getTable('cv_project');
    if (!$table->hasColumn('visible')) {
        $this->addSql('ALTER TABLE cv_project ADD visible BOOLEAN DEFAULT false NOT NULL');
    }
}

// ALTER TABLE DROP
if ($schema->hasTable('cv_project')) {
    $table = $schema->getTable('cv_project');
    if ($table->hasColumn('status')) {
        $this->addSql('ALTER TABLE cv_project DROP status');
    }
}

// CREATE INDEX
if ($schema->hasTable('cv_project')) {
    $table = $schema->getTable('cv_project');
    if (!$table->hasIndex('idx_visible')) {
        $this->addSql('CREATE INDEX idx_visible ON cv_project (visible)');
    }
}

// DROP TABLE
if ($schema->hasTable('old_table')) {
    $this->addSql('DROP TABLE old_table');
}

// CREATE TABLE
if (!$schema->hasTable('new_table')) {
    $this->addSql('CREATE TABLE new_table (id INT)');
}
```

## Avantages de cette approche

✅ **Transparent** : Aucun changement dans le workflow (`doctrine:migrations:diff` fonctionne normalement)
✅ **Automatique** : Toutes les futures migrations bénéficient des vérifications
✅ **Robuste** : Fallback au comportement standard si parsing échoue
✅ **Idempotent** : Les migrations peuvent être rejouées sans erreur
✅ **Maintenable** : Code bien structuré et documenté
✅ **Testable** : Parser et générateur indépendants

## Limitations connues

⚠️ **Requêtes complexes** : Les requêtes multi-tables ou très spécifiques ne sont pas parsées
⚠️ **PostgreSQL focus** : Patterns optimisés pour PostgreSQL (facilement adaptable)
⚠️ **Overhead minimal** : Léger temps de traitement supplémentaire lors de la génération

## Utilisation

```bash
# Générer une migration (avec vérifications automatiques)
php bin/console doctrine:migrations:diff

# Exécuter les migrations (idempotent maintenant)
php bin/console doctrine:migrations:migrate
```

## Désactivation

Pour désactiver temporairement, commenter dans `doctrine_migrations.yaml` :

```yaml
# factories:
#     'Doctrine\Migrations\Generator\SqlGenerator': 'App\Doctrine\Migration\SafeSqlGeneratorFactory'
```

## Conclusion

✅ **Mission accomplie** : Les migrations Doctrine sont maintenant générées avec des vérifications automatiques de l'existence des tables, colonnes et index avant d'exécuter les requêtes SQL.

Cette solution est production-ready et ne nécessite aucun changement dans le workflow de développement existant.
