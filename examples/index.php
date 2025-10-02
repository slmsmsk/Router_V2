<?php

declare(strict_types=1);

require __DIR__ . '/../src/Router.php';

/**
 * Örnek kullanım dosyası
 *
 * Bu dosya, Router sınıfının nasıl kullanılacağını gösterir
 */

// Ana sayfa rotası (callback fonksiyon)
Router::get('/', function (): void {
    echo "Ana Sayfa";
}, 'home');

// Blog detay rotası (include dosyası)
Router::get('/blog/:id/:slug', __DIR__ . '/blog_detail.php', 'blog_detay');

// Admin grup rotaları
Router::group('admin', function ($r) {
    $r->get('kullanıcı/:id', __DIR__ . '/admin_user.php', 'admin_user_show');
});

// Örnek URL oluşturma
echo Router::url('blog_detay', ['id'=>42, 'slug'=>'php-router']); // /blog/42/php-router
echo "<br>";
echo Router::url('/blog/:id/:slug', ['id'=>7, 'slug'=>'deneme']); // /blog/7/deneme

// Gelen HTTP isteğini dispatch et
Router::dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
