<footer class="footer">
    <div class="container footer_container">
        <div class="footer_item">
            <a href="/index.php" class="footer_logo">
                <img src="/image/logo.jpeg" alt="Trésor Africain" class="footer_logo_img"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                <span class="footer_logo_fallback"><i class="fas fa-leaf"></i> Trésor Africain</span>
            </a>
            <div class="footer_p">
                Trésor Africain à votre service
            </div>
        </div>
        <div class="footer_item">
            <h3 class="footer_item_titl">Contact</h3>
            <ul class="footer_list">
                <li class="li footer_list_item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:service@tresorafricain.com">service@tresorafricain.com</a>
                </li>
            </ul>
        </div>
        <div class="footer_item">
            <h3 class="footer_item_titl">Liens rapides</h3>
            <ul class="footer_list">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])): ?>
                    <li class="li footer_list_item">
                        <a href="/user/mon-compte.php">Mon compte</a>
                    </li>
                    <li class="li footer_list_item">
                        <a href="/user/deconnexion.php">Déconnexion</a>
                    </li>
                <?php else: ?>
                    <li class="li footer_list_item">
                        <a href="/user/connexion.php">Connexion</a>
                    </li>
                    <li class="li footer_list_item">
                        <a href="/user/inscription.php">Inscription</a>
                    </li>
                <?php endif; ?>
                <li class="li footer_list_item">
                    <a href="/panier.php">Panier</a>
                </li>
                <li class="li footer_list_item">
                    <a href="/produits.php">Produits</a>
                </li>
            </ul>
        </div>
        <div class="footer_item">
            <h3 class="footer_item_titl">Informations légales</h3>
            <ul class="footer_list">
                <li class="li footer_list_item">
                    <a href="/politique-confidentialite.php">Politique de confidentialité</a>
                </li>
                <li class="li footer_list_item">
                    <a href="/conditions-utilisation.php">Conditions d'utilisation</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="footer_bottom">
        <div class="container footer_bottom_container">
            <p class="footer_copy">
                2026 Trésor Africain | Tous droits réservés
            </p>
        </div>
    </div>
</footer>
<?php include __DIR__ . '/includes/social_floating.php'; ?>