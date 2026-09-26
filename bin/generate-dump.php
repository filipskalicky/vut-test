<?php

/**
 * One-off helper used to write database/dump.sql from the same sample data
 * as NewsSeeder / UserSeeder. Run: php bin/generate-dump.php
 */
$hash = '$2y$10$.BJPgjB2P.oAafGcuemxB.xfODFvLi1ppbMku6G8xgDJy33ammfeW';

function sql(string $s): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $s) . "'";
}

function doc(string $paragraph, array $items = [], bool $ordered = false): string
{
    $blocks = [
        ['type' => 'paragraph', 'data' => ['text' => $paragraph]],
    ];

    if ($items !== []) {
        $blocks[] = [
            'type' => 'list',
            'data' => [
                'style' => $ordered ? 'ordered' : 'unordered',
                'items' => array_map(static fn (string $item): array => [
                    'content' => $item,
                    'items'   => [],
                ], $items),
            ],
        ];
    }

    return json_encode([
        'time'    => 1_725_000_000_000,
        'blocks'  => $blocks,
        'version' => '2.30.7',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

$rows = [
    ['Zahájení semestru', doc('Vítáme studenty v novém semestru. Zápis předmětů probíhá do konce týdne.', ['Studijní oddělení', 'Knihovna', 'Menzy']), '2026-09-20 08:00:00', null],
    ['Otevření nové laboratoře', doc('Fakulta otevřela laboratoř pro vestavěné systémy. Prohlídky probíhají ve čtvrtek.'), '2026-09-15 10:30:00', null],
    ['Termíny zápočtů', doc('Přehled termínů zápočtů je zveřejněn v informačním systému. Kapacita je omezená.'), '2026-09-10 07:15:00', null],
    ['Konference studentů', doc('Studentská konference se koná v aule. Přihlášky posílejte na <a href="mailto:konference@example.com">konference@example.com</a>.', ['Abstrakt do 1. října', 'Prezentace 10 minut', 'Posterová sekce odpoledne'], true), '2026-09-01 09:00:00', '2099-12-31 23:59:00'],
    ['Údržba informačního systému', doc('V sobotu od 22:00 do 02:00 bude probíhat plánovaná odstávka. Uložte si rozpracovanou práci.'), '2026-08-20 22:00:00', null],
    ['Nové studijní materiály', doc('Do e-learningu byly nahrány aktualizované slidy a vzorové úlohy z předchozích let.'), '2026-08-01 12:00:00', null],
    ['Den otevřených dveří', doc('Tato aktualita se na webu objeví až v roce 2099, protože má budoucí datum zobrazení.'), '2099-01-01 08:00:00', null],
    ['Zápis na zkoušky (archiv)', doc('Tato aktualita už vypršela a ve veřejné části se nezobrazuje.'), '2020-01-10 08:00:00', '2020-12-31 23:59:00'],
];

$lines = [];
foreach ($rows as $row) {
    $to      = $row[3] === null ? 'NULL' : sql($row[3]);
    $lines[] = '(' . sql($row[0]) . ', ' . sql($row[1]) . ', ' . sql($row[2]) . ', ' . $to . ", '2026-09-24 19:00:00', '2026-09-24 19:00:00')";
}

$out  = <<<SQL
-- Aktuality database dump
-- Login: test / test

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `aktuality` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `aktuality`;

DROP TABLE IF EXISTS `news`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `news` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `visible_from` DATETIME NOT NULL,
  `visible_to` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visible_from_visible_to` (`visible_from`, `visible_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`username`, `password_hash`, `created_at`) VALUES
SQL;

$out .= '(' . sql('test') . ', ' . sql($hash) . ", '2026-09-24 19:00:00');\n\n";
$out .= "INSERT INTO `news` (`title`, `content`, `visible_from`, `visible_to`, `created_at`, `updated_at`) VALUES\n";
$out .= implode(",\n", $lines) . ";\n\n";
$out .= "INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES\n";
$out .= "('2026-01-01-000001', 'App\\\\Database\\\\Migrations\\\\CreateUsersTable', 'default', 'App', 1727182800, 1),\n";
$out .= "('2026-01-01-000002', 'App\\\\Database\\\\Migrations\\\\CreateNewsTable', 'default', 'App', 1727182801, 1),\n";
$out .= "('2026-09-24-200000', 'App\\\\Database\\\\Migrations\\\\ChangeNewsVisibilityToDatetime', 'default', 'App', 1727200000, 2);\n\n";
$out .= "SET FOREIGN_KEY_CHECKS = 1;\n";

$path = dirname(__DIR__) . '/database/dump.sql';
file_put_contents($path, $out);

echo "Wrote {$path} (" . strlen($out) . " bytes)\n";
