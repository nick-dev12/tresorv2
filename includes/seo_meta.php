<?php
/**
 * Meta tags SEO pour le référencement
 * Usage: include avec les variables $seo_title, $seo_description, $seo_keywords (optionnel), $seo_image (optionnel), $seo_canonical (optionnel), $seo_noindex (optionnel)
 */
include __DIR__ . '/favicon.php';
if (!function_exists('get_site_base_url')) {
    require_once __DIR__ . '/site_url.php';
}
$base = get_site_base_url();
$seo_title = isset($seo_title) ? $seo_title : 'Trésor Africain - Produits naturels';
$seo_description = isset($seo_description) ? $seo_description : 'Trésor Africain : produits naturels issus de la production locale. Noix, huiles, céréales, fruits et plus encore.';
$seo_keywords = isset($seo_keywords) ? $seo_keywords : 'produits naturels, Trésor Africain, épicerie fine, noix, huiles, céréales, Sénégal';
$seo_image = isset($seo_image) ? $seo_image : $base . '/image/logo.jpeg';
$seo_canonical = isset($seo_canonical) ? $seo_canonical : '';
$seo_noindex = isset($seo_noindex) && $seo_noindex;
$seo_og_type = isset($seo_og_type) ? $seo_og_type : 'website';
?>
<title><?php echo htmlspecialchars($seo_title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($seo_keywords); ?>">
<meta name="robots" content="<?php echo $seo_noindex ? 'noindex, nofollow' : 'index, follow'; ?>">
<?php if ($seo_canonical): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($seo_canonical); ?>">
<?php endif; ?>
<!-- Open Graph / Facebook -->
<meta property="og:type" content="<?php echo htmlspecialchars($seo_og_type); ?>">
<meta property="og:url"
    content="<?php echo htmlspecialchars($seo_canonical ?: $base . ($_SERVER['REQUEST_URI'] ?? '/')); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($seo_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($seo_description); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($seo_image); ?>">
<meta property="og:locale" content="fr_FR">
<meta property="og:site_name" content="Trésor Africain">
<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo htmlspecialchars($seo_title); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($seo_description); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($seo_image); ?>">