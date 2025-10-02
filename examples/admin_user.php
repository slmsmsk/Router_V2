<?php

/**
 * Admin kullanıcı sayfası
 *
 * Dispatch sırasında $params değişkeni atanır
 * $params[0] => kullanıcı id
 */

echo "Admin Kullanıcı Sayfası<br>";
echo "ID: " . ($params[0] ?? 'yok');
