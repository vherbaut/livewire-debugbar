# 🚀 Livewire Debugbar

Une debugbar avancée pour Laravel Livewire avec hot reload intelligent, manipulation d'événements en temps réel et monitoring de performance.

## ✨ Fonctionnalités

- 🔍 **Inspection complète** des composants Livewire
- 🔥 **Hot reload intelligent** (sans WebSocket)
- 📡 **Gestion d'événements** avancée avec replay
- 📊 **Monitoring de performance** avec alertes
- 🎨 **Interface moderne** avec Alpine.js + Tailwind

## 🚀 Installation rapide

### 1. Installation via Composer

```bash
composer require votre-vendor/livewire-debugbar --dev
```

### 2. Publier les assets

```bash
# Configuration
php artisan vendor:publish --tag=livewire-debugbar-config

# Assets CSS/JS
php artisan vendor:publish --tag=livewire-debugbar-assets
```

### 3. Configuration

```env
# .env
LIVEWIRE_DEBUGBAR_ENABLED=true
LIVEWIRE_DEBUGBAR_HOT_RELOAD=true
```

### 4. Compiler les assets (optionnel)

Si vous voulez customiser l'interface :

```bash
npm install
npm run build
```

## 💻 Développement

### Build des assets

```bash
# Installation
npm install

# Développement avec watch
npm run dev

# Build de production
npm run build

# Watch mode
npm run watch
```

### Structure du package (Spatie Package Tools)

```
src/
├── LivewireDebugbarServiceProvider.php    # ServiceProvider principal
├── DebugbarCollector.php                  # Collecteur de données
├── Commands/LivewireDebugbarCommand.php   # Commande artisan
├── Http/
│   ├── Middleware/DebugbarMiddleware.php  # Injection automatique
│   └── Controllers/FileWatcherController.php # Hot reload endpoint
└── Listeners/ComponentListener.php        # Événements Livewire

config/livewire-debugbar.php              # Configuration
routes/web.php                            # Routes du package
resources/
├── views/debugbar.blade.php              # Vue principale
├── js/app.ts                             # Logique TypeScript
├── css/app.css                           # Styles Tailwind
└── dist/                                 # Assets compilés
```

## 🎮 Utilisation

### Interface principale

La debugbar apparaît automatiquement en bas de page avec 4 onglets :

1. **Composants** - Inspection et manipulation des propriétés
2. **Événements** - Lifecycle, Dispatched, Dispatch New
3. **Performance** - Métriques et alertes
4. **Hot Reload** - Configuration et logs

### Raccourcis clavier

- `Ctrl+Shift+D` : Toggle debugbar
- `Ctrl+Shift+C` : Clear données
- `Ctrl+Shift+R` : Rechargement manuel
- `Ctrl+Shift+H` : Toggle hot reload

### Commande Artisan

```bash
# Vérifier le statut
php artisan livewire-debugbar:status
```

## 🔧 Configuration

```php
// config/livewire-debugbar.php
return [
    'enabled' => env('LIVEWIRE_DEBUGBAR_ENABLED', env('APP_DEBUG', false)),
    'position' => 'bottom', // bottom, top, left, right
    
    'hot_reload' => [
        'enabled' => true,
        'interval' => 3000, // ms
        'watch_paths' => [
            'app/Livewire',
            'resources/views/livewire',
        ],
    ],
    
    'thresholds' => [
        'max_properties' => 50,
        'max_serialized_size' => 10240, // 10KB
        'slow_render_time' => 100, // ms
    ],
];
```

## 🎯 Fonctionnalités détaillées

### Hot Reload intelligent

- ✅ **Surveillance des fichiers** via hash MD5
- ✅ **Rechargement sélectif** des composants modifiés
- ✅ **100% côté client** (pas de WebSocket)
- ✅ **Détection d'erreurs** automatique

```php
// Modifiez app/Livewire/UserProfile.php
// → Seul UserProfile se recharge via $wire.$refresh()
// → Les autres composants gardent leur état
```

### Gestion d'événements

- ✅ **Capture automatique** de tous les dispatch()
- ✅ **Replay instantané** avec mêmes paramètres
- ✅ **Interface de création** d'événements de test
- ✅ **100% côté client** avec APIs Livewire

```php
// Ces événements sont automatiquement capturés
$this->dispatch('userSaved', userId: $user->id);
$this->dispatchTo('notification', 'show', 'Message');
$this->dispatchSelf('resetForm');
```

### Alertes de performance

- ⚠️ **Trop de propriétés** (>50)
- 🔴 **Données volumineuses** (>10KB)
- 🐌 **Rendu lent** (>100ms)
- 📊 **Trop de requêtes** (>10)

## 🧪 Tests

```bash
# Tests PHP
./vendor/bin/pest

# Tests avec couverture
./vendor/bin/pest --coverage

# Analyse statique
./vendor/bin/phpstan analyse
```

## 📚 Documentation

Consultez le dossier `docs/` pour plus de détails :

- `docs/installation.md` - Installation détaillée
- `docs/events.md` - Gestion des événements
- `docs/hot-reload.md` - Configuration hot reload
- `docs/performance.md` - Monitoring de performance

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature
3. Commit vos changements
4. Push vers la branche
5. Ouvrir une Pull Request

## 📄 Licence

MIT License

## 🙋 Support

- GitHub Issues
- GitHub Discussions
- Email : support@example.com

---

**Fait avec ❤️ pour la communauté Laravel**
