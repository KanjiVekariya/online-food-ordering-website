-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 01:26 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `full_address` text NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `label`, `full_address`, `city`, `postal_code`) VALUES
(1, 4, 'home', 'kkv hall kalavad road', 'rajkot', '360300'),
(2, 4, 'office', 'mavdi chowkadi', 'rajkot', '360006'),
(3, 4, 'farmhouse', 'Near vijaywada opposite to swaminarayan gurukul', 'mumbai', '360065'),
(10, 9, 'Home', 'fgdfg', 'rajkot', '360005'),
(13, 8, 'home', 'andheri west gokuldham society powder gali road', 'mumbai', '360005'),
(15, 11, 'office', 'mavdi chokdi rajkot', 'rajkot', '360311'),
(16, 13, 'home', 'kkv circle kalawad road nana mauva circle', 'rajkot', '360005');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `restaurant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `restaurant_id`) VALUES
(8, 'indian', NULL),
(9, 'chinese', NULL),
(10, 'Italian', NULL),
(11, 'Mexican', NULL),
(12, 'Gujarati', NULL),
(13, 'korean', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_persons`
--

CREATE TABLE `delivery_persons` (
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT 'male',
  `password_hash` text NOT NULL,
  `assigned_order_id` int(11) DEFAULT NULL,
  `location_lat` decimal(10,6) DEFAULT NULL,
  `location_long` decimal(10,6) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `delivery_persons`
--

INSERT INTO `delivery_persons` (`delivery_id`, `name`, `email`, `phone`, `gender`, `password_hash`, `assigned_order_id`, `location_lat`, `location_long`, `is_available`) VALUES
(1, 'vekariya kanji', 'vekariyakanji578@gmail.com', '9638527410', 'male', '$2y$10$h4jHiiPlg02u.wcWv6W0rePqfKqos2DoTb0JFKOzlBa5dNuH57CxC', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_staff`
--

CREATE TABLE `delivery_staff` (
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `assigned_order_id` int(11) DEFAULT NULL,
  `location_lat` decimal(10,6) DEFAULT NULL,
  `location_long` decimal(10,6) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `delivery_staff`
--

INSERT INTO `delivery_staff` (`staff_id`, `user_id`, `assigned_order_id`, `location_lat`, `location_long`, `is_available`) VALUES
(1, 1, 1, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `name`, `description`, `price`, `image_url`, `category_id`, `restaurant_id`, `is_available`) VALUES
(12, 'Punjabi thali', 'a delicious punjabi thali', '450.00', 'uploads/6881f3613151d_6880ae6bf0457_punjabi_thali.avif', 9, 2, 1),
(14, 'gujarati thali', 'a delicious gujarati thali that every gujarati loves it', '150.00', 'uploads/6881f48f2e75d_6880aa29ef528_img1.jpg', 8, 2, 1),
(15, 'Pav bhaji', 'A delicious snack of pav bhaji', '120.00', 'uploads/6881f632ef148_6881f14e4f68f_6880afe5451c0_pavbhaji.avif', 8, 4, 1),
(16, 'Manchurian momos', 'A mouthwatering vegetarian delight from the Momo category, Manchurian Momo is a delectable fusion of flavors that is sure to leave you craving for more.', '85.00', 'uploads/68835993c5c8c_momos.avif', 8, 2, 1),
(17, 'chilli momos', 'Savor the delectable flavors of our vegetarian Momo, a true delight for your taste buds.', '85.00', 'uploads/68835a1ebb701_chilli_momos.avif', 8, 6, 1),
(18, 'Veg. Manchurian', 'A flavorsome Chinese starter that brings together vegetables in a delectable fusion of textures and tastes.', '120.00', 'uploads/68835e47eebef_machurian.avif', 9, 2, 1),
(19, 'Cutting Dosa', 'A flavorful and crispy South Indian specialty, perfect for those looking for a savory breakfast or snack option.', '110.00', 'uploads/68835ec264844_paper_dosa.avif', 8, 7, 1),
(20, 'paneer masala', 'A flavorful and aromatic North Indian delicacy that perfectly combines tender paneer with a rich and luscious gravy.', '129.00', 'uploads/6884b99cd14ec_panner_masala.avif', 8, 9, 1),
(21, 'kaju masala', 'delicious kaju masala', '125.00', 'uploads/6884b9fa546f7_kaju_masala.avif', 8, 8, 1),
(22, 'pizza', 'Pizza topped with our herb-infused signature pan sauce and 100% mozzarella cheese. A classic treat for all cheese lovers out there!', '169.00', 'uploads/68947a744d5ed_pizza_hut.avif', 10, 5, 1),
(23, 'gujarati thali', 'Best authentic gujarati thali with a typical test', '156.00', 'uploads/68a44540976e6_gujrati_thali2.jpg', 8, 9, 1),
(24, 'cheese chilli sandwich', 'Cheese, black pepper A flavorful and satisfying veggie delight grilled to perfection, perfect to satisfy your hunger cravings.', '190.00', 'uploads/68ef7f43bfa26_sandwich3.avif', 10, 9, 1),
(25, 'sandwich', 'Cheese grilled sandwich is sandwich consisting of a flavourful filling between bread, cheese and grilled to perfection.', '180.00', 'uploads/menu_images/menu_68ef8221ed655.avif', 11, 8, 1),
(26, 'maxican sandwich', 'Cheese, cutlet, pineapple pieces, pineapple jam, tomato, cucumber, capsicum, onion.', '210.00', 'uploads/68ef805a4d459_sandwich.avif', 11, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `order_status` enum('pending','preparing','on_the_way','delivered','cancelled') DEFAULT 'pending',
  `total_price` decimal(10,2) DEFAULT NULL,
  `placed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `restaurant_id`, `order_status`, `total_price`, `placed_at`, `address_id`) VALUES
(2, 13, 8, 'pending', '727.30', '2025-10-15 11:17:02', 16),
(3, 13, 8, 'pending', '308.40', '2025-10-15 11:17:52', 16);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `user_canceled` tinyint(1) DEFAULT 0,
  `restaurant_canceled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `price`, `status`, `user_canceled`, `restaurant_canceled`) VALUES
(5, 2, 25, 1, '180.00', 'delivered', 0, 0),
(6, 2, 18, 1, '120.00', 'delivered', 0, 0),
(7, 2, 16, 1, '85.00', 'delivered', 0, 0),
(8, 2, 14, 1, '150.00', 'delivered', 0, 0),
(9, 3, 25, 1, '180.00', 'delivered', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_method` enum('card','cash_on_delivery','wallet') DEFAULT 'cash_on_delivery',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `paid_at`) VALUES
(1, 19, 'cash_on_delivery', 'pending', '2025-08-07 10:26:22'),
(2, 20, 'cash_on_delivery', 'pending', '2025-08-07 10:26:41');

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `is_open` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`restaurant_id`, `name`, `description`, `address`, `phone`, `photo`, `open_time`, `close_time`, `is_open`, `created_at`) VALUES
(1, 'Ajay\'s cafe', 'Kathiyawadi, North Indian, Gujarati, Rajasthani, Chinese, Fast Food, Desserts, Beverages and soft drink', 'yogigham gurukul rajkot KKV hall', '9638527410', 'uploads/rest_6883451708042.jpg', '14:00:00', '21:42:00', 1, '2025-07-23 10:13:53'),
(2, 'The Spice Hub', 'Authentic Indian cuisine with a modern twist. Known for flavorful curries and biryanis.', '123 Curry Lane, New Delhi, India', '+91-9876543210', 'uploads/rest_68835268e30c1.avif', '10:00:00', '22:00:00', 1, '2025-07-23 10:15:54'),
(3, 'Bella Italia', 'Cozy Italian restaurant offering handmade pastas, wood-fired pizzas, and fine wines.', '45 Via Roma, Rome, Italy', '+39-06-1234567', 'uploads/rest_68834451c1707.jpg', '11:30:00', '23:00:00', 1, '2025-07-23 10:15:54'),
(4, 'Sushi World', 'Fresh sushi and sashimi prepared by experienced chefs. Also offers ramen and tempura.', '789 Sakura Street, Tokyo, Japan', '+81-3-1234-5678', 'uploads/rest_68834cba251dc.webp', '12:00:00', '21:00:00', 1, '2025-07-23 10:15:54'),
(5, 'Green Garden Cafe', 'Vegetarian and vegan-friendly cafe with organic ingredients and freshly brewed coffee.', '88 Maple Avenue, San Francisco, CA, USA', '+1-415-555-0199', 'uploads/rest_68834c77336b9.jpg', '08:00:00', '20:00:00', 1, '2025-07-23 10:15:54'),
(6, 'The BBQ Shack', 'Casual spot specializing in smoked ribs, brisket, and classic southern sides.', '150 Smokehouse Blvd, Austin, TX, USA', '+1-512-555-0101', 'uploads/rest_688347f412727.webp', '11:00:00', '22:30:00', 1, '2025-07-23 10:15:54'),
(7, 'punjabi national hotel', 'best for punjabi meals', 'near nanavati chok rajkot', '9586478963', 'uploads/rest_6883524a9443e.avif', '18:00:00', '20:34:00', 1, '2025-07-24 10:04:45'),
(8, 'vir vachcharaj hotel', 'A hotel where you can eat authentic gujarati', 'nana mava circle road rajkot', '95864225956', 'uploads/rest_6883481fefbd8.avif', '16:14:00', '22:14:00', 1, '2025-07-24 10:44:44'),
(9, 'Gupta Bhojnalay', 'A cozy family-owned diner serving classic American comfort food with a modern twist. Friendly atmosphere and homemade recipes.', '123 Maple Street, Springfield, IL 62704', '9685741263', 'uploads/1755595945_gupata.avif', '03:00:00', '18:00:00', 1, '2025-07-25 08:29:34');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_categories`
--

CREATE TABLE `restaurant_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `restaurant_categories`
--

INSERT INTO `restaurant_categories` (`id`, `restaurant_id`, `category_id`) VALUES
(45, 1, 8),
(46, 3, 8),
(52, 3, 13),
(23, 4, 9),
(42, 4, 10),
(47, 5, 8),
(21, 5, 9),
(39, 5, 10),
(28, 5, 11),
(49, 6, 8),
(29, 6, 11),
(53, 6, 13),
(41, 7, 10),
(50, 8, 8),
(24, 8, 9),
(44, 8, 12),
(48, 9, 8),
(22, 9, 9),
(40, 9, 10),
(43, 9, 12);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `created_at`) VALUES
(4, 'kanji', 'vekariyakanji578@gmail.com', '123456', '6354090872', '2025-07-30 08:30:36'),
(7, 'darshit', 'darshitkumbhani@gmail.com', '123456', '9586422963', '2025-07-31 08:53:05'),
(8, 'user', 'user@gmail.com', '123', '9638527410', '2025-08-06 11:05:54'),
(9, 'jaymin', 'jaymin@gmail.com', '123', '639852741', '2025-08-07 10:27:35'),
(11, 'krish bhuva', 'krishbhuva@gmail.com', '123456', '6354090872', '2025-09-26 10:10:33'),
(12, 'admin', 'admin@gmail.com', '123', '9874563214', '2025-10-09 05:08:28'),
(13, 'pulkit', 'pulkitchotaliya@gmail.com', '123456', '9586422963', '2025-10-15 09:50:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `fk_addresses_user` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `delivery_persons`
--
ALTER TABLE `delivery_persons`
  ADD PRIMARY KEY (`delivery_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `delivery_staff`
--
ALTER TABLE `delivery_staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`restaurant_id`);

--
-- Indexes for table `restaurant_categories`
--
ALTER TABLE `restaurant_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_restaurant_category` (`restaurant_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `delivery_persons`
--
ALTER TABLE `delivery_persons`
  MODIFY `delivery_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_staff`
--
ALTER TABLE `delivery_staff`
  MODIFY `staff_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `restaurant_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `restaurant_categories`
--
ALTER TABLE `restaurant_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`);

--
-- Constraints for table `restaurant_categories`
--
ALTER TABLE `restaurant_categories`
  ADD CONSTRAINT `restaurant_categories_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `restaurant_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
