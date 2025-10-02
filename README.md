# PHP Router

Basit ve statik bir PHP Router sınıfı. Bu sınıf, modern PHP projelerinde **route yönetimini** kolaylaştırır ve **callable veya include edilecek PHP dosyası** ile esnek kullanım sağlar.

## Özellikler

- GET ve POST metodlarını destekler
- Dinamik parametreler (`id`, `slug`, `any`) regex ile yakalanır
- Route grupları (prefix) ile ortak URL yapıları kolayca oluşturulur
- Named routes (isimlendirilmiş rotalar) ile URL oluşturma kolaydır
- Handler olarak **callable** veya **include edilecek PHP dosyası** kullanılabilir
- PSR-12 ve PHPDoc standartlarına uygun yazılmıştır

## Kurulum

1. GitHub’dan klonlayarak:
```bash
git clone https://github.com/slmsmsk/PHP-Router.git
cd php-router
```

2. Projeye dahil et:
```php
require "src/Router.php";
```

## Kullanım Örnekleri

**Basit GET rotası (callback):**
```php
Router::get('/', function () {
    echo "Ana Sayfa";
}, 'home');
```

**Dinamik rota ve include dosyası:**
```php
Router::get('/blog/:id/:slug', 'examples/blog_detail.php', 'blog_detay');
```
- `:id` ve `:slug` parametreleri `$params` dizisine atanır
- `examples/blog_detail.php` dosyası içinde `$params[0]` → id, `$params[1]` → slug

**Route grupları (prefix):**
```php
Router::group('admin', function ($r) {
    $r->get('kullanıcı/:id', 'examples/admin_user.php', 'admin_user_show');
});
```
- Bu örnekte rota `/admin/kullanıcı/:id` olarak kaydedilir
- Prefix kullanımı sayesinde tüm admin rotalarını tek seferde gruplamak mümkün

**Dispatch (isteği yönlendirme):**
```php
Router::dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
```
- Bu fonksiyon gelen URI’ye göre doğru handler’ı çalıştırır
- Bulunamazsa 404, metod desteklenmezse 405 döner

**URL oluşturma (named routes):**
```php
echo Router::url('blog_detay', ['id'=>42, 'slug'=>'php-router']);
// Çıktı: /blog/42/php-router
```

## Önerilen Proje Yapısı

```
php-router/
 ├── src/
 │    └── Router.php
 ├── examples/
 │    ├── index.php
 │    ├── blog_detail.php
 │    └── admin_user.php
 ├── README.md
 └── composer.json (opsiyonel)
```

## Lisans

Bu proje MIT lisansı ile lisanslanmıştır.

