<?php
/**
 * Page d'accueil du tableau de bord administrateur
 * Programmation procédurale uniquement
 */

session_start();

// Vérifier si l'admin est connecté, sinon rediriger vers la page de connexion
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/model_commandes_admin.php';
require_once __DIR__ . '/../models/model_commandes_personnalisees.php';
require_once __DIR__ . '/../models/model_produits.php';
require_once __DIR__ . '/../models/model_categories.php';

$recherche = trim($_GET['recherche'] ?? '');
$categorie_id = isset($_GET['categorie_id']) ? (int) $_GET['categorie_id'] : 0;
$categories = get_all_categories();
$produits = get_all_produits();

if (!empty($produits)) {
    $produits = array_values(array_filter($produits, function ($produit) use ($recherche, $categorie_id) {
        if ($categorie_id > 0 && (int) ($produit['categorie_id'] ?? 0) !== $categorie_id) {
            return false;
        }

        if ($recherche === '') {
            return true;
        }

        $needle = function_exists('mb_strtolower') ? mb_strtolower($recherche) : strtolower($recherche);
        $haystacks = [
            $produit['nom'] ?? '',
            $produit['description'] ?? '',
            $produit['categorie_nom'] ?? '',
            $produit['statut'] ?? ''
        ];

        foreach ($haystacks as $value) {
            $value = function_exists('mb_strtolower') ? mb_strtolower((string) $value) : strtolower((string) $value);
            if (strpos($value, $needle) !== false) {
                return true;
            }
        }

        return false;
    }));
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Administration Sugar Paper</title>
    <?php require_once __DIR__ . '/../includes/asset_version.php'; ?>
    <?php include __DIR__ . '/../includes/pwa_meta.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin-dashboard.css<?php echo asset_version_query(); ?>">
    <style>
        .admin-filters-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: 20px;
            padding: 16px;
            background: #fff;
            border: 1px solid #ececec;
            border-radius: 12px;
        }

        .admin-filter-field {
            flex: 1 1 220px;
        }

        .admin-filter-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #6b2f20;
        }

        .admin-filter-field input,
        .admin-filter-field select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d9d9d9;
            border-radius: 10px;
            background: #fff;
        }

        .admin-filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-filter-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 16px;
            border-radius: 10px;
            border: 1px solid #d9d9d9;
            color: #6b2f20;
            background: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        .produit-card-linkable {
            cursor: pointer;
        }

        .produit-card-linkable:hover .produit-card-nom {
            color: #918a44;
        }

        /* En-tête tableau de bord — hero + barre d’actions */
        .content-header.content-header--dashboard {
            display: block;
            padding: 0;
            overflow: hidden;
            border: none;
            margin-bottom: 28px;
            background: linear-gradient(135deg,
                rgba(194, 102, 56, 0.08) 0%,
                rgba(255, 255, 255, 1) 45%,
                rgba(145, 138, 68, 0.06) 100%);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .dashboard-hero-inner {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: clamp(20px, 4vw, 36px);
            align-items: center;
            padding: clamp(20px, 4vw, 32px) clamp(18px, 3vw, 32px);
            position: relative;
        }

        .dashboard-hero-inner::before {
            content: "";
            position: absolute;
            left: 0;
            top: 12%;
            bottom: 12%;
            width: 4px;
            border-radius: 4px;
            background: linear-gradient(180deg, var(--couleur-dominante), var(--boutons-secondaires));
        }

        .dashboard-hero-head {
            padding-left: 16px;
            min-width: 0;
        }

        .dashboard-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--couleur-dominante);
            background: rgba(194, 102, 56, 0.12);
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 12px;
        }

        .dashboard-hero-title {
            font-family: var(--font-titres);
            font-size: clamp(1.45rem, 3.5vw, 2rem);
            font-weight: 700;
            color: var(--titres);
            margin: 0 0 8px 0;
            line-height: 1.2;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .dashboard-hero-title i {
            color: var(--couleur-dominante);
            font-size: 0.95em;
        }

        .dashboard-hero-desc {
            margin: 0;
            font-size: clamp(0.9rem, 2vw, 1rem);
            color: #444;
            line-height: 1.55;
            max-width: 36em;
        }

        .dashboard-hero-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
            align-items: center;
            align-content: center;
        }

        .dashboard-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            white-space: nowrap;
        }

        .dashboard-btn:focus-visible {
            outline: 2px solid var(--couleur-dominante);
            outline-offset: 2px;
        }

        .dashboard-btn--ghost {
            background: #fff;
            color: var(--titres);
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .dashboard-btn--ghost:hover {
            border-color: var(--couleur-dominante);
            color: var(--couleur-dominante);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(194, 102, 56, 0.15);
        }

        .dashboard-btn--secondary {
            background: var(--boutons-secondaires);
            color: #fff;
            box-shadow: 0 2px 8px rgba(145, 138, 68, 0.35);
        }

        .dashboard-btn--secondary:hover {
            filter: brightness(1.05);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(145, 138, 68, 0.4);
            color: #fff;
        }

        .dashboard-btn--primary {
            background: var(--couleur-dominante);
            color: #fff;
            padding-left: 22px;
            padding-right: 22px;
            box-shadow: 0 4px 14px rgba(194, 102, 56, 0.4);
        }

        .dashboard-btn--primary:hover {
            filter: brightness(1.06);
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(194, 102, 56, 0.45);
            color: #fff;
        }

        #btn-enable-notifications.notifications-enabled.dashboard-btn--ghost {
            background: rgba(145, 138, 68, 0.15);
            border-color: var(--boutons-secondaires);
            color: var(--marron-accent);
            cursor: default;
        }

        #btn-enable-notifications.notifications-enabled.dashboard-btn--ghost:hover {
            transform: none;
        }

        @media (max-width: 900px) {
            .dashboard-hero-inner {
                grid-template-columns: 1fr;
                align-items: stretch;
            }

            .dashboard-hero-toolbar {
                justify-content: flex-start;
            }
        }

        @media (max-width: 600px) {
            .dashboard-hero-inner::before {
                top: 8%;
                bottom: 8%;
            }

            .dashboard-hero-head {
                padding-left: 12px;
            }

            .dashboard-btn {
                flex: 1 1 calc(50% - 6px);
                min-width: 0;
                white-space: normal;
                text-align: center;
            }

            .dashboard-btn--primary {
                flex: 1 1 100%;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/nav.php'; ?>

    <!-- Barre de navigation verticale -->

    <!-- Contenu principal -->
    <div class="contents-container">
        <div class="content-header content-header--dashboard">
            <div class="dashboard-hero-inner">
                <header class="dashboard-hero-head">
                    <span class="dashboard-hero-badge"><i class="fas fa-shield-halved" aria-hidden="true"></i> Espace administrateur</span>
                    <h1 class="dashboard-hero-title"><i class="fas fa-chart-line" aria-hidden="true"></i> Tableau de bord</h1>
                    <p class="dashboard-hero-desc">Vue d’ensemble de votre activité : commandes, catalogue et livraisons, en un coup d’œil.</p>
                </header>
                <div class="dashboard-hero-toolbar" role="toolbar" aria-label="Actions rapides">
                    <button type="button" id="btn-install-pwa" class="dashboard-btn dashboard-btn--ghost"
                        title="Installer l'application Sugar Paper sur cet appareil" style="display: none;">
                        <i class="fas fa-download" aria-hidden="true"></i> Installer l’app
                    </button>
                    <button type="button" id="btn-enable-notifications" class="dashboard-btn dashboard-btn--ghost"
                        title="Recevoir des notifications push pour les nouvelles commandes">
                        <i class="fas fa-bell" aria-hidden="true"></i> Notifications
                    </button>
                    <a href="zones-livraison/index.php" class="dashboard-btn dashboard-btn--secondary">
                        <i class="fas fa-truck" aria-hidden="true"></i> Zones de livraison
                    </a>
                    <a href="produits/ajouter.php" class="dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-plus" aria-hidden="true"></i> Nouveau produit
                    </a>
                </div>
            </div>
        </div>

        <?php
        if (isset($_SESSION['notification_test_message'])) {
            $test_msg = $_SESSION['notification_test_message'];
            $test_type = $_SESSION['notification_test_type'] ?? 'success';
            unset($_SESSION['notification_test_message'], $_SESSION['notification_test_type']);
            ?>
            <div class="alert-box message-<?php echo htmlspecialchars($test_type); ?>" style="margin-bottom: 20px;">
                <p><i class="fas fa-<?php echo $test_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($test_msg); ?></p>
            </div>
            <?php
        }
        // Récupérer les statistiques des commandes
        $total_commandes = count_commandes_by_statut();
        $commandes_perso_en_attente = count_commandes_personnalisees_by_statut('en_attente');
        $en_attente = count_commandes_by_statut('en_attente');
        $prise_en_charge = count_commandes_by_statut('prise_en_charge');
        $livraison_en_cours = count_commandes_by_statut('livraison_en_cours');
        ?>

        <!-- Statistiques des commandes -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Commandes</h3>
                <div class="stat-value"><?php echo $total_commandes; ?></div>
            </div>
            <div class="stat-card stat-en-attente">
                <h3>En Attente</h3>
                <div class="stat-value"><?php echo $en_attente; ?></div>
            </div>
            <div class="stat-card stat-prise">
                <h3>Prise en charge</h3>
                <div class="stat-value"><?php echo $prise_en_charge; ?></div>
            </div>
            <div class="stat-card stat-livraison">
                <h3>Livraison en cours</h3>
                <div class="stat-value"><?php echo $livraison_en_cours; ?></div>
            </div>
        </div>

        <!-- Lien rapide vers les commandes -->
        <?php if ($en_attente > 0 || $prise_en_charge > 0): ?>
            <div class="alert-box">
                <p>
                    <i class="fas fa-exclamation-circle"></i>
                    <?php if ($en_attente > 0): ?>
                        <?php echo $en_attente; ?> commande<?php echo $en_attente > 1 ? 's' : ''; ?> en attente de prise en
                        charge
                    <?php elseif ($prise_en_charge > 0): ?>
                        <?php echo $prise_en_charge; ?> commande<?php echo $prise_en_charge > 1 ? 's' : ''; ?>
                        prise<?php echo $prise_en_charge > 1 ? 's' : ''; ?> en charge,
                        prête<?php echo $prise_en_charge > 1 ? 's' : ''; ?> à être
                        expédiée<?php echo $prise_en_charge > 1 ? 's' : ''; ?>
                    <?php endif; ?>
                </p>
                <a href="commandes/index.php" class="btn-alert">
                    <i class="fas fa-arrow-right"></i> Gérer les commandes
                </a>
            </div>
        <?php endif; ?>

        <?php if ($commandes_perso_en_attente > 0): ?>
            <div class="alert-box" style="margin-top: 15px;">
                <p>
                    <i class="fas fa-palette"></i>
                    <?php echo $commandes_perso_en_attente; ?>
                    commande<?php echo $commandes_perso_en_attente > 1 ? 's' : ''; ?>
                    personnalisée<?php echo $commandes_perso_en_attente > 1 ? 's' : ''; ?> en attente
                </p>
                <a href="commandes-personnalisees/index.php" class="btn-alert">
                    <i class="fas fa-arrow-right"></i> Voir les commandes personnalisées
                </a>
            </div>
        <?php endif; ?>

        <!-- Section produits -->
        <section class="produits-section">
            <div class="section-title">
                <h2><i class="fas fa-box"></i> Mes Produits (<?php echo count($produits); ?>)</h2>
            </div>

            <form method="GET" action="" class="admin-filters-bar">
                <div class="admin-filter-field">
                    <label for="recherche">Recherche</label>
                    <input type="text" id="recherche" name="recherche" placeholder="Nom, description, statut..."
                        value="<?php echo htmlspecialchars($recherche); ?>">
                </div>
                <div class="admin-filter-field">
                    <label for="categorie_id">Catégorie</label>
                    <select id="categorie_id" name="categorie_id">
                        <option value="0">Toutes les catégories</option>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?php echo (int) $categorie['id']; ?>"
                                <?php echo $categorie_id === (int) $categorie['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categorie['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-filter-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="dashboard.php" class="btn-filter-reset">
                        <i class="fas fa-rotate-left"></i>&nbsp;Réinitialiser
                    </a>
                </div>
            </form>

            <?php if (empty($produits)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Aucun produit enregistré pour le moment.</p>
                    <a href="produits/ajouter.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter le premier produit
                    </a>
                </div>
            <?php else: ?>
                <!-- Grille de produits -->
                <div class="produits-grid">
                    <?php foreach ($produits as $produit): ?>
                        <?php
                        $statut_class = 'statut-actif';
                        if ($produit['statut'] == 'inactif') {
                            $statut_class = 'statut-inactif';
                        } elseif ($produit['statut'] == 'rupture_stock') {
                            $statut_class = 'statut-rupture';
                        }
                        $statut_label = ucfirst(str_replace('_', ' ', $produit['statut']));
                        ?>
                        <div class="produit-card produit-card-linkable"
                            data-href="produits/modifier.php?id=<?php echo (int) $produit['id']; ?>">
                            <span class="statut-badge <?php echo $statut_class; ?>"><?php echo $statut_label; ?></span>
                            <img src="/upload/<?php echo htmlspecialchars($produit['image_principale']); ?>"
                                alt="<?php echo htmlspecialchars($produit['nom']); ?>" class="produit-card-image"
                                onerror="this.src='/image/produit1.jpg'">
                            <div class="produit-card-body">
                                <h3 class="produit-card-nom"><?php echo htmlspecialchars($produit['nom']); ?></h3>
                                <p class="produit-card-categorie">
                                    <?php echo htmlspecialchars($produit['categorie_nom'] ?? 'Sans catégorie'); ?>
                                </p>
                                <p class="produit-card-prix">
                                    <?php echo number_format($produit['prix'], 0, ',', ' '); ?>
                                    <span class="prix-unite">FCFA</span>
                                    <?php if ($produit['prix_promotion']): ?>
                                        <span class="prix-promo">
                                            (Promo: <?php echo number_format($produit['prix_promotion'], 0, ',', ' '); ?> FCFA)
                                        </span>
                                    <?php endif; ?>
                                </p>
                                <p class="produit-card-stock">
                                    Stock: <span class="stock-value"><?php echo $produit['stock']; ?></span>

                                </p>
                                <div class="produit-card-actions">
                                    <a href="produits/modifier.php?id=<?php echo $produit['id']; ?>" class="btn-card btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="produits/supprimer.php?id=<?php echo $produit['id']; ?>"
                                        class="btn-card btn-delete"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script src="https://www.gstatic.com/firebasejs/12.9.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/12.9.0/firebase-messaging-compat.js"></script>
    <?php require_once __DIR__ . '/../includes/firebase_init.php'; ?>
    <script>
        if (window.FIREBASE_CONFIG) {
            firebase.initializeApp(window.FIREBASE_CONFIG);
        }
    </script>
    <script src="/js/firebase-notifications.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.produit-card-linkable').forEach(function(card) {
                card.addEventListener('click', function(event) {
                    if (event.target.closest('a, button, input, select, textarea, form')) {
                        return;
                    }
                    var href = card.getAttribute('data-href');
                    if (href) {
                        window.location.href = href;
                    }
                });
            });

            var btn = document.getElementById('btn-enable-notifications');
            if (btn) {
                btn.addEventListener('click', function () {
                    if (typeof FirebaseNotifications !== 'undefined') {
                        FirebaseNotifications.enable('admin', this);
                    } else {
                        alert(
                            'Erreur: Les scripts de notification ne sont pas chargés. Vérifiez la console (F12).'
                        );
                    }
                });
            }

            var installBtn = document.getElementById('btn-install-pwa');
            var deferredPrompt;

            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
                if (installBtn) installBtn.style.display = 'none';
            } else {
                window.addEventListener('beforeinstallprompt', function (e) {
                    e.preventDefault();
                    deferredPrompt = e;
                    if (installBtn) installBtn.style.display = 'inline-flex';
                });

                if (installBtn) {
                    installBtn.addEventListener('click', function () {
                        if (!deferredPrompt) {
                            alert(
                                'L\'installation n\'est pas disponible. Essayez depuis Chrome ou Edge en mode HTTPS.'
                            );
                            return;
                        }
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then(function (choiceResult) {
                            if (choiceResult.outcome === 'accepted') {
                                installBtn.style.display = 'none';
                            }
                            deferredPrompt = null;
                        });
                    });
                }
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>