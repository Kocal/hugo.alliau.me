# Safe Doctrine Migrations Generator

Ce projet inclut un générateur personnalisé pour les migrations Doctrine qui ajoute automatiquement des vérifications de sécurité (`hasTable()`, `hasColumn()`, `hasIndex()`) avant d'exécuter les requêtes SQL.

## Pourquoi ?

Dans certains contextes, les migrations peuvent être exécutées sur des bases de données ayant des états différents :
- Déploiements sur plusieurs environnements désynchronisés
- Migrations rejouées plusieurs fois
- Bases de données avec des historiques de migrations différents

Cette fonctionnalité permet de rendre les migrations **idempotentes** : elles peuvent être exécutées plusieurs fois sans erreur.

## Comment ça marche ?

### Architecture

Le système est composé de 4 composants principaux :

1. **`ParsedQuery`** : Structure de données représentant une requête SQL parsée
2. **`SqlParser`** : Parse les requêtes SQL PostgreSQL pour extraire les métadonnées (table, colonne, index)
3. **`SafeSqlGenerator`** : Étend le `SqlGenerator` de Doctrine pour générer du code avec vérifications
4. **`SafeSqlGeneratorFactory`** : Factory pour instancier le `SafeSqlGenerator`

### Exemple de code généré

#### Avant (standard Doctrine)
```php
public function up(Schema $schema): void
{
    $this->addSql('ALTER TABLE cv_project ADD visible BOOLEAN DEFAULT false NOT NULL');
    $this->addSql('CREATE INDEX idx_visible ON cv_project (visible)');
}
```

#### Après (avec SafeSqlGenerator)
```php
public function up(Schema $schema): void
{
    if ($schema->hasTable('cv_project')) {
        $table = $schema->getTable('cv_project');
        if (!$table->hasColumn('visible')) {
            $this->addSql('ALTER TABLE cv_project ADD visible BOOLEAN DEFAULT false NOT NULL');
        }
        
        if (!$table->hasIndex('idx_visible')) {
            $this->addSql('CREATE INDEX idx_visible ON cv_project (visible)');
        }
    }
}
```

## Opérations SQL supportées

Le parser reconnaît et traite les opérations SQL suivantes :

| Opération | Vérification générée | Exemple SQL |
|-----------|---------------------|-------------|
| `ALTER TABLE ... ADD COLUMN` | `hasTable()` + `!hasColumn()` | `ALTER TABLE users ADD email VARCHAR(255)` |
| `ALTER TABLE ... DROP COLUMN` | `hasTable()` + `hasColumn()` | `ALTER TABLE users DROP email` |
| `ALTER TABLE ... ALTER COLUMN` | `hasTable()` + `hasColumn()` | `ALTER TABLE users ALTER COLUMN email TYPE TEXT` |
| `ALTER TABLE ... RENAME COLUMN` | `hasTable()` + `hasColumn()` | `ALTER TABLE users RENAME COLUMN email TO mail` |
| `CREATE TABLE` | `!hasTable()` | `CREATE TABLE users (id INT)` |
| `DROP TABLE` | `hasTable()` | `DROP TABLE users` |
| `CREATE INDEX` | `hasTable()` + `!hasIndex()` | `CREATE INDEX idx_email ON users (email)` |
| `DROP INDEX` | `hasTable()` + `hasIndex()` | `DROP INDEX idx_email` |

## Configuration

La configuration est automatique et définie dans :

### `config/services/doctrine.php`
```php
$services->set(SqlParser::class);
$services->set('App\Doctrine\Migration\SafeSqlGeneratorFactory')
    ->arg('$parser', service(SqlParser::class))
    ->arg('$configuration', service('doctrine.migrations.configuration'))
    ->arg('$connection', service('doctrine.dbal.default_connection'));
```

### `config/packages/doctrine_migrations.yaml`
```yaml
doctrine_migrations:
    factories:
        'Doctrine\Migrations\Generator\SqlGenerator': 'App\Doctrine\Migration\SafeSqlGeneratorFactory'
```

## Utilisation

Aucun changement dans votre workflow ! Utilisez les commandes Doctrine habituelles :

```bash
# Générer une migration basée sur les différences de schéma
php bin/console doctrine:migrations:diff

# Générer une migration vide
php bin/console doctrine:migrations:generate

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

Les migrations générées incluront automatiquement les vérifications de sécurité.

## Désactivation (si nécessaire)

Pour désactiver temporairement cette fonctionnalité, commentez la section `factories` dans `config/packages/doctrine_migrations.yaml` :

```yaml
doctrine_migrations:
    # factories:
    #     'Doctrine\Migrations\Generator\SqlGenerator': 'App\Doctrine\Migration\SafeSqlGeneratorFactory'
```

## Limitations

- **Requêtes complexes** : Les requêtes SQL très complexes ou multi-tables peuvent ne pas être reconnues et seront générées sans vérifications (fallback au comportement standard)
- **PostgreSQL focus** : Les patterns de parsing sont optimisés pour PostgreSQL. Pour d'autres SGBD, des adaptations pourraient être nécessaires
- **Performance** : Léger overhead lors de la génération (négligeable en pratique)

## Structure des fichiers

```
src/Doctrine/Migration/
├── ParsedQuery.php              # Structure de données
├── SqlParser.php                # Parser SQL
├── SafeSqlGenerator.php         # Générateur avec vérifications
└── SafeSqlGeneratorFactory.php  # Factory

config/
├── packages/doctrine_migrations.yaml  # Configuration Doctrine
└── services/doctrine.php              # Services Symfony
```

## Tests

Un script de test est disponible pour vérifier le fonctionnement :

```bash
php -r "
require 'vendor/autoload.php';
use App\Doctrine\Migration\SqlParser;
\$parser = new SqlParser();
\$result = \$parser->parse('ALTER TABLE users ADD email VARCHAR(255)');
var_dump(\$result);
"
```

## Contribuer

Pour ajouter le support d'un nouveau type d'opération SQL :

1. Ajoutez un pattern dans `SqlParser::PATTERN_*`
2. Ajoutez un case dans `SqlParser::parse()`
3. Ajoutez une méthode `generate*Code()` dans `SafeSqlGenerator`
4. Ajoutez un case dans `SafeSqlGenerator::generateSafeCode()`
