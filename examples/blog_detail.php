<?php

/**
 * Blog detay sayfası
 *
 * Dispatch sırasında $params değişkeni atanır
 * $params[0] => id
 * $params[1] => slug
 */

echo "Blog Detay Sayfası<br>";
echo "ID: " . ($params[0] ?? 'yok') . "<br>";
echo "Slug: " . ($params[1] ?? 'yok') . "<br>";
