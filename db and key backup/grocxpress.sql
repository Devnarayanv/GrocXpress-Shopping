-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2025 at 09:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `grocxpress`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`) VALUES
(15, 4, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'Paid',
  `razorpay_payment_id` varchar(64) DEFAULT NULL,
  `razorpay_order_id` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `status`, `razorpay_payment_id`, `razorpay_order_id`) VALUES
(1, 1, '2025-07-19 12:13:48', 75000.00, 'Paid', 'pay_QupUUqaXBCtvQh', 'order_QupRbQgNQ6gAWN'),
(2, 1, '2025-07-19 12:19:24', 75000.00, 'Paid', 'pay_QuphBD8Kfm8oh8', 'order_QupgjcUxFuAQPP'),
(3, 1, '2025-07-19 12:53:08', 29999.00, 'Paid', 'pay_QuqGoICJhs8JY8', 'order_QuqGE1Vp3Wk5iW');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 5, 1, 75000.00),
(2, 2, 5, 1, 75000.00),
(3, 3, 4, 1, 29999.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `mrp` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `price`, `image`, `description`, `category`, `mrp`) VALUES
(1, 'Fresh Milk', 60.00, 'https://images.squarespace-cdn.com/content/v1/638d8044b6fc77648ebcedba/21e5e22f-c5ad-42e4-80e9-1022ae1fe6fa/400+ml+-+Kota+Fresh+Standard+Milk', '1L Pack, pure & fresh', 'grocery', 0.00),
(2, 'Basmati Rice 5kg', 350.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm2omuetkivN4saJNI9sJ3xbgtuzwKL22fMg&s', 'Premium quality, long grain', 'grocery', 0.00),
(3, 'Tomato (1kg)', 50.00, 'https://upload.wikimedia.org/wikipedia/commons/8/89/Tomato_je.jpg', 'Farm fresh', 'grocery', 0.00),
(4, 'Samsung Smart TV', 29999.00, 'https://images.samsung.com/is/image/samsung/p6pim/in/ua50du7660klxl/gallery/in-crystal-uhd-du7000-ua50du7660klxl-540323231?$684_547_PNG$', '43\" 4K UHD Smart, 2023 Model', 'electronics', 0.00),
(5, 'Apple iPhone 15', 75000.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSvgCPBUySTN1TjyFl-Lu-XqGoZlDnLDpnbQ&s', '128GB, Blue', 'electronics', 0.00),
(8, 'Chilli Powder', 35.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRNxOXt2MBhrw84jk7vMAVO618uhjfM-KhkaQ&s', 'Chilli Powder', 'grocery', 0.00),
(9, 'Iron Box', 599.00, 'https://impexstore.com/cdn/shop/products/41SPpkRjtQL.jpg?v=1661235652', 'Ironbox ', 'electronics', 0.00),
(10, 'Samsung S25 ultra', 129999.00, 'https://m.media-amazon.com/images/I/71P85R392uL._UF1000,1000_QL80_.jpg', 'Samsung S25 ultra ', 'electronics', 0.00),
(12, 'Potato', 45.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR1hyLmBOdkNwILGTv3fAHKYk05fKBHTE61dg&s', '1kg fresh Potato', 'grocery', 0.00),
(13, 'Iqoo neo 10', 31999.00, 'https://m.media-amazon.com/images/I/610ELrtuHEL._UF1000,1000_QL80_.jpg', 'Display: 6.78-inch 1.5K AMOLED display with a 144Hz refresh rate for smooth visuals and a peak brightness of 5,500 nits, protected by Schott Xensation Up glass.\r\nProcessor: Powered by the Qualcomm Snapdragon 8s Gen 4 processor, combined with the SuperComputing Chip Q1 for optimized gaming performance and efficient heat management.\r\nRAM and Storage: Available in multiple configurations including 8G', 'electronics', 0.00),
(14, 'Asus TUF Gaming A15', 57999.00, 'https://in.store.asus.com/media/catalog/product/f/a/fa506_grpht_blck_5_.png?quality=80&bg-color=255,255,255&fit=bounds&height=800&width=800&canvas=800:800', 'Processor\r\nAMD Ryzen processors: Most Asus TUF A15 models come equipped with AMD Ryzen CPUs, including various iterations like the Ryzen 5, 7, and 9 series.\r\nSome models might be equipped with Intel Core processors as well. \r\nGraphics\r\nNVIDIA GeForce GTX and RTX series: The A15 models offer a range of discrete graphics cards, including NVIDIA GeForce GTX 1650, GTX 1660 Ti, and RTX 2050, 2060, 3050', 'electronics', 0.00),
(15, 'Apple watch series 9', 36999.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTg1Nt16WjR-OB8IyBwGrcqO3741ckx4sXbaA&s', 'Dimensions and weight\r\nSize Options: 41mm and 45mm.\r\n41mm (GPS): Height: 41mm, Width: 35mm, Depth: 10.7mm. Weight (aluminum): 31.9 grams. Fits 130–200mm wrists.\r\n41mm (GPS + Cellular): Height: 41mm, Width: 35mm, Depth: 10.7mm. Weight (aluminum): 32.1 grams. Fits 130–200mm wrists.\r\n45mm (GPS): Height: 45mm, Width: 38mm, Depth: 10.7mm. Weight (aluminum): 38.7 grams. Fits 140–245mm wrists.\r\n45mm (GPS', 'electronics', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(4) DEFAULT 0,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `is_admin`, `role`, `profile_image`) VALUES
(1, 'test', 'test123@gmail.com', '202cb962ac59075b964b07152d234b70', 0, 'user', 'uploads/687b44a224256_.trashed-1744115324-Snapchat-574087010.jpg'),
(2, 'Devan', 'devan@grocxpress.com', '0192023a7bbd73250516f069df18b500', 1, 'admin', NULL),
(3, 'Aravind', 'aravind@grocxpress.com', '0192023a7bbd73250516f069df18b500', 1, 'admin', NULL),
(4, 'test2', 'test1223@gmail.com', '202cb962ac59075b964b07152d234b70', 0, 'user', NULL),
(5, 'Anjala', 'anjala@bvmcollege.com', 'dd646a1c07c3defb9dfec12c541621ed', 0, 'user', NULL),
(6, 'devan', 'devan@gmail.com', '202cb962ac59075b964b07152d234b70', 0, 'user', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `user_id`, `product_id`) VALUES
(8, 2, 4),
(10, 1, 3),
(11, 1, 4),
(12, 1, 12),
(13, 1, 13);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
