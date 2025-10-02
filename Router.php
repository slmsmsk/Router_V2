<?php

declare(strict_types=1);

/**
 * Statik PHP Router Sınıfı
 *
 * - GET ve POST metodlarını destekler
 * - Dinamik parametreleri (id, slug, any) regex ile yakalar
 * - Route gruplarını (prefix) destekler
 * - Named routes (isimlendirilmiş rotalar) ve URL oluşturma sağlar
 * - Handler olarak callable veya include edilecek PHP dosyası kullanılabilir
 *
 * @package PHP-Router
 */
class Router
{
    /**
     * Tüm tanımlı rotalar
     *
     * @var array<string, array>
     */
    private static array $routes = [
        'GET'  => [],
        'POST' => []
    ];

    /**
     * İsimlendirilmiş rotalar (named routes)
     *
     * @var array<string, string>
     */
    private static array $namedRoutes = [];

    /**
     * Parametre tiplerine karşılık gelen regex desenleri
     *
     * @var array<string, string>
     */
    private static array $patterns = [
        'id'   => '([0-9]+)',          // Sadece rakam
        'slug' => '([a-zA-Z0-9-]+)',   // Harf, rakam ve tire
        'any'  => '([^/]+)'            // Herhangi bir karakter
    ];

    /**
     * GET rotası ekler
     *
     * @param string        $path    Rota yolu (örn: /blog/:id)
     * @param callable|string $handler Callback fonksiyon veya include edilecek dosya
     * @param string|null   $name    Rota adı (opsiyonel)
     * @return void
     */
    public static function get(string $path, $handler, ?string $name = null): void
    {
        self::addRoute('GET', $path, $handler, $name);
    }

    /**
     * POST rotası ekler
     *
     * @param string        $path    Rota yolu (örn: /blog/:id)
     * @param callable|string $handler Callback fonksiyon veya include edilecek dosya
     * @param string|null   $name    Rota adı (opsiyonel)
     * @return void
     */
    public static function post(string $path, $handler, ?string $name = null): void
    {
        self::addRoute('POST', $path, $handler, $name);
    }

    /**
     * Rotayı ekler ve regex oluşturur
     *
     * @param string        $method  HTTP metodu (GET/POST)
     * @param string        $path    Rota yolu
     * @param callable|string $handler Callback veya include dosyası
     * @param string|null   $name    Rota adı (opsiyonel)
     * @return void
     */
    private static function addRoute(string $method, string $path, $handler, ?string $name = null): void
    {
        // Rota yolunu regex pattern'e çevir
        $regex = self::convertToRegex($path);

        // Rotaları array içine ekle
        self::$routes[$method][] = [
            'regex'   => $regex,
            'handler' => $handler,
            'path'    => $path,
            'name'    => $name
        ];

        // Eğer rota adı verilmişse namedRoutes dizisine ekle
        if ($name !== null) {
            self::$namedRoutes[$name] = $path;
        }
    }

    /**
     * Rota grubu tanımlama (prefix kullanarak)
     *
     * @param string   $prefix   Rota prefixi (örn: admin)
     * @param callable $callback Rotayı tanımlayan anonim fonksiyon
     * @return void
     */
    public static function group(string $prefix, callable $callback): void
    {
        // Sondaki / işareti varsa temizle
        $prefix = rtrim($prefix, '/');

        // Anonim sınıf ile grup rotalarını tanımla
        $callback(new class($prefix) {
            private string $prefix;

            /**
             * Constructor
             *
             * @param string $prefix Ortak prefix
             */
            public function __construct(string $prefix)
            {
                $this->prefix = $prefix;
            }

            /**
             * GET rotası ekler grup prefix ile
             *
             * @param string        $path    Rota yolu
             * @param callable|string $handler Callback veya include dosyası
             * @param string|null   $name    Rota adı
             * @return void
             */
            public function get(string $path, $handler, ?string $name = null): void
            {
                Router::get($this->prefix . '/' . ltrim($path, '/'), $handler, $name);
            }

            /**
             * POST rotası ekler grup prefix ile
             *
             * @param string        $path    Rota yolu
             * @param callable|string $handler Callback veya include dosyası
             * @param string|null   $name    Rota adı
             * @return void
             */
            public function post(string $path, $handler, ?string $name = null): void
            {
                Router::post($this->prefix . '/' . ltrim($path, '/'), $handler, $name);
            }
        });
    }

    /**
     * İstek URI'sini uygun rotaya yönlendirir
     *
     * @param string $uri    İstek URI'si
     * @param string $method HTTP metodu
     * @return mixed
     */
    public static function dispatch(string $uri, string $method)
    {
        // URI’den sadece path kısmını al
        $uri = parse_url($uri, PHP_URL_PATH);
        $method = strtoupper($method);

        // HTTP metodu desteklenmiyorsa
        if (!isset(self::$routes[$method])) {
            http_response_code(405);
            echo "405 - Method Not Allowed";
            return null;
        }

        // Tüm rotaları kontrol et
        foreach (self::$routes[$method] as $route) {
            if (preg_match($route['regex'], $uri, $matches)) {
                array_shift($matches); // tüm eşleşmeyi kaldır

                $handler = $route['handler'];

                // Handler callable ise çalıştır
                if (is_callable($handler)) {
                    return call_user_func_array($handler, $matches);
                }
                // Handler string ise ve dosya mevcutsa include et
                elseif (is_string($handler) && file_exists($handler)) {
                    $params = $matches; // include dosyası içinde kullanılabilir
                    return include $handler;
                }
                // Geçersiz handler
                else {
                    echo "500 - Geçersiz rota handler!";
                    return null;
                }
            }
        }

        // Rota bulunamadı
        http_response_code(404);
        echo "404 - Sayfa bulunamadı!";
        return null;
    }

    /**
     * Rota yolunu regex pattern’e çevirir
     *
     * @param string $path Rota yolu (örn: /blog/:id)
     * @return string Regex pattern
     */
    private static function convertToRegex(string $path): string
    {
        $path = rtrim($path, '/');

        // :param şeklindeki ifadeleri regex ile değiştir
        $pattern = preg_replace_callback('/\:([a-zA-Z0-9_]+)/', function ($matches) {
            $key = $matches[1];
            return self::$patterns[$key] ?? self::$patterns['any'];
        }, $path);

        return "#^" . $pattern . "$#";
    }

    /**
     * URL oluşturma (named route veya direkt path)
     *
     * @param string $route  Rota adı veya path
     * @param array  $params Parametreler (örn: ['id'=>42,'slug'=>'php-router'])
     * @return string Oluşan URL
     */
    public static function url(string $route, array $params = []): string
    {
        $path = self::$namedRoutes[$route] ?? $route;

        return preg_replace_callback('/\:([a-zA-Z0-9_]+)/', function ($matches) use ($params) {
            $key = $matches[1];
            return $params[$key] ?? $matches[0];
        }, $path);
    }
}
