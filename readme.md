Nette Web Project
=================

Welcome to the Nette Web Project! This is a basic skeleton application built using
[Nette](https://nette.org), ideal for kick-starting your new web projects.

Nette is a renowned PHP web development framework, celebrated for its user-friendliness,
robust security, and outstanding performance. It's among the safest choices
for PHP frameworks out there.

If Nette helps you, consider supporting it by [making a donation](https://nette.org/donate).
Thank you for your generosity!


Requirements
------------

This Web Project is compatible with Nette 3.1 and requires PHP 8.0.


Installation
------------

To install the Web Project, Composer is the recommended tool. If you're new to Composer,
follow [these instructions](https://doc.nette.org/composer). Then, run:

	composer create-project nette/web-project path/to/install
	cd path/to/install

Ensure the `temp/` and `log/` directories are writable.


Web Server Setup
----------------

To quickly dive in, use PHP's built-in server:

	php -S localhost:8000 -t www

Then, open `http://localhost:8000` in your browser to view the welcome page.

For Apache or Nginx users, configure a virtual host pointing to your project's `www/` directory.

**Important Note:** Ensure `app/`, `config/`, `log/`, and `temp/` directories are not web-accessible.
Refer to [security warning](https://nette.org/security-warning) for more details.


Minimal Skeleton
----------------

For demonstrating issues or similar tasks, rather than starting a new project, use
this [minimal skeleton](https://github.com/nette/web-project/tree/minimal).



Actual DB:
-- Tabulka pro kategorie galerie
CREATE TABLE `gallery_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Tabulka pro obrázky v galerii
CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255),
  `alt_text` varchar(200),
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `active` (`active`),
  CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Vložení základních kategorií
INSERT INTO `gallery_categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Vřetena', 'vretena', 'Opravy a servis vřeten', 1),
('Revolverové hlavy', 'revolvery', 'Opravy revolverových hlav', 2),
('Technologie', 'technologie', 'Naše technologie a postupy', 3),
('Dílna', 'workshop', 'Záběry z naší dílny', 4);

-- Tabulka pro administrátory (pokud ještě nemáte)
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `faq` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `question` TEXT NOT NULL,
  `answer` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `faq` (`question`, `answer`) VALUES
('Jak často by se měla provádět preventivní údržba vřetena?', 'Preventivní údržba by se měla provádět podle provozních hodin a doporučení výrobce. Obecně doporučujeme kontrolu každých 2000-3000 provozních hodin, nebo minimálně jednou ročně při intenzivním provozu.'),
('Jaké jsou příznaky poškození vřetena?', 'Mezi hlavní příznaky patří: zvýšené vibrace, hluk během provozu, zahřívání vřetena, snížená přesnost obrábění, problémy s upínáním nástrojů nebo nestabilní otáčky.'),
('Jak dlouho trvá standardní oprava vřetena?', 'Doba opravy závisí na rozsahu poškození. Standardní oprava trvá 7-14 dní. Komplexní rekonstrukce může trvat až 3 týdny. Po diagnostice vám poskytneme přesný časový harmonogram.'),
('Poskytujete záruku na opravy?', 'Ano, na všechny naše opravy poskytujeme záruku. Délka záruky závisí na typu opravy a použitých komponentech. Detaily záruky jsou vždy specifikovány v nabídce a smlouvě.'),
('Můžete opravit vřetena všech výrobců?', 'Máme zkušenosti s širokým spektrem výrobců. U některých méně běžných značek je nutná předběžná konzultace. Vždy se snažíme najít řešení i pro nestandardní případy.');

CREATE TABLE `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
