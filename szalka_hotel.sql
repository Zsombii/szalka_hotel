-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Már 05. 00:09
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `szalka_hotel`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$4tAs9vdP20nLMGRZ5ONzEuw7PdYbIYI1BlgHvTK0gIg4f8VHRiNRW', 'admin@szalkahotel.hu', '2026-02-25 21:32:18', '2026-03-04 21:04:21');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `guest_email` varchar(100) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `adults` int(11) NOT NULL DEFAULT 2,
  `children` int(11) NOT NULL DEFAULT 0,
  `total_price` int(11) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `booking_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `bookings`
--

INSERT INTO `bookings` (`id`, `room_id`, `guest_name`, `guest_email`, `guest_phone`, `check_in`, `check_out`, `adults`, `children`, `total_price`, `special_requests`, `status`, `booking_date`) VALUES
(4, 7, 'veres zsombor', 'asd@sad.com', '06701213232', '2026-02-27', '2026-02-28', 1, 1, 11500, '', 'confirmed', '2026-02-26 22:33:30'),
(6, 7, 'veres zsombor', 'asd@sad.com', '06701213232', '2026-03-04', '2026-03-12', 2, 0, 92000, '', 'confirmed', '2026-03-04 22:20:17'),
(10, 1, 'veres zsombor', 'asd@sad.com', '06701213232', '2026-03-05', '2026-03-06', 2, 0, 22000, '', 'confirmed', '2026-03-04 23:31:47');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `newsletter`
--

INSERT INTO `newsletter` (`id`, `email`) VALUES
(3, 'admin@cinema.hu'),
(4, 'thedarkalien51@gmail.com'),
(6, 'thedarkalien510@gmail.com'),
(7, 'vzsombor1215@gmail.com'),
(5, 'zsombor115@freemail.hu');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_type_id` int(11) DEFAULT NULL,
  `room_number` varchar(10) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `detailed_description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('available','booked') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `rooms`
--

INSERT INTO `rooms` (`id`, `room_type_id`, `room_number`, `type`, `price`, `description`, `detailed_description`, `image`, `status`) VALUES
(1, 1, '101', 'Classic szoba', 12000, 'Kényelmes egyágyas szoba.', NULL, NULL, 'available'),
(3, 2, '201', 'Superior szoba', 25000, 'Erkélyes, tágas szoba.', NULL, NULL, 'available'),
(5, 3, '301', 'Grand lakosztály', 18000, 'Jacuzzival felszerelt szoba.', NULL, NULL, 'available'),
(7, 4, '401', 'Classic tetőtéri szoba', 11500, 'Klasszikus tetőtéri szoba.', NULL, NULL, 'available'),
(9, 5, '501', 'Családi superior szoba', 27000, 'Panorámás tetőtéri apartman.', NULL, NULL, 'available');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `room_types`
--

CREATE TABLE `room_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `detailed_description` text DEFAULT NULL,
  `base_price` int(11) NOT NULL,
  `max_guests` int(11) DEFAULT 2,
  `size_sqm` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `room_types`
--

INSERT INTO `room_types` (`id`, `type_name`, `description`, `detailed_description`, `base_price`, `max_guests`, `size_sqm`, `created_at`) VALUES
(1, 'Classic szoba', 'A Classic szoba a tökéletes választás azok számára, akik a kényelmet és az eleganciát keresik megfizethető áron. A 18 m²-es szoba modern, berendezéssel várja vendégeit. Az ablakból nyíló városra néző kilátás, a kényelmes franciaágy és a gondosan megválogatott bútorok biztosítják a pihentető kikapcsolódást.', 'A Classic szoba a tökéletes választás azok számára, akik a kényelmet és az eleganciát keresik megfizethető áron. A 18 m²-es szoba modern, berendezéssel várja vendégeit. Az ablakból nyíló városra néző kilátás, a kényelmes franciaágy és a gondosan megválogatott bútorok biztosítják a pihentető kikapcsolódást.\r\n\r\nA szobához tartozik egy fürdőszoba zuhanykabinnal. A szoba további felszereltségéhez tartozik egy íróasztal és egy kényelmes szék, valamint egy gardróbszekrény, ahol kényelmesen elférnek a csomagok.', 22000, 2, 18, '2026-02-20 18:58:03'),
(2, 'Superior szoba', 'A Superior szoba a tökéletes választás azok számára, akik extra kényelemre és tágasabb térre vágynak. A 22 m²-es, ízlésesen berendezett szoba, ami ideális hosszabb tartózkodáshoz vagy üzleti utazásokhoz is. Az ablakokból és az erkélyről a szálloda gondozott parkjára nyílik lenyűgöző kilátás.\r\n\r\n', 'A Superior szoba a tökéletes választás azok számára, akik extra kényelemre és tágasabb térre vágynak. A 22 m²-es, ízlésesen berendezett szoba, ami ideális hosszabb tartózkodáshoz vagy üzleti utazásokhoz is. Az ablakokból és az erkélyről a szálloda gondozott parkjára nyílik lenyűgöző kilátás.\r\n\r\nA prémium kényelmi extrák – mint például a Nespresso kávéfőző, vízforraló és az ingyenes üdvözlő italok garantálják a felejthetetlen és pihentető kikapcsolódást.', 25000, 2, 22, '2026-02-20 18:58:03'),
(3, 'Grand lakosztály', 'A Grand Lakosztály a Hotel Szalka legelőkelőbb és legtágasabb szálláshelye, amely a végsőkig vitt eleganciát és kifinomultságot képviseli. A 65 m²-en elterülő, különlegesen kialakított lakosztály amely, tágas hálószobából és elegáns nappaliból áll.', 'A Grand Lakosztály a Hotel Szalka legelőkelőbb és legtágasabb szálláshelye, amely a végsőkig vitt eleganciát és kifinomultságot képviseli. A 65 m²-en elterülő, különlegesen kialakított lakosztály amely, tágas hálószobából és elegáns nappaliból áll. \r\n\r\nA privát pezsgőfürdő (jacuzzi) és a tágas erkély gondoskodik a felejthetetlen kikapcsolódásról. Az erkélyről és a panorámaablakokból kilátás nyílik a szálloda parkjára, maximális komfortot és privát szférát biztosítva.', 18000, 2, 65, '2026-02-20 18:58:03'),
(4, 'Classic tetőtéri szoba', 'A Classic Tetőtéri szoba a hangulatos és romantikus kikapcsolódás kedvelőinek nyújt különleges élményt. A szálloda legfelső szintjén, a tetőtérben kialakított szoba egyedi atmoszféráját a ferde falak, a tetőszerkezet elemei és a gondosan megtervezett, mégis letisztult design adják.', 'A Classic Tetőtéri szoba a hangulatos és romantikus kikapcsolódás kedvelőinek nyújt különleges élményt. A szálloda legfelső szintjén, a tetőtérben kialakított szoba egyedi atmoszféráját a ferde falak, a tetőszerkezet elemei és a gondosan megtervezett, mégis letisztult design adják.\r\n\r\nA tetőtéri elhelyezkedés garantálja a csendet és a nyugalmat, így a Classic Tetőtéri szoba tökéletes választás pároknak, vagy azoknak, akik egy kis extra hangulatot keresnek mátészalkai tartózkodásukhoz.', 11500, 2, 25, '2026-02-20 18:58:03'),
(5, 'Családi superior szoba', 'A Családi Superior szoba a gyermekes családok igényeire szabott, tágas és kényelmes otthon a Hotel Szalkában. A szoba kialakítása igazi családbarát megoldásokat kínál.', 'A Családi Superior szoba a gyermekes családok igényeire szabott, tágas és kényelmes otthon a Hotel Szalkában. A szoba kialakítása igazi családbarát megoldásokat kínál.\r\n\r\nA szoba különlegessége a nagy méretű erkély, ahonnan gyönyörű kilátás nyílik a szálloda parkjára. A családok kényelmét szolgálja továbbá a tágas fürdőszoba, amely káddal is rendelkezik.', 27000, 5, 60, '2026-02-20 18:58:03');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `room_type_features`
--

CREATE TABLE `room_type_features` (
  `id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `feature_icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `room_type_features`
--

INSERT INTO `room_type_features` (`id`, `room_type_id`, `feature_name`, `feature_icon`) VALUES
(170, 1, 'Max. 2 fő részére', NULL),
(171, 1, '18 m²', NULL),
(172, 1, 'Légkondicionáló', NULL),
(173, 1, 'LED TV', NULL),
(174, 1, 'Minibár', NULL),
(175, 1, 'Széf', NULL),
(176, 1, 'Fürdőköpeny, papucs', NULL),
(177, 1, 'Ingyenes Wi-Fi', NULL),
(178, 1, 'Hajszárító', NULL),
(179, 1, 'Telefon', NULL),
(190, 4, '2 fő részére', NULL),
(191, 4, '22 m²', NULL),
(192, 4, 'Légkondicionáló', NULL),
(193, 4, 'LED TV', NULL),
(194, 4, 'Minibár', NULL),
(195, 4, 'Széf', NULL),
(196, 4, 'Ingyenes Wi-Fi', NULL),
(197, 4, 'Kávéfőző', NULL),
(198, 4, 'Vízforraló', NULL),
(199, 4, 'Fürdőköpeny, papucs', NULL),
(250, 3, 'Max. 2 fő részére', NULL),
(251, 3, '70 m²', NULL),
(252, 3, 'Légkondicionáló', NULL),
(253, 3, 'Jacuzzi', NULL),
(254, 3, 'Minibár', NULL),
(255, 3, 'Széf', NULL),
(256, 3, 'Fürdőköpeny, papucs', NULL),
(257, 3, 'Kávéfőző', NULL),
(258, 3, 'Ingyenes Wi-Fi', NULL),
(259, 3, 'OLED TV', NULL),
(269, 5, 'Max. 5 fő részére', NULL),
(270, 5, 'Légkondicionáló', NULL),
(271, 5, '60 m²', NULL),
(272, 5, 'Széf', NULL),
(273, 5, 'OLED TV', NULL),
(274, 5, 'Erkély', NULL),
(275, 5, 'Kávéfőző', NULL),
(276, 5, 'Minibár', NULL),
(277, 5, 'Ingyenes Wi-Fi', NULL),
(278, 5, 'Telefon', NULL),
(279, 2, '2 fő részére', NULL),
(280, 2, '22 m²', NULL),
(281, 2, 'Erkély', NULL),
(282, 2, 'Légkondicionáló', NULL),
(283, 2, 'OLED TV', NULL),
(284, 2, 'Minibár', NULL),
(285, 2, 'Széf', NULL),
(286, 2, 'Ingyenes Wi-Fi', NULL),
(287, 2, 'Kávéfőző', NULL),
(288, 2, 'Vízforraló', NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `room_type_images`
--

CREATE TABLE `room_type_images` (
  `id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `room_type_images`
--

INSERT INTO `room_type_images` (`id`, `room_type_id`, `image_url`, `is_main`, `sort_order`) VALUES
(1, 1, 'uploads/rooms/room_1_1771446714_699621ba75f3c.jpg', 1, 0),
(2, 1, 'uploads/rooms/room_1_1771446720_699621c008bff.jpg', 0, 0),
(3, 1, 'uploads/rooms/room_1_1771446725_699621c51749c.jpg', 0, 0),
(4, 1, 'uploads/rooms/room_1_1771446729_699621c9b63c6.jpg', 0, 0),
(7, 4, 'uploads/room_types/roomtype_4_1772027645_699efefd65419.jpg', 1, 0),
(8, 4, 'uploads/room_types/roomtype_4_1772027649_699eff01a86da.jpg', 0, 0),
(9, 4, 'uploads/room_types/roomtype_4_1772027655_699eff07cf4e2.jpg', 0, 0),
(10, 4, 'uploads/room_types/roomtype_4_1772027660_699eff0c39a80.jpg', 0, 0),
(11, 5, 'uploads/room_types/roomtype_5_1772027887_699effef52f26.png', 1, 0),
(12, 5, 'uploads/room_types/roomtype_5_1772027892_699efff43ff0f.png', 0, 0),
(13, 5, 'uploads/room_types/roomtype_5_1772027902_699efffe70833.jpg', 0, 0),
(14, 5, 'uploads/room_types/roomtype_5_1772027915_699f000ba2157.jpg', 0, 0),
(16, 5, 'uploads/room_types/roomtype_5_1772027947_699f002bd42e1.jpg', 0, 0),
(17, 3, 'uploads/room_types/roomtype_3_1772028830_699f039e7cd89.jpg', 1, 0),
(18, 3, 'uploads/room_types/roomtype_3_1772028835_699f03a370726.jpg', 0, 0),
(19, 3, 'uploads/room_types/roomtype_3_1772028840_699f03a80d5d6.jpg', 0, 0),
(20, 3, 'uploads/room_types/roomtype_3_1772028848_699f03b02a98b.png', 0, 0),
(21, 3, 'uploads/room_types/roomtype_3_1772028852_699f03b4591ed.jpg', 0, 0),
(22, 2, 'uploads/room_types/roomtype_2_1772029236_699f05349ec49.png', 1, 0),
(23, 2, 'uploads/room_types/roomtype_2_1772029240_699f0538ce05b.png', 0, 0),
(24, 2, 'uploads/room_types/roomtype_2_1772029249_699f0541101f9.png', 0, 0),
(25, 2, 'uploads/room_types/roomtype_2_1772029253_699f054550ea7.jpg', 0, 0),
(26, 2, 'uploads/room_types/roomtype_2_1772029257_699f05494d7cc.png', 0, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `unavailable_dates`
--

CREATE TABLE `unavailable_dates` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(50) DEFAULT 'booking'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- A tábla indexei `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- A tábla indexei `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A tábla indexei `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_type` (`room_type_id`);

--
-- A tábla indexei `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `room_type_features`
--
ALTER TABLE `room_type_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- A tábla indexei `room_type_images`
--
ALTER TABLE `room_type_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- A tábla indexei `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_room_date` (`room_id`,`date`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT a táblához `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT a táblához `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT a táblához `room_types`
--
ALTER TABLE `room_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `room_type_features`
--
ALTER TABLE `room_type_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;

--
-- AUTO_INCREMENT a táblához `room_type_images`
--
ALTER TABLE `room_type_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT a táblához `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `room_type_features`
--
ALTER TABLE `room_type_features`
  ADD CONSTRAINT `room_type_features_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `room_type_images`
--
ALTER TABLE `room_type_images`
  ADD CONSTRAINT `room_type_images_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  ADD CONSTRAINT `unavailable_dates_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
