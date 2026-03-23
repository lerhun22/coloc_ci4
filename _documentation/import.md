# COLOC V3 — Documentation Import

## 1. Objectif

Le module Import permet de créer ou rafraîchir une compétition à partir :

- d’un ZIP local
- du serveur COPAINS

Le système garantit :

- pas de doublons
- suppression propre avant refresh
- cohérence DB + fichiers
- compatibilité jugement

---

## 2. Stratégie générale

Toujours :

```
IMPORT = DELETE + INSERT
```

Jamais update.

Raison :

- les IDs viennent de COPAINS
- notes liées aux photos
- photos liées à compétition
- classement lié compétition

Donc :

```
refresh = deleteCompetition + import
```

---

## 3. Routes

```
/import           → ZIP
/import/zip/run   → ZIP import

/import/copain    → COPAINS
/import/copain/run → COPAINS import
```

Routes.php :

```
import
import/zip/run
import/copain
import/copain/run
```

---

## 4. Controllers

### Import.php

Import ZIP local

Fonctions :

```
index()
run()
```

---

### ImportFromCopain.php

Import serveur fédéral

Fonctions :

```
index()
run()
```

Utilise :

```
CopainClient
CopainImporter
CompetitionService
```

---

## 5. Libraries

### CopainClient

Responsable :

- login
- cookies
- appels API
- download JSON
- generate zip

⚠ ne pas modifier

---

### CopainImporter

Responsable :

- import complet
- transaction DB
- delete si existe
- insert tables
- unzip photos

Tables :

```
competitions
juges
photos
notes
medailles
```

---

### CompetitionCleaner

Responsable :

supprimer une compétition :

```
notes
classements
classementclubs
classementauteurs
medailles
juges
photos
competitions
uploads folder
```

Important :

ne pas supprimer participants / clubs

---

## 6. Services

### CompetitionService

Responsable :

```
getActive()
setActive()
```

stocke compétition active.

---

## 7. Dossiers

```
public/uploads/competitions/{id}
```

Contenu :

```
csv
photos
thumbs
pdf
pte
etiquettes
```

Nom dossier = id compétition

Important pour refresh.

---

## 8. Workflow import COPAINS

```
ImportFromCopain::run
  → CopainClient login
  → CopainImporter importCompetition
      → deleteCompetition
      → insert DB
      → unzip
  → setActive
  → redirect
```

---

## 9. Workflow import ZIP

```
Import::run
  → unzip
  → insert DB
  → create folder
```

---

## 10. Règles importantes

Toujours :

```
delete avant import
transaction DB
dossier unique
id = copains id
```

Ne jamais :

```
update lignes
changer id
supprimer participants
supprimer clubs
```

---

## 11. Tests obligatoires

Avant concours :

```
import zip
refresh zip
import copain
refresh copain
photos ok
notes ok
juges ok
```

---

## 12. Version

COLOC V3
Import stable
Refresh sécurisé
Compatible jugement
