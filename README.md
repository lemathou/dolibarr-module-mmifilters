# MMIFilters — Filtres et actions de listes : assignation commerciale et filtres sur devis, commandes, factures et expéditions.

> Module Dolibarr **MMIFilters** (version 1.1), branche `20.0`.

## Fonctionnalités

### Fonctionnalités principales

- Action de masse « assigner un commercial » sur les listes de devis, commandes et factures (`actions_mmifilters`).
- Filtres de listes et test de filtre expédition (`test/functional/shipment_pro_filter_test.php`).

### Écrans et menus

- **MMI Filtres listes** — `/mmifilters/mmifiltersindex.php` (menu mmifilters, droit `1`)

### Pages

- `admin/about.php`
- `admin/setup.php`

### Configuration

| Constante | Type | Rôle |
|---|---|---|
| `MMIFILTERS_PRO_USE_SIREN` | yesno | Pour le filtre "Pro", rechercher aussi les tiers dont au moins un des champs SIREN, SIRET ou TVA INTRA est non vide |

### Hooks

- Contextes : `propallist`, `orderlist`, `invoicelist`, `shipmentlist`
- Callbacks : `printFieldPreListTitle`, `printFieldListWhere`, `proJoinClause`, `proWhereClause`, `printFieldListFrom`, `printFieldListJoinSoc`, `printFieldListJoinPropal`, `printFieldListJoinInvoice`, `printFieldListJoinCommande`, `printFieldListSearchParam`, `addMoreMassActions`, `doMassActions`, `doPreMassActions`

### Classes et composants

- `class/actions_mmifilters.class.php`
- `lib/mmifilters.lib.php`

## Prérequis

- Dolibarr >= 11.0 (branche `20.0` : Dolibarr 20.0).
- Modules MMI : `mmicommon`. **`mmicommon` doit être activé en premier.**

## Installation

1. Copier le module dans `htdocs/custom/mmifilters` :

   ```sh
   cd htdocs/custom
   git clone <dépôt> mmifilters
   ```

2. Dans Dolibarr : **Configuration → Modules**, activer d'abord `mmicommon`, puis ce module.
3. Vérifier les permissions dans la fiche des utilisateurs concernés.

## Licence

GPLv3 ou ultérieure — voir `COPYING`. Documentation sous GFDL.

## Changelog

Voir [CHANGELOG.md](CHANGELOG.md) (évolutions datées).
